<?php

namespace App\Libraries;

/** Resolve a deployment URL without trusting an incoming request's Host. */
final class DeploymentUrl
{
    public static function resolve(string $configured, string $railwayDomain = ''): string
    {
        $url = self::normalize($configured);
        $domain = strtolower(trim($railwayDomain));
        $railwayUrl = preg_match('/\A[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?\z/D', $domain)
            ? self::normalize('https://' . $domain . '/')
            : null;

        // A renamed Railway domain replaces an old generated address. An
        // explicitly configured custom domain or subdirectory stays canonical.
        $host = strtolower((string) parse_url($url ?? '', PHP_URL_HOST));
        $generatedHost = str_ends_with($host, '.up.railway.app')
            || str_ends_with($host, '.railway.app');
        $loopbackHost = in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);
        if ($railwayUrl !== null && ($url === null || $generatedHost || $loopbackHost)) {
            return $railwayUrl;
        }

        // Background CLI services still need a valid URL before a public
        // domain is created, or after it has been removed.
        return $url ?? 'http://localhost/';
    }

    public static function normalize(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return null;
        }
        $parts = parse_url($value);
        if ($parts === false || !isset($parts['host'])
            || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || isset($parts['user']) || isset($parts['pass'])
            || isset($parts['query']) || isset($parts['fragment'])) {
            return null;
        }

        return rtrim($value, '/') . '/';
    }
}
