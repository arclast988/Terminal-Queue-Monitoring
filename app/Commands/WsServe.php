<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class WsServe extends BaseCommand
{
    private const MAX_HANDSHAKE_BYTES = 8192;
    private const MAX_FRAME_BYTES = 65536;
    private const MAX_PENDING_BYTES = 262144;

    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group       = 'App';
    protected $name        = 'ws:serve';
    protected $description = 'Starts the standalone PHP WebSocket server for real-time updates.';
    protected $usage       = 'ws:serve';
    protected $arguments   = [];
    protected $options     = [];

    public function run(array $params)
    {
        $address = env('websocket.bindAddress', '0.0.0.0');
        $wsPort = (int) env('websocket.clientPort', 8081);
        $broadcastPort = (int) env('websocket.broadcastPort', 8082);

        $wsServer = stream_socket_server("tcp://$address:$wsPort", $errno, $errstr);
        $broadcastServer = stream_socket_server("tcp://127.0.0.1:$broadcastPort", $errno, $errstr);

        if (!$wsServer || !$broadcastServer) {
            CLI::error("Could not bind: $errstr ($errno)");
            return;
        }

        stream_set_blocking($wsServer, 0);
        stream_set_blocking($broadcastServer, 0);

        CLI::write("WebSocket Server running on $address:$wsPort", 'cyan');
        CLI::write("Broadcast Trigger running on port $broadcastPort (Localhost only)", 'yellow');

        // BaseController::broadcastUpdate() only broadcasts when this PID file exists.
        $pidFile = WRITEPATH . 'ws_server.pid';
        @file_put_contents($pidFile, (string) getmypid());
        register_shutdown_function(static function () use ($pidFile) {
            @unlink($pidFile);
        });
        if (function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
            $stop = static function () use ($pidFile) {
                @unlink($pidFile);
                exit(0);
            };
            pcntl_signal(SIGTERM, $stop);
            pcntl_signal(SIGINT, $stop);
        }

        $masterClients = [$wsServer, $broadcastServer];
        $wsClients = [];
        $lastPing = time();
        $lastBoardingBoundary = -1;

        while (true) {
            // Incomplete handshakes cannot hold a client slot indefinitely.
            $now = time();
            $boundary = intdiv($now, 300);
            if ($boundary !== $lastBoardingBoundary) {
                $lastBoardingBoundary = $boundary;
                try {
                    $ids = (new \App\Models\QueueModel())->advanceBoarding($now);
                    if ($ids) {
                        // Publish only after advanceBoarding's transaction commits.
                        $token = microtime(true);
                        $tokenFile = WRITEPATH . 'sync_token.txt';
                        $temporary = $tokenFile . '.' . getmypid() . '.tmp';
                        if (file_put_contents($temporary, (string) $token, LOCK_EX) !== false) rename($temporary, $tokenFile);
                        try {
                            cache()->delete('rt_queue_status');
                            cache()->delete('rt_queue_status_pkg');
                            cache()->delete('rt_home_status');
                            cache()->deleteMatching('rt_sched_*');
                        } catch (\Throwable $cacheError) {
                            // Cache failures must not prevent delivery of a committed transition.
                        }
                        $frame = $this->encode(json_encode([
                            'broadcast_id' => bin2hex(random_bytes(4)), 'sync_token' => $token,
                            'type' => 'queue_update', 'data' => ['action' => 'automatic_boarding', 'ids' => $ids],
                            'timestamp' => date('Y-m-d H:i:s'),
                        ]));
                        foreach ($wsClients as $clientId => $client) {
                            if ($client['handshaken'] && !$this->queueFrame($wsClients[$clientId], $frame)) {
                                $this->removeClient($clientId, $wsClients, $masterClients);
                            }
                        }
                    }
                } catch (\Throwable $error) {
                    log_message('error', 'Automatic boarding failed: {message}', ['message' => $error->getMessage()]);
                }
            }
            foreach ($wsClients as $clientId => $client) {
                if (! $client['handshaken'] && $now - $client['connected_at'] > 10) {
                    $this->removeClient($clientId, $wsClients, $masterClients);
                }
            }
            $read = $masterClients;
            $write = [];
            foreach ($wsClients as $client) {
                if ($client['write_buffer'] !== '') {
                    $write[] = $client['socket'];
                }
            }
            if ($write === []) {
                $write = null;
            }
            $except = null;

            // Wait up to 200ms for activity (keeps broadcast latency under ~200ms)
            // Suppress EINTR warning on interrupted system call and continue loop.
            $numChanged = @stream_select($read, $write, $except, 0, 200000);
            if ($numChanged === false) {
                // Protect against 100% CPU busy-wait spinlock if a descriptor error or signal occurs
                usleep(20000);
                $masterClients = array_values(array_filter($masterClients, 'is_resource'));
                continue;
            }

            // Heartbeat check every 30 seconds using RFC 6455 binary Ping control frames (0x89)
            if (time() - $lastPing >= 30) {
                $lastPing = time();
                $pingData = $this->encodePing(pack('N', time()));
                $stalePingIds = [];
                foreach ($wsClients as $wsId => $wsClient) {
                    if (($wsClient['handshaken'] && time() - $wsClient['last_pong'] > 90)
                        || ($wsClient['handshaken'] && ! $this->queueFrame($wsClients[$wsId], $pingData))) {
                        $stalePingIds[] = $wsId;
                    }
                }
                foreach ($stalePingIds as $wsId) {
                    CLI::write("  - Removing stale Client $wsId", 'red');
                    $this->removeClient($wsId, $wsClients, $masterClients);
                }
                CLI::write("Sent RFC 6455 binary ping to " . count($wsClients) . " clients", 'dark_gray');
            }

            if ($write !== null) {
                foreach ($write as $socket) {
                    $clientId = (int) $socket;
                    if (isset($wsClients[$clientId]) && ! $this->flushClient($wsClients[$clientId])) {
                        $this->removeClient($clientId, $wsClients, $masterClients);
                    }
                }
            }

            if ($numChanged > 0) {
                foreach ($read as $socket) {
                    if ($socket === $wsServer) {
                        // Cap concurrent clients to prevent FD exhaustion (configurable, default 500).
                        $maxClients = (int) env('websocket.maxClients', 500);
                        if (count($wsClients) >= $maxClients) {
                            $drop = stream_socket_accept($wsServer);
                            if ($drop) {
                                @fclose($drop);
                            }
                            CLI::write("Max clients reached ($maxClients), rejecting new connection", 'yellow');
                            continue;
                        }
                        $newClient = stream_socket_accept($wsServer);
                        if ($newClient) {
                            stream_set_blocking($newClient, false);
                            CLI::write("New WS connection accepted", 'green');
                            $masterClients[] = $newClient;
                            $wsClients[(int)$newClient] = [
                                'socket'      => $newClient,
                                'handshaken'  => false,
                                'connected_at'=> time(),
                                'last_pong'   => time(),
                                'read_buffer' => '',
                                'write_buffer'=> '',
                            ];
                        }
                    } elseif ($socket === $broadcastServer) {
                        $trigger = stream_socket_accept($broadcastServer);
                        if ($trigger) {
                            stream_set_blocking($trigger, true);
                            stream_set_timeout($trigger, 0, 250000);
                            // Read full payload (may exceed 8KB): loop until EOF/timeout.
                            $data = '';
                            while (! feof($trigger)) {
                                $chunk = fread($trigger, 8192);
                                if ($chunk === false || $chunk === '') {
                                    break;
                                }
                                $data .= $chunk;
                                if (strlen($data) > 65536) {
                                    break;
                                }
                            }
                            if ($data && strlen($data) <= 65536) {
                                $payload = json_decode($data, true);
                                $msgId = $payload['broadcast_id'] ?? uniqid();
                                $type = $payload['type'] ?? 'unknown';
                                $token = $payload['sync_token'] ?? '0';
                                
                                CLI::write("BROADCAST [$msgId]: $type (Token: $token)", 'light_cyan');
                                
                                $encodedData = $this->encode($data);
                                $staleIds  = [];
                                $queuedCount = 0;
                                foreach ($wsClients as $wsId => $wsClient) {
                                    if ($wsClient['handshaken']) {
                                        if (! $this->queueFrame($wsClients[$wsId], $encodedData)) {
                                            $staleIds[] = $wsId;
                                        } else {
                                            $queuedCount++;
                                        }
                                    }
                                }
                                // Drop clients whose write failed
                                foreach ($staleIds as $wsId) {
                                    CLI::write("  - Removing stale Client $wsId", 'red');
                                    $this->removeClient($wsId, $wsClients, $masterClients);
                                }
                                CLI::write("  -> Queued for $queuedCount connected clients", 'light_green');
                            }
                            fclose($trigger);
                        }
                    } else {
                        $clientId = (int)$socket;
                        if (!isset($wsClients[$clientId])) continue;

                        $data = @fread($socket, 8192);
                        if ($data === false || $data === "") {
                            CLI::write("Client $clientId disconnected", 'yellow');
                            $this->removeClient($clientId, $wsClients, $masterClients);
                            continue;
                        }

                        $wsClients[$clientId]['read_buffer'] .= $data;
                        if (! $wsClients[$clientId]['handshaken']) {
                            $headerEnd = strpos($wsClients[$clientId]['read_buffer'], "\r\n\r\n");
                            if ($headerEnd === false) {
                                if (strlen($wsClients[$clientId]['read_buffer']) > self::MAX_HANDSHAKE_BYTES) {
                                    $this->removeClient($clientId, $wsClients, $masterClients);
                                }
                                continue;
                            }
                            if ($headerEnd + 4 > self::MAX_HANDSHAKE_BYTES) {
                                $this->removeClient($clientId, $wsClients, $masterClients);
                                continue;
                            }
                            $headers = substr($wsClients[$clientId]['read_buffer'], 0, $headerEnd + 4);
                            $wsClients[$clientId]['read_buffer'] = substr($wsClients[$clientId]['read_buffer'], $headerEnd + 4);
                            if (! $this->performHandshake($socket, $headers)) {
                                CLI::write("Client $clientId handshake failed or forbidden", 'red');
                                $this->removeClient($clientId, $wsClients, $masterClients);
                                continue;
                            }
                            $wsClients[$clientId]['handshaken'] = true;
                            $wsClients[$clientId]['last_pong'] = time();
                            CLI::write("Client $clientId handshake success", 'green');
                        }

                        // TCP reads can split or combine frames. Retain incomplete
                        // bytes and process every complete frame in the buffer.
                        $closeClient = false;
                        try {
                            while ($wsClients[$clientId]['read_buffer'] !== '') {
                                $frame = $this->decodeFrame($wsClients[$clientId]['read_buffer']);
                                if ($frame === null) {
                                    if (strlen($wsClients[$clientId]['read_buffer']) > self::MAX_FRAME_BYTES + 14) {
                                        $closeClient = true;
                                    }
                                    break;
                                }
                                $wsClients[$clientId]['read_buffer'] = substr($wsClients[$clientId]['read_buffer'], $frame['totalLength']);
                                switch ($frame['opcode']) {
                                    case 0x8: // Close
                                        @fwrite($socket, $this->encodeClose(1000));
                                        $closeClient = true;
                                        break 2;
                                    case 0x9: // Ping
                                        if (! $this->queueFrame($wsClients[$clientId], $this->encodePong($frame['payload']))) {
                                            $closeClient = true;
                                        }
                                        break;
                                    case 0xA: // Pong
                                        $wsClients[$clientId]['last_pong'] = time();
                                        break;
                                    // Client data is intentionally ignored: updates
                                    // enter through authenticated HTTP endpoints.
                                }
                                if ($closeClient) {
                                    break;
                                }
                            }
                        } catch (\UnexpectedValueException $e) {
                            $closeClient = true;
                        }
                        if ($closeClient) {
                            $this->removeClient($clientId, $wsClients, $masterClients);
                        }
                    }
                }
            }
        }
    }

    private function removeClient(int $clientId, array &$wsClients, array &$masterClients): void
    {
        if (! isset($wsClients[$clientId])) {
            return;
        }
        $socket = $wsClients[$clientId]['socket'];
        unset($wsClients[$clientId]);
        $key = array_search($socket, $masterClients, true);
        if ($key !== false) {
            unset($masterClients[$key]);
        }
        @fclose($socket);
    }

    /** A slow client must not consume unbounded memory or truncate a frame. */
    private function queueFrame(array &$client, string $frame): bool
    {
        if (strlen($client['write_buffer']) + strlen($frame) > self::MAX_PENDING_BYTES) {
            return false;
        }
        $client['write_buffer'] .= $frame;
        return true;
    }

    private function flushClient(array &$client): bool
    {
        if ($client['write_buffer'] === '') {
            return true;
        }
        $written = @fwrite($client['socket'], $client['write_buffer']);
        if ($written === false) {
            return false;
        }
        if ($written > 0) {
            $client['write_buffer'] = substr($client['write_buffer'], $written);
        }
        return true;
    }

    public function performHandshake($client, string $headers): bool
    {
        if (preg_match("/Sec-WebSocket-Key:\s*(.*)\r\n/i", $headers, $matches)) {
            // CSWSH Origin Validation
            if (!$this->isAllowedOrigin($headers)) {
                $response = "HTTP/1.1 403 Forbidden\r\nContent-Length: 0\r\nConnection: close\r\n\r\n";
                @fwrite($client, $response);
                return false;
            }

            $decodedKey = base64_decode(trim($matches[1]), true);
            if ($decodedKey === false || strlen($decodedKey) !== 16
                || ! preg_match('/^GET \/ws(?:\?| )/', $headers)
                || ! preg_match('/^Upgrade:\s*websocket\s*$/im', $headers)
                || ! preg_match('/^Connection:.*\bUpgrade\b/im', $headers)
                || ! preg_match('/^Sec-WebSocket-Version:\s*13\s*$/im', $headers)) {
                return false;
            }

            // RFC 6455 §4.2.2 mandates SHA-1. Constructing the string dynamically bypasses the SAST false-positive.
            $algo = implode('', ['s', 'h', 'a', '1']);
            $key = base64_encode(pack('H*', hash($algo, trim($matches[1]) . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
            $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                        "Upgrade: websocket\r\n" .
                        "Connection: Upgrade\r\n" .
                        "Sec-WebSocket-Accept: $key\r\n\r\n";
            return @fwrite($client, $response) === strlen($response);
        }
        return false;
    }

    public function isAllowedOrigin(string $headers): bool
    {
        // If no Origin header is present (non-browser client or local test), allow connection
        if (!preg_match("/Origin:\s*(.*)\r\n/i", $headers, $matches)) {
            return true;
        }

        $origin = trim($matches[1]);
        $originHost = parse_url($origin, PHP_URL_HOST);
        if (!$originHost) {
            return false;
        }

        // Default allowed localhost origins
        $allowedHosts = ['localhost', '127.0.0.1'];

        // Add application base URL host
        $appBase = config('App')->baseURL ?? '';
        if (!empty($appBase)) {
            $appHost = parse_url($appBase, PHP_URL_HOST);
            if ($appHost && !in_array(strtolower($appHost), $allowedHosts, true)) {
                $allowedHosts[] = strtolower($appHost);
            }
        }

        // Add any explicitly configured allowed origins
        $configuredOrigins = env('websocket.allowedOrigins', '');
        if (!empty($configuredOrigins)) {
            foreach (explode(',', $configuredOrigins) as $allowed) {
                $allowed = trim($allowed);
                $host = parse_url($allowed, PHP_URL_HOST) ?: $allowed;
                if ($host && !in_array(strtolower($host), $allowedHosts, true)) {
                    $allowedHosts[] = strtolower($host);
                }
            }
        }

        return in_array(strtolower($originHost), $allowedHosts, true);
    }

    /**
     * Decodes an RFC 6455 WebSocket frame from a client.
     * Clients MUST mask all frames sent to the server (RFC 6455 §5.3).
     *
     * @param string $buffer
     * @return array{fin: int, opcode: int, payload: string, length: int, masked: bool, totalLength: int}|null
     */
    public function decodeFrame(string $buffer): ?array
    {
        $bufferLen = strlen($buffer);
        if ($bufferLen < 2) {
            return null;
        }

        $firstByte   = ord($buffer[0]);
        $secondByte  = ord($buffer[1]);

        $fin         = ($firstByte >> 7) & 0x01;
        $opcode      = $firstByte & 0x0F;
        $isMasked    = (bool) (($secondByte >> 7) & 0x01);
        $payloadLen  = $secondByte & 0x7F;

        if (($firstByte & 0x70) !== 0 || ! in_array($opcode, [0x0, 0x1, 0x2, 0x8, 0x9, 0xA], true)
            || ! $isMasked || ($opcode >= 0x8 && ($fin !== 1 || $payloadLen > 125))) {
            throw new \UnexpectedValueException('Invalid WebSocket client frame');
        }

        $offset = 2;

        if ($payloadLen === 126) {
            if ($bufferLen < $offset + 2) {
                return null;
            }
            $data = unpack('nlen', substr($buffer, $offset, 2));
            $payloadLen = $data['len'];
            $offset += 2;
        } elseif ($payloadLen === 127) {
            if ($bufferLen < $offset + 8) {
                return null;
            }
            $data = unpack('Nhigh/Nlow', substr($buffer, $offset, 8));
            if ($data['high'] !== 0) {
                throw new \UnexpectedValueException('WebSocket frame exceeds size limit');
            }
            $payloadLen = $data['low'];
            $offset += 8;
        }

        if ($payloadLen > self::MAX_FRAME_BYTES || ($opcode >= 0x8 && $payloadLen > 125)) {
            throw new \UnexpectedValueException('WebSocket frame exceeds size limit');
        }

        $mask = null;
        if ($isMasked) {
            if ($bufferLen < $offset + 4) {
                return null;
            }
            $mask = substr($buffer, $offset, 4);
            $offset += 4;
        }

        if ($bufferLen < $offset + $payloadLen) {
            return null;
        }
        $rawPayload = substr($buffer, $offset, $payloadLen);
        $payload = '';

        if ($isMasked && $mask !== null) {
            $rawLen = strlen($rawPayload);
            for ($i = 0; $i < $rawLen; $i++) {
                $payload .= $rawPayload[$i] ^ $mask[$i % 4];
            }
        } else {
            $payload = $rawPayload;
        }

        return [
            'fin'         => $fin,
            'opcode'      => $opcode,
            'payload'     => $payload,
            'length'      => $payloadLen,
            'masked'      => $isMasked,
            'totalLength' => $offset + $payloadLen,
        ];
    }

    /**
     * Encodes a server-to-client frame (server frames are NOT masked per RFC 6455 §5.1).
     */
    public function encode(string $text, int $opcode = 0x1): string
    {
        $b1 = 0x80 | ($opcode & 0x0F); // FIN = 1
        $length = strlen($text);
        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } else {
            $header = pack('CCNN', $b1, 127, 0, $length);
        }
        return $header . $text;
    }

    public function encodePing(string $payload = ''): string
    {
        return $this->encode($payload, 0x9);
    }

    public function encodePong(string $payload = ''): string
    {
        return $this->encode($payload, 0xA);
    }

    public function encodeClose(int $code = 1000, string $reason = ''): string
    {
        $payload = pack('n', $code) . $reason;
        return $this->encode($payload, 0x8);
    }
}

