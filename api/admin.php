<?php
/**
 * Admin API
 * - GET ?action=stats : Get statistics
 * - POST ?action=add-user : Add new member
 * - PUT ?action=reset-password : Reset user password
 * - DELETE ?action=delete-user&id=X : Delete user (with protection)
 * - DELETE ?action=clear-expenses : Clear all expenses
 */

require_once 'db.php';

requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'stats') {
            getStats();
        } else {
            jsonResponse(['error' => 'Invalid action'], 400);
        }
        break;
    case 'POST':
        if ($action === 'add-user') {
            addUser();
        } else {
            jsonResponse(['error' => 'Invalid action'], 400);
        }
        break;
    case 'PUT':
        if ($action === 'reset-password') {
            resetPassword();
        } else {
            jsonResponse(['error' => 'Invalid action'], 400);
        }
        break;
    case 'DELETE':
        if ($action === 'clear-expenses') {
            clearExpenses();
        } elseif ($action === 'delete-user') {
            deleteUser();
        } else {
            jsonResponse(['error' => 'Invalid action'], 400);
        }
        break;
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

function getStats() {
    $pdo = getDB();
    
    // Total expenses
    $totalExpenses = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses")->fetch()['total'];
    
    // Total settlements
    $totalSettlements = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM settlements")->fetch()['total'];
    
    // Expense count
    $expenseCount = $pdo->query("SELECT COUNT(*) as count FROM expenses")->fetch()['count'];
    
    // By category
    $byCategory = $pdo->query("
        SELECT category, COUNT(*) as count, SUM(amount) as total 
        FROM expenses 
        GROUP BY category 
        ORDER BY total DESC
    ")->fetchAll();
    
    // By month
    $byMonth = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total 
        FROM expenses 
        GROUP BY month 
        ORDER BY month DESC 
        LIMIT 6
    ")->fetchAll();
    
    jsonResponse([
        'total_expenses' => (float)$totalExpenses,
        'total_settlements' => (float)$totalSettlements,
        'expense_count' => (int)$expenseCount,
        'by_category' => $byCategory,
        'by_month' => $byMonth
    ]);
}

function addUser() {
    $input = getInput();
    $username = trim($input['username'] ?? '');
    $displayName = trim($input['display_name'] ?? '');
    $password = $input['password'] ?? '';
    
    if (!$username || !$displayName || !$password) {
        jsonResponse(['error' => 'Username, display_name, dan password harus diisi'], 400);
    }
    
    if (strlen($username) < 3) {
        jsonResponse(['error' => 'Username minimal 3 karakter'], 400);
    }
    
    if (strlen($password) < 6) {
        jsonResponse(['error' => 'Password minimal 6 karakter'], 400);
    }
    
    // Check if username already exists
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'Username sudah digunakan'], 400);
    }
    
    // Create user
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, display_name, role) VALUES (?, ?, ?, 'member')");
    $stmt->execute([$username, $hash, $displayName]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Member berhasil ditambahkan',
        'user_id' => $pdo->lastInsertId()
    ], 201);
}

function resetPassword() {
    $input = getInput();
    $userId = (int)($input['user_id'] ?? 0);
    
    if (!$userId) {
        jsonResponse(['error' => 'User ID required'], 400);
    }
    
    // Reset to default password
    $defaultPassword = 'kontrakan123';
    $hash = password_hash($defaultPassword, PASSWORD_DEFAULT);
    
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$hash, $userId]);
    
    jsonResponse([
        'success' => true, 
        'message' => 'Password reset to: kontrakan123'
    ]);
}

function deleteUser() {
    $userId = (int)($_GET['id'] ?? 0);
    
    if (!$userId) {
        jsonResponse(['error' => 'User ID required'], 400);
    }
    
    $pdo = getDB();
    
    // Check if user exists and is not admin
    $stmt = $pdo->prepare("SELECT id, role, display_name FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['error' => 'User tidak ditemukan'], 404);
    }
    
    if ($user['role'] === 'admin') {
        jsonResponse(['error' => 'Tidak bisa menghapus admin'], 403);
    }
    
    // Check if user has any expenses (paid by them)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM expenses WHERE paid_by = ?");
    $stmt->execute([$userId]);
    if ($stmt->fetch()['count'] > 0) {
        jsonResponse(['error' => 'User masih punya transaksi. Hapus transaksi dulu atau reset data.'], 400);
    }
    
    // Check if user has any expense splits
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM expense_splits WHERE user_id = ?");
    $stmt->execute([$userId]);
    if ($stmt->fetch()['count'] > 0) {
        jsonResponse(['error' => 'User masih terlibat dalam pembagian biaya. Hapus transaksi terkait dulu.'], 400);
    }
    
    // Check if user has any settlements
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM settlements WHERE from_user = ? OR to_user = ?");
    $stmt->execute([$userId, $userId]);
    if ($stmt->fetch()['count'] > 0) {
        jsonResponse(['error' => 'User masih punya riwayat pembayaran. Reset pembayaran dulu.'], 400);
    }
    
    // Safe to delete
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    jsonResponse([
        'success' => true,
        'message' => "Member {$user['display_name']} berhasil dihapus"
    ]);
}

function clearExpenses() {
    $pdo = getDB();
    
    // This will cascade delete splits and related notifications
    $pdo->exec("DELETE FROM expenses");
    $pdo->exec("DELETE FROM settlements");
    $pdo->exec("DELETE FROM notifications");
    
    jsonResponse([
        'success' => true,
        'message' => 'All expenses, settlements, and notifications cleared'
    ]);
}

