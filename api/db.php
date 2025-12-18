<?php
/**
 * Database Connection
 * Supports both local (XAMPP) and Railway production
 */

// Suppress HTML error output - return JSON instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

set_exception_handler(function($e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    exit;
});

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Check if running on Railway or local
if (getenv('RAILWAY_ENVIRONMENT') || getenv('MYSQLHOST')) {
    // Railway: Use environment variables
    define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
    define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
    define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');
    define('DB_USER', getenv('MYSQLUSER') ?: 'root');
    define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
} else {
    // Local development (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'kontrakan_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
    }
    
    return $pdo;
}

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configure persistent sessions (30 days)
$sessionLifetime = 60 * 60 * 24 * 30; // 30 days in seconds

// Only configure and start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters BEFORE session_start
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // Set session garbage collection lifetime
    ini_set('session.gc_maxlifetime', $sessionLifetime);

    session_start();
}

// Regenerate session ID periodically for security (every 30 minutes of activity)
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    return $_SESSION['user_id'];
}

function requireAdmin() {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        jsonResponse(['error' => 'Admin access required'], 403);
    }
}

function getInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}
