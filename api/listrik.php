<?php
/**
 * Listrik Rotation API
 * - GET : Get rotation stats (who paid how many times)
 */

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

requireAuth();

$pdo = getDB();

// Get count of Listrik payments per user (excluding admin)
$stmt = $pdo->query("
    SELECT 
        u.id as user_id,
        u.display_name,
        COUNT(e.id) as payment_count,
        COALESCE(SUM(e.amount), 0) as total_paid
    FROM users u
    LEFT JOIN expenses e ON u.id = e.paid_by AND e.category = 'Listrik'
    WHERE u.role != 'admin'
    GROUP BY u.id
    ORDER BY payment_count DESC
");

$stats = $stmt->fetchAll();

// Get last Listrik payment
$lastStmt = $pdo->query("
    SELECT e.*, u.display_name as paid_by_name
    FROM expenses e
    JOIN users u ON e.paid_by = u.id
    WHERE e.category = 'Listrik'
    ORDER BY e.created_at DESC
    LIMIT 1
");
$lastPayment = $lastStmt->fetch();

// Determine who should pay next (whoever has paid the least)
$minPayer = null;
$minCount = PHP_INT_MAX;
foreach ($stats as $s) {
    if ($s['payment_count'] < $minCount) {
        $minCount = $s['payment_count'];
        $minPayer = $s;
    }
}

jsonResponse([
    'stats' => $stats,
    'last_payment' => $lastPayment,
    'next_payer' => $minPayer,
    'default_amount' => 100000
]);
