<?php
/**
 * Expenses API
 * - GET : List all expenses
 * - POST : Create new expense
 * - DELETE ?id=X : Delete expense (admin or owner)
 */

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        listExpenses();
        break;
    case 'POST':
        createExpense();
        break;
    case 'DELETE':
        deleteExpense();
        break;
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

function listExpenses() {
    requireAuth();
    
    $pdo = getDB();
    
    // Get filter params
    $category = $_GET['category'] ?? null;
    $limit = (int)($_GET['limit'] ?? 50);
    
    $sql = "SELECT e.*, u.display_name as paid_by_name 
            FROM expenses e 
            JOIN users u ON e.paid_by = u.id";
    $params = [];
    
    if ($category) {
        $sql .= " WHERE e.category = ?";
        $params[] = $category;
    }
    
    $sql .= " ORDER BY e.created_at DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $expenses = $stmt->fetchAll();
    
    // Get splits for each expense
    foreach ($expenses as &$expense) {
        $stmt = $pdo->prepare("
            SELECT es.*, u.display_name 
            FROM expense_splits es 
            JOIN users u ON es.user_id = u.id 
            WHERE es.expense_id = ?
        ");
        $stmt->execute([$expense['id']]);
        $expense['splits'] = $stmt->fetchAll();
    }
    
    jsonResponse(['expenses' => $expenses]);
}

function createExpense() {
    $userId = requireAuth();
    $input = getInput();
    
    // Validate required fields
    $required = ['amount', 'description', 'category'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            jsonResponse(['error' => "Field '$field' is required"], 400);
        }
    }
    
    $amount = (float)$input['amount'];
    $description = $input['description'];
    $category = $input['category'];
    $splits = $input['splits'] ?? []; // array of {user_id, amount} - can be empty for Listrik
    $receiptImage = $input['receipt_image'] ?? null;
    
    // Require splits for non-Listrik categories
    if ($category !== 'Listrik' && empty($splits)) {
        jsonResponse(['error' => "Field 'splits' is required"], 400);
    }
    
    if ($amount <= 0) {
        jsonResponse(['error' => 'Amount must be greater than 0'], 400);
    }
    
    $pdo = getDB();
    
    try {
        $pdo->beginTransaction();
        
        // Insert expense
        $stmt = $pdo->prepare("
            INSERT INTO expenses (paid_by, amount, description, category, receipt_image) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $amount, $description, $category, $receiptImage]);
        $expenseId = $pdo->lastInsertId();
        
        // Insert splits
        $stmtSplit = $pdo->prepare("
            INSERT INTO expense_splits (expense_id, user_id, amount) VALUES (?, ?, ?)
        ");
        
        $stmtNotif = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type, related_id) 
            VALUES (?, ?, ?, 'expense', ?)
        ");
        
        $payerName = $_SESSION['display_name'];
        
        foreach ($splits as $split) {
            $splitUserId = (int)$split['user_id'];
            $splitAmount = (float)$split['amount'];
            
            $stmtSplit->execute([$expenseId, $splitUserId, $splitAmount]);
            
            // Send notification to each user (except the payer)
            if ($splitUserId !== $userId) {
                $notifTitle = "Pengeluaran Baru";
                $notifMsg = "$payerName nalangin $category: $description sebesar Rp " . number_format($splitAmount, 0, ',', '.');
                $stmtNotif->execute([$splitUserId, $notifTitle, $notifMsg, $expenseId]);
            }
        }
        
        $pdo->commit();
        
        jsonResponse([
            'success' => true, 
            'message' => 'Expense created',
            'expense_id' => $expenseId
        ], 201);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Failed to create expense: ' . $e->getMessage()], 500);
    }
}

function deleteExpense() {
    $userId = requireAuth();
    $expenseId = (int)($_GET['id'] ?? 0);
    
    if (!$expenseId) {
        jsonResponse(['error' => 'Expense ID required'], 400);
    }
    
    $pdo = getDB();
    
    // Check if user is owner or admin
    $stmt = $pdo->prepare("SELECT paid_by FROM expenses WHERE id = ?");
    $stmt->execute([$expenseId]);
    $expense = $stmt->fetch();
    
    if (!$expense) {
        jsonResponse(['error' => 'Expense not found'], 404);
    }
    
    if ($expense['paid_by'] !== $userId && $_SESSION['user_role'] !== 'admin') {
        jsonResponse(['error' => 'Not authorized to delete this expense'], 403);
    }
    
    // Delete expense (cascade will delete splits)
    $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->execute([$expenseId]);
    
    jsonResponse(['success' => true, 'message' => 'Expense deleted']);
}
