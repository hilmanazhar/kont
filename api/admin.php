<?php
/**
 * Admin API
 * - GET ?action=stats : Get statistics
 * - PUT ?action=reset-password : Reset user password
 * - DELETE ?action=user&id=X : Delete user (not recommended)
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
