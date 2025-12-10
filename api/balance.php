<?php
/**
 * Balance API v2 - Direct person-to-person debt tracking
 * - GET : Calculate who owes whom directly
 */

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

requireAuth();

$pdo = getDB();

// Get all users
$users = $pdo->query("SELECT id, display_name FROM users")->fetchAll();
$userMap = [];
foreach ($users as $u) {
    $userMap[$u['id']] = $u['display_name'];
}

// Initialize debt matrix: debt[from][to] = amount that 'from' owes to 'to'
$debtMatrix = [];
foreach ($users as $u1) {
    foreach ($users as $u2) {
        if ($u1['id'] != $u2['id']) {
            $debtMatrix[$u1['id']][$u2['id']] = 0;
        }
    }
}

// Process all expenses (EXCLUDE Listrik - rotation system)
$stmt = $pdo->query("
    SELECT e.id, e.paid_by, e.amount, e.category
    FROM expenses e
    WHERE e.category != 'Listrik'
");
$expenses = $stmt->fetchAll();

foreach ($expenses as $expense) {
    $payerId = $expense['paid_by'];
    
    // Get splits for this expense
    $splitStmt = $pdo->prepare("SELECT user_id, amount FROM expense_splits WHERE expense_id = ?");
    $splitStmt->execute([$expense['id']]);
    $splits = $splitStmt->fetchAll();
    
    foreach ($splits as $split) {
        $owerId = $split['user_id'];
        $splitAmount = (float)$split['amount'];
        
        // If someone else (not the payer) has a split, they owe the payer
        if ($owerId != $payerId && $splitAmount > 0) {
            $debtMatrix[$owerId][$payerId] += $splitAmount;
        }
    }
}

// Process settlements (reduce debt)
$stmt = $pdo->query("SELECT from_user, to_user, amount FROM settlements");
$settlements = $stmt->fetchAll();

foreach ($settlements as $s) {
    $fromUser = $s['from_user'];
    $toUser = $s['to_user'];
    $amount = (float)$s['amount'];
    
    // Settlement reduces debt from the payer to the receiver
    $debtMatrix[$fromUser][$toUser] -= $amount;
}

// Simplify debts: if A owes B $100 and B owes A $30, result: A owes B $70
$netDebts = [];
foreach ($users as $u1) {
    foreach ($users as $u2) {
        if ($u1['id'] < $u2['id']) {
            $debt1to2 = $debtMatrix[$u1['id']][$u2['id']] ?? 0;
            $debt2to1 = $debtMatrix[$u2['id']][$u1['id']] ?? 0;
            $net = $debt1to2 - $debt2to1;
            
            if (abs($net) > 0.01) { // Only include non-zero debts
                if ($net > 0) {
                    // u1 owes u2
                    $netDebts[] = [
                        'from_user_id' => $u1['id'],
                        'from_name' => $userMap[$u1['id']],
                        'to_user_id' => $u2['id'],
                        'to_name' => $userMap[$u2['id']],
                        'amount' => round($net, 2)
                    ];
                } else {
                    // u2 owes u1
                    $netDebts[] = [
                        'from_user_id' => $u2['id'],
                        'from_name' => $userMap[$u2['id']],
                        'to_user_id' => $u1['id'],
                        'to_name' => $userMap[$u1['id']],
                        'amount' => round(abs($net), 2)
                    ];
                }
            }
        }
    }
}

// Calculate overall balance per user
$balances = [];
foreach ($users as $user) {
    $userId = $user['id'];
    $owesToOthers = 0;
    $owedByOthers = 0;
    
    // Sum what this user owes to others
    foreach ($netDebts as $d) {
        if ($d['from_user_id'] == $userId) {
            $owesToOthers += $d['amount'];
        }
        if ($d['to_user_id'] == $userId) {
            $owedByOthers += $d['amount'];
        }
    }
    
    $balance = $owedByOthers - $owesToOthers; // Positive = others owe me
    
    $balances[] = [
        'user_id' => $userId,
        'display_name' => $user['display_name'],
        'balance' => round($balance, 2)
    ];
}

// Sort by balance descending
usort($balances, function($a, $b) {
    return $b['balance'] <=> $a['balance'];
});

jsonResponse([
    'balances' => $balances,
    'settlement_suggestions' => $netDebts
]);
