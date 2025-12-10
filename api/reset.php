<?php
/**
 * Reset API - Admin Only
 * - POST : Reset various data (transaksi, info, settlements, or all)
 */

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Require admin authentication
$userId = requireAuth();
$pdo = getDB();

// Check if user is admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'admin') {
    jsonResponse(['error' => 'Unauthorized - Admin only'], 403);
}

$input = getInput();
$type = $input['type'] ?? null;

if (!$type) {
    jsonResponse(['error' => 'Type is required'], 400);
}

try {
    $pdo->beginTransaction();
    
    switch ($type) {
        case 'transaksi':
            // Reset expenses and expense_splits
            $pdo->exec("DELETE FROM expense_splits");
            $pdo->exec("DELETE FROM expenses");
            $pdo->exec("ALTER TABLE expenses AUTO_INCREMENT = 1");
            $pdo->exec("ALTER TABLE expense_splits AUTO_INCREMENT = 1");
            $message = 'Semua transaksi berhasil dihapus';
            break;
            
        case 'info':
            // Reset info_kontrakan
            $pdo->exec("DELETE FROM info_kontrakan");
            $pdo->exec("ALTER TABLE info_kontrakan AUTO_INCREMENT = 1");
            $message = 'Semua info berhasil dihapus';
            break;
            
        case 'settlements':
            // Reset settlements only
            $pdo->exec("DELETE FROM settlements");
            $pdo->exec("ALTER TABLE settlements AUTO_INCREMENT = 1");
            $message = 'Semua riwayat pembayaran berhasil dihapus';
            break;
            
        case 'notifications':
            // Reset notifications
            $pdo->exec("DELETE FROM notifications");
            $pdo->exec("ALTER TABLE notifications AUTO_INCREMENT = 1");
            $message = 'Semua notifikasi berhasil dihapus';
            break;
            
        case 'all':
            // Reset everything except users
            $pdo->exec("DELETE FROM expense_splits");
            $pdo->exec("DELETE FROM expenses");
            $pdo->exec("DELETE FROM settlements");
            $pdo->exec("DELETE FROM notifications");
            $pdo->exec("DELETE FROM info_kontrakan");
            
            // Reset auto increment
            $pdo->exec("ALTER TABLE expenses AUTO_INCREMENT = 1");
            $pdo->exec("ALTER TABLE expense_splits AUTO_INCREMENT = 1");
            $pdo->exec("ALTER TABLE settlements AUTO_INCREMENT = 1");
            $pdo->exec("ALTER TABLE notifications AUTO_INCREMENT = 1");
            $pdo->exec("ALTER TABLE info_kontrakan AUTO_INCREMENT = 1");
            
            $message = 'Reset sistem berhasil - Semua data kecuali users dihapus';
            break;
            
        default:
            $pdo->rollBack();
            jsonResponse(['error' => 'Invalid type'], 400);
    }
    
    $pdo->commit();
    jsonResponse(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['error' => 'Reset failed: ' . $e->getMessage()], 500);
}
