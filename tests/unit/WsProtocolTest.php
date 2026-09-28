<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Commands\WsServe;

/**
 * @internal
 */
final class WsProtocolTest extends CIUnitTestCase
{
    private WsServe $ws;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ws = new WsServe(service('logger'), service('commands'));
    }

    public function testEncodeTextFrame(): void
    {
        $payload = '{"action":"test"}';
        $frame = $this->ws->encode($payload);

        $this->assertNotEmpty($frame);
        $firstByte = ord($frame[0]);
        $secondByte = ord($frame[1]);

        // FIN = 1 (0x80), Opcode = 0x1 (text) -> 0x81
        $this->assertEquals(0x81, $firstByte);
        // Mask = 0 (server to client frames are unmasked), length = strlen($payload)
        $this->assertEquals(strlen($payload), $secondByte);
        $this->assertEquals($payload, substr($frame, 2));
    }

    public function testEncodePingControlFrame(): void
    {
        $payload = pack('N', 12345678);
        $frame = $this->ws->encodePing($payload);

        $this->assertNotEmpty($frame);
        $firstByte = ord($frame[0]);
        // FIN = 1, Opcode = 0x9 (Ping) -> 0x89
        $this->assertEquals(0x89, $firstByte);
        $this->assertEquals(4, ord($frame[1]));
        $this->assertEquals($payload, substr($frame, 2));
    }

    public function testEncodePongControlFrame(): void
    {
        $payload = 'pong-payload';
        $frame = $this->ws->encodePong($payload);

        $firstByte = ord($frame[0]);
        // FIN = 1, Opcode = 0xA (Pong) -> 0x8A
        $this->assertEquals(0x8A, $firstByte);
        $this->assertEquals($payload, substr($frame, 2));
    }

    public function testEncodeCloseControlFrame(): void
    {
        $frame = $this->ws->encodeClose(1000, 'Normal Closure');

        $firstByte = ord($frame[0]);
        // FIN = 1, Opcode = 0x8 (Close) -> 0x88
        $this->assertEquals(0x88, $firstByte);

        $payload = substr($frame, 2);
        $unpacked = unpack('ncode', substr($payload, 0, 2));
        $this->assertEquals(1000, $unpacked['code']);
        $this->assertEquals('Normal Closure', substr($payload, 2));
    }

    public function testDecodeMaskedClientTextFrame(): void
    {
        $text = 'Hello WebSocket!';
        $mask = pack('N', 0x37fa213d); // 4-byte mask key

        $firstByte = 0x81; // FIN + text opcode
        $secondByte = 0x80 | strlen($text); // MASK bit = 1 + length

        $maskedPayload = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $maskedPayload .= $text[$i] ^ $mask[$i % 4];
        }

        $rawFrame = pack('CC', $firstByte, $secondByte) . $mask . $maskedPayload;

        $decoded = $this->ws->decodeFrame($rawFrame);

        $this->assertNotNull($decoded);
        $this->assertEquals(1, $decoded['fin']);
        $this->assertEquals(0x1, $decoded['opcode']);
        $this->assertTrue($decoded['masked']);
        $this->assertEquals($text, $decoded['payload']);
    }

    public function testDecodeClientCloseFrame(): void
    {
        $closeCode = pack('n', 1000);
        $mask = pack('N', 0x12345678);

        $firstByte = 0x88; // FIN + Close opcode (0x8)
        $secondByte = 0x80 | 2; // MASK = 1, len = 2

        $maskedPayload = ($closeCode[0] ^ $mask[0]) . ($closeCode[1] ^ $mask[1]);
        $rawFrame = pack('CC', $firstByte, $secondByte) . $mask . $maskedPayload;

        $decoded = $this->ws->decodeFrame($rawFrame);

        $this->assertNotNull($decoded);
        $this->assertEquals(0x8, $decoded['opcode']);
        $this->assertEquals($closeCode, $decoded['payload']);
    }

    public function testDecodeClientPingFrame(): void
    {
        $mask = pack('N', 0x55aa55aa);
        $firstByte = 0x89; // FIN + Ping opcode (0x9)
        $secondByte = 0x80 | 0; // MASK = 1, len = 0

        $rawFrame = pack('CC', $firstByte, $secondByte) . $mask;
        $decoded = $this->ws->decodeFrame($rawFrame);

        $this->assertNotNull($decoded);
        $this->assertEquals(0x9, $decoded['opcode']);
        $this->assertEquals('', $decoded['payload']);
    }

    public function testDecodeIncompleteBufferReturnsNull(): void
    {
        $this->assertNull($this->ws->decodeFrame(''));
        $this->assertNull($this->ws->decodeFrame('a'));
    }

    public function testDecodeWaitsForCompletePayloadAndPreservesFrameBoundary(): void
    {
        $mask = "\x01\x02\x03\x04";
        $payload = 'pong';
        $masked = '';
        for ($i = 0; $i < strlen($payload); $i++) {
            $masked .= $payload[$i] ^ $mask[$i % 4];
        }
        $frame = "\x8a\x84" . $mask . $masked;

        $this->assertNull($this->ws->decodeFrame(substr($frame, 0, -1)));
        $decoded = $this->ws->decodeFrame($frame . $frame);
        $this->assertSame($payload, $decoded['payload']);
        $this->assertSame(strlen($frame), $decoded['totalLength']);
        $this->assertSame($payload, $this->ws->decodeFrame(substr($frame . $frame, $decoded['totalLength']))['payload']);
    }

    public function testDecodeRejectsUnmaskedClientFrame(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->ws->decodeFrame("\x89\x00");
    }

    public function testDecodeRejectsOversizedClientFrameBeforePayloadArrives(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->ws->decodeFrame("\x82\xff" . pack('NN', 0, 65537));
    }

    public function testHandshakeRequiresValidWebSocketUpgrade(): void
    {
        $stream = fopen('php://memory', 'r+');
        $headers = "GET /ws HTTP/1.1\r\nHost: localhost\r\nUpgrade: websocket\r\n"
            . "Connection: Upgrade\r\nSec-WebSocket-Version: 13\r\n"
            . "Sec-WebSocket-Key: " . base64_encode(random_bytes(16)) . "\r\nOrigin: http://localhost\r\n\r\n";

        $this->assertTrue($this->ws->performHandshake($stream, $headers));
        rewind($stream);
        $this->assertStringStartsWith('HTTP/1.1 101', stream_get_contents($stream));
        fclose($stream);

        $invalid = str_replace('Sec-WebSocket-Version: 13', 'Sec-WebSocket-Version: 12', $headers);
        $stream = fopen('php://memory', 'r+');
        $this->assertFalse($this->ws->performHandshake($stream, $invalid));
        fclose($stream);
    }

    public function testOriginValidationAllowsLocalhost(): void
    {
        $headers = "GET / HTTP/1.1\r\nHost: localhost:8081\r\nOrigin: http://localhost\r\n\r\n";
        $this->assertTrue($this->ws->isAllowedOrigin($headers));

        $headersIp = "GET / HTTP/1.1\r\nHost: 127.0.0.1:8081\r\nOrigin: http://127.0.0.1:8080\r\n\r\n";
        $this->assertTrue($this->ws->isAllowedOrigin($headersIp));
    }

    public function testOriginValidationRejectsMaliciousOrigin(): void
    {
        $headers = "GET / HTTP/1.1\r\nHost: localhost:8081\r\nOrigin: https://malicious-attacker-site.com\r\n\r\n";
        $this->assertFalse($this->ws->isAllowedOrigin($headers));
    }

    public function testOriginValidationAllowsMissingOriginForNonBrowserClients(): void
    {
        $headers = "GET / HTTP/1.1\r\nHost: localhost:8081\r\nUser-Agent: TerminalApp/1.0\r\n\r\n";
        $this->assertTrue($this->ws->isAllowedOrigin($headers));
    }
}
