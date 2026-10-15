<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SecurityHeaders extends \CodeIgniter\Filters\SecureHeaders
{
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        parent::after($request, $response, $arguments);
        $path = ltrim($request->getUri()->getPath(), '/');
        $basePath = trim((string) parse_url(config('App')->baseURL, PHP_URL_PATH), '/');
        if ($basePath !== '' && str_starts_with($path, $basePath . '/')) $path = substr($path, strlen($basePath) + 1);
        if (preg_match('~^(admin|staff|api|auth|profile|contact|history)(/|$)|^(login|logout|forgot-password|reset-password|verify-reset-code|submit-reset-code|resend-reset-code|send-reset-code|change-password|send-change-password-code|verify-change-password-code)(/|$)~', $path)) {
            $response->setHeader('X-Robots-Tag', 'noindex, nofollow');
        }
        if ($path === 'guest' && (is_array(session()->get('guest_contact_pending')) || is_string(session()->getFlashdata('contact_gmail_url')) || is_array(session()->getFlashdata('_ci_old_input')))) {
            $response->setHeader('Cache-Control', 'no-store');
        }
        return $response;
    }
}
