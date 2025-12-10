<?php
/**
 * Router for PHP built-in server
 * Handles static files and routes PHP requests
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Remove query string
$file = __DIR__ . $path;

// If it's a directory, look for index.html or index.php
if (is_dir($file)) {
    if (file_exists($file . '/index.html')) {
        return false; // Let built-in server handle it
    }
    if (file_exists($file . '/index.php')) {
        include $file . '/index.php';
        return true;
    }
}

// If file exists and is not PHP, let built-in server handle it
if (is_file($file)) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if ($ext !== 'php') {
        return false; // Static file - let server handle
    }
    // Execute PHP file
    include $file;
    return true;
}

// Default: serve index.html for SPA routing
if (file_exists(__DIR__ . '/index.html')) {
    include __DIR__ . '/index.html';
    return true;
}

// 404
http_response_code(404);
echo "Not Found";
