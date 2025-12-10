<?php
/**
 * Settlements API v2
 * - GET : List all settlements
 * - POST : Create new settlement (supports both debtor and creditor confirmation)
 */

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        listSettlements();
        break;
    case 'POST':
        createSettlement();
        break;
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

function listSettlements() {
    requireAuth();
    
    $pdo = getDB();
    $limit = (int)($_GET['limit'] ?? 50);
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
    
    $sql = "
        SELECT s.*, 
               fu.display_name as from_name,
               tu.display_name as to_name
        FROM settlements s
        JOIN users fu ON s.from_user = fu.id
        JOIN users tu ON s.to_user = tu.id
    ";
    
    $params = [];
    
    // Filter by user (either from or to)
    if ($userId) {
        $sql .= " WHERE (s.from_user = ? OR s.to_user = ?)";
        $params[] = $userId;
        $params[] = $userId;
    }
    
    $sql .= " ORDER BY s.created_at DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $settlements = $stmt->fetchAll();
    
    jsonResponse(['settlements' => $settlements]);
}

function createSettlement() {
    $userId = requireAuth();
    $input = getInput();
    
    // Check if this is creditor confirming (from_user is different) or debtor recording (from_user is self)
    $fromUser = (int)($input['from_user'] ?? $userId);
    $toUser = (int)($input['to_user'] ?? 0);
    $amount = (float)($input['amount'] ?? 0);
    
    // If from_user is not provided, it means debtor is recording their own payment
    if (!isset($input['from_user'])) {
        $fromUser = $userId;
    }
    
    if (!$toUser || $amount <= 0) {
        jsonResponse(['error' => 'Invalid to_user or amount'], 400);
    }
    
    if ($toUser === $fromUser) {
        jsonResponse(['error' => 'Cannot settle to yourself'], 400);
    }
    
    // Security: Only allow if user is either the payer (from) or receiver (to)
    if ($userId !== $fromUser && $userId !== $toUser) {
        jsonResponse(['error' => 'Not authorized to record this settlement'], 403);
    }
    
    $pdo = getDB();
    
    // Verify both users exist
    $stmt = $pdo->prepare("SELECT id, display_name FROM users WHERE id IN (?, ?)");
    $stmt->execute([$fromUser, $toUser]);
    $users = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    if (count($users) < 2) {
        jsonResponse(['error' => 'User not found'], 404);
    }
    
    try {
        $pdo->beginTransaction();
        
        // Create settlement
        $stmt = $pdo->prepare("INSERT INTO settlements (from_user, to_user, amount) VALUES (?, ?, ?)");
        $stmt->execute([$fromUser, $toUser, $amount]);
        $settlementId = $pdo->lastInsertId();
        
        // Notify the other party
        $amountFormatted = number_format($amount, 0, ',', '.');
        
        if ($userId === $fromUser) {
            // Debtor recorded -> notify creditor
            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, title, message, type, related_id)
                VALUES (?, 'Pembayaran Diterima', ?, 'settlement', ?)
            ");
            $stmt->execute([
                $toUser,
                "{$users[$fromUser]} sudah membayar Rp $amountFormatted ke kamu",
                $settlementId
            ]);
        } else {
            // Creditor confirmed -> notify debtor
            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, title, message, type, related_id)
                VALUES (?, 'Pembayaran Dikonfirmasi', ?, 'settlement', ?)
            ");
            $stmt->execute([
                $fromUser,
                "{$users[$toUser]} mengkonfirmasi pembayaran Rp $amountFormatted dari kamu",
                $settlementId
            ]);
        }
        
        $pdo->commit();
        
        jsonResponse([
            'success' => true,
            'message' => 'Settlement recorded',
            'settlement_id' => $settlementId
        ], 201);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Failed to create settlement: ' . $e->getMessage()], 500);
    }
}
