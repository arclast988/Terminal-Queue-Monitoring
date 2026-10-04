<?php
// Use the production configuration/framework serializer for browser fixtures.
require dirname(__DIR__, 2) . '/system/Test/bootstrap.php';
config('App')->baseURL = $argv[1] ?? 'http://localhost/';
$response = service('response', null, false);
(new \CodeIgniter\HTTP\ContentSecurityPolicy(new \Config\ContentSecurityPolicy()))->finalize($response);
echo $response->getHeaderLine('Content-Security-Policy');
