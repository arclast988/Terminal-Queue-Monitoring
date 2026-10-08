<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DeploymentAppUrlTest extends TestCase
{
    #[DataProvider('productionUrls')]
    public function testProductionBootstrapUsesAValidTrustedUrl(string $configured, string $domain, string $host, string $expected): void
    {
        $input = json_encode(compact('configured', 'domain', 'host'), JSON_THROW_ON_ERROR);
        $process = proc_open(
            [PHP_BINARY, ROOTPATH . 'tests/deployment/probe-app-url.php', $input],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            ROOTPATH
        );
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors);
        $marker = strrpos($output, 'PROBE_URL:');
        $this->assertNotFalse($marker, $output);
        $result = substr($output, $marker + strlen('PROBE_URL:'));
        $this->assertSame(['baseURL' => $expected, 'uri' => $expected], json_decode($result, true, 512, JSON_THROW_ON_ERROR));
    }

    public static function productionUrls(): array
    {
        return [
            'CLI after domain removal' => ['', '', '', 'http://localhost/'],
            'CLI with malformed site name' => ['new-site-name', '', '', 'http://localhost/'],
            'CLI after Railway rename' => ['https://old-site.up.railway.app/', 'new-site.up.railway.app', '', 'https://new-site.up.railway.app/'],
            'web request after Railway rename' => ['https://old-site.up.railway.app/', 'new-site.up.railway.app', 'new-site.up.railway.app', 'https://new-site.up.railway.app/'],
            'unrelated Railway host cannot replace custom URL' => ['https://terminal.example.org/', 'new-site.up.railway.app', 'untrusted.up.railway.app', 'https://terminal.example.org/'],
        ];
    }
}
