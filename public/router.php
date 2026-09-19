<?php

/**
 * PHP's built-in server calls the router for every request. Let it serve
 * existing public assets directly, and send application routes to CodeIgniter.
 */
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestPath = rawurldecode($requestPath);
$documentRoot = realpath(__DIR__);
$requestedFile = realpath($documentRoot . DIRECTORY_SEPARATOR . ltrim($requestPath, '/\\'));

if (
    $requestedFile !== false
    && $documentRoot !== false
    && strncmp($requestedFile, $documentRoot . DIRECTORY_SEPARATOR, strlen($documentRoot . DIRECTORY_SEPARATOR)) === 0
    && is_file($requestedFile)
) {
    return false;
}

require __DIR__ . '/index.php';
