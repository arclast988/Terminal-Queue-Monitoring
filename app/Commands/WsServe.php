<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class WsServe extends BaseCommand
{
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
        $address = '0.0.0.0';
        $wsPort = 8081;
        $broadcastPort = 8082;

        $wsServer = stream_socket_server("tcp://$address:$wsPort", $errno, $errstr);
        $broadcastServer = stream_socket_server("tcp://127.0.0.1:$broadcastPort", $errno, $errstr);

        if (!$wsServer || !$broadcastServer) {
            CLI::error("Could not bind: $errstr ($errno)");
            return;
        }

        stream_set_blocking($wsServer, 0);
        stream_set_blocking($broadcastServer, 0);

        CLI::write("WebSocket Server running on port $wsPort", 'cyan');
        CLI::write("Broadcast Trigger running on port $broadcastPort (Localhost only)", 'yellow');

        $masterClients = [$wsServer, $broadcastServer];
        $wsClients = [];
        $lastPing = time();

        while (true) {
            $read = $masterClients;
            $write = null;
            $except = null;

            // Wait up to 5 seconds for activity
            $numChanged = stream_select($read, $write, $except, 5);

            // Heartbeat check every 30 seconds
            if (time() - $lastPing >= 30) {
                $lastPing = time();
                $pingData = $this->encode(json_encode(['type' => 'ping', 'timestamp' => time()]));
                foreach ($wsClients as $wsId => $wsClient) {
                    if ($wsClient['handshaken']) {
                        @fwrite($wsClient['socket'], $pingData);
                    }
                }
                CLI::write("Sent heartybeat ping to " . count($wsClients) . " clients", 'dark_gray');
            }

            if ($numChanged > 0) {
                foreach ($read as $socket) {
                    if ($socket === $wsServer) {
                        $newClient = stream_socket_accept($wsServer);
                        if ($newClient) {
                            CLI::write("New WS connection accepted", 'green');
                            $masterClients[] = $newClient;
                            $wsClients[(int)$newClient] = ['socket' => $newClient, 'handshaken' => false];
                        }
                    } elseif ($socket === $broadcastServer) {
                        $trigger = stream_socket_accept($broadcastServer);
                        if ($trigger) {
                            $data = fread($trigger, 8192);
                            if ($data) {
                                $payload = json_decode($data, true);
                                $msgId = $payload['broadcast_id'] ?? uniqid();
                                $type = $payload['type'] ?? 'unknown';
                                $token = $payload['sync_token'] ?? '0';
                                
                                CLI::write("BROADCAST [$msgId]: $type (Token: $token)", 'light_cyan');
                                
                                $encodedData = $this->encode($data);
                                $targetIds = [];
                                foreach ($wsClients as $wsId => $wsClient) {
                                    if ($wsClient['handshaken']) {
                                        $result = @fwrite($wsClient['socket'], $encodedData);
                                        if ($result !== false) {
                                            $targetIds[] = $wsId;
                                        } else {
                                            CLI::write("  - Removing stale Client $wsId", 'red');
                                            // Handle stale connection in next loop
                                        }
                                    }
                                }
                                if (count($targetIds) > 0) {
                                    CLI::write("  -> Sent to clients: [" . implode(', ', $targetIds) . "]", 'light_green');
                                } else {
                                    CLI::write("  -> No active clients found.", 'yellow');
                                }
                            }
                            fclose($trigger);
                        }
                    } else {
                        $clientId = (int)$socket;
                        if (!isset($wsClients[$clientId])) continue;

                        $data = @fread($socket, 8192);
                        if ($data === false || $data === "") {
                            CLI::write("Client $clientId disconnected", 'yellow');
                            unset($wsClients[$clientId]);
                            $key = array_search($socket, $masterClients);
                            if ($key !== false) unset($masterClients[$key]);
                            @fclose($socket);
                            continue;
                        }

                        if (!$wsClients[$clientId]['handshaken']) {
                            if ($this->performHandshake($socket, $data)) {
                                $wsClients[$clientId]['handshaken'] = true;
                                CLI::write("Client $clientId handshake success", 'green');
                            } else {
                                CLI::write("Client $clientId handshake failed", 'red');
                                unset($wsClients[$clientId]);
                                $key = array_search($socket, $masterClients);
                                if ($key !== false) unset($masterClients[$key]);
                                @fclose($socket);
                            }
                        }
                    }
                }
            }
        }
    }

    private function performHandshake($client, $headers)
    {
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $matches)) {
            $key = base64_encode(pack('H*', sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
            $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                        "Upgrade: websocket\r\n" .
                        "Connection: Upgrade\r\n" .
                        "Sec-WebSocket-Accept: $key\r\n\r\n";
            return @fwrite($client, $response);
        }
        return false;
    }

    private function encode($text)
    {
        $b1 = 0x80 | (0x1 & 0x0f); // Text frame
        $length = strlen($text);
        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } else {
            $header = pack('CCNN', $b1, 127, 0, $length);
        }
        return $header . $text;
    }
}
