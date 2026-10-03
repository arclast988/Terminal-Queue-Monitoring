<?php

namespace App\Filters;

use App\Libraries\InitialStyles;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

final class InitialStylesFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $body = $response->getBody();
        if (is_string($body) && str_contains(strtolower($response->getHeaderLine('Content-Type')), 'text/html')) {
            $response->setBody(InitialStyles::prepare($body));
        }

        return $response;
    }
}
