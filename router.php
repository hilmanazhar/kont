<?php
/**
 * Router for PHP built-in server
 * Handles static files and routes PHP requests
 */

// Start session for all requests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$file = __DIR__ . $path;

// Handle API requests - always process PHP files
if (preg_match('/\.php$/', $path)) {
    if (is_file($file)) {
        include $file;
        exit;
    }
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not found']);
    exit;
}

// If it's a directory, look for index files
if (is_dir($file)) {
    if (file_exists($file . '/index.php')) {
        include $file . '/index.php';
        exit;
    }
    if (file_exists($file . '/index.html')) {
        return false; // Let built-in server handle static
    }
}

// If static file exists, let built-in server handle it
if (is_file($file)) {
    return false;
}

// Default: serve index.html for SPA routing
if (file_exists(__DIR__ . '/index.html')) {
    readfile(__DIR__ . '/index.html');
    exit;
}

// 404
http_response_code(404);
echo "Not Found";

