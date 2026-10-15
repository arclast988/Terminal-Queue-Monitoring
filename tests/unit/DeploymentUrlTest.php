<?php

namespace Tests\Unit;

use App\Libraries\DeploymentUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DeploymentUrlTest extends TestCase
{
    #[DataProvider('deploymentUrls')]
    public function testDeploymentAlwaysHasAValidCanonicalUrl(string $configured, string $domain, string $expected): void
    {
        $actual = DeploymentUrl::resolve($configured, $domain);
        $this->assertSame($expected, $actual);
        $this->assertNotFalse(filter_var($actual, FILTER_VALIDATE_URL));
    }

    public static function deploymentUrls(): array
    {
        return [
            'domain removed' => ['', '', 'http://localhost/'],
            'invalid explicit URL without domain' => ['new-site-name', '', 'http://localhost/'],
            'new Railway domain' => ['', 'new-site.up.railway.app', 'https://new-site.up.railway.app/'],
            'renamed generated domain' => ['https://old-site.up.railway.app/', 'new-site.up.railway.app', 'https://new-site.up.railway.app/'],
            'local default with public domain' => ['http://localhost/', 'new-site.up.railway.app', 'https://new-site.up.railway.app/'],
            'malformed explicit URL with domain' => ['https://', 'new-site.up.railway.app', 'https://new-site.up.railway.app/'],
            'custom domain retained' => ['https://terminal.example.org/queue', 'new-site.up.railway.app', 'https://terminal.example.org/queue/'],
            'normalization' => [' https://terminal.example.org/ ', '', 'https://terminal.example.org/'],
            'non-web scheme rejected' => ['ftp://terminal.example.org/', '', 'http://localhost/'],
            'credentials rejected' => ['https://user:password@terminal.example.org/', '', 'http://localhost/'],
            'query rejected' => ['https://terminal.example.org/?token=private', '', 'http://localhost/'],
            'fragment rejected' => ['https://terminal.example.org/#section', '', 'http://localhost/'],
            'invalid public domain rejected' => ['', 'new-site.up.railway.app/extra', 'http://localhost/'],
        ];
    }
}
