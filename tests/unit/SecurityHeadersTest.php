<?php

namespace Tests\Unit;

use App\Filters\SecurityHeaders;
use CodeIgniter\HTTP\ContentSecurityPolicy;
use CodeIgniter\Test\CIUnitTestCase;

final class SecurityHeadersTest extends CIUnitTestCase
{
    public function testEnforcedCspAllowsLocalAssetsAndSameOriginWebSocketWithoutRemoteScripts(): void
    {
        $this->assertTrue(config('App')->CSPEnabled);
        $response = service('response', null, false);
        (new ContentSecurityPolicy(new \Config\ContentSecurityPolicy()))->finalize($response);
        $header = $response->getHeaderLine('Content-Security-Policy');
        foreach (["default-src 'self'", "object-src 'none'", "base-uri 'self'", "form-action 'self'", "frame-ancestors 'self'", "font-src 'self' data:"] as $directive) $this->assertStringContainsString($directive, $header);
        $this->assertStringNotContainsString('unsafe-eval', $header);
        $this->assertStringNotContainsString('script-src https:', $header);
        $this->assertSame('', $response->getHeaderLine('Content-Security-Policy-Report-Only'));
        $url = parse_url(config('App')->baseURL);
        $this->assertStringContainsString(($url['scheme'] === 'https' ? 'wss://' : 'ws://') . $url['host'], $header);
    }

    public function testPrivateRoutesAndRedirectsHaveNoindexAndPublicPagesRemainIndexable(): void
    {
        $filter = new SecurityHeaders();
        foreach (['admin/dashboard','staff/queue','auth/session-status','profile','contact/verify','login','reset-password/token','api/queue-status'] as $path) {
            $request = service('request', config('App'), false);
            $request->getUri()->setPath(parse_url(base_url($path), PHP_URL_PATH));
            $response = service('response', null, false)->setStatusCode(302);
            $filter->after($request, $response);
            $this->assertSame('noindex, nofollow', $response->getHeaderLine('X-Robots-Tag'), $path);
            $this->assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
        }
        foreach (['guest','fares','schedules','search'] as $path) {
            $request = service('request', config('App'), false);
            $request->getUri()->setPath(parse_url(base_url($path), PHP_URL_PATH));
            $response = service('response', null, false);
            $filter->after($request, $response);
            $this->assertSame('', $response->getHeaderLine('X-Robots-Tag'), $path);
        }
        $robots = file_get_contents(FCPATH . 'robots.txt');
        foreach (['/admin','/staff','/contact','/api','/login'] as $path) $this->assertStringContainsString('Disallow: '.$path, $robots);
        $this->assertStringNotContainsString("Disallow:\n", $robots);
    }
}
