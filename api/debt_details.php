<?php
/**
 * Debt Details API v11 - Bidirectional Reset Detection
 * 
 * This version considers BOTH directions of transactions between two users
 * to find the reset point. When the NET balance (considering both debts) 
 * reaches 0, we reset and only show transactions after that point.
 */

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

requireAuth();

$creditorId = $_GET['creditor_id'] ?? null;
$debtorId = $_GET['debtor_id'] ?? null;
$debug = isset($_GET['debug']);

if (!$creditorId || !$debtorId) {
    jsonResponse(['error' => 'creditor_id and debtor_id required'], 400);
}

$pdo = getDB();

// ============================================
// STEP 1: Get ALL transactions between the two users (BOTH DIRECTIONS)
// ============================================

// Expenses where User A paid for User B (A = creditor, B = debtor)
$stmt = $pdo->prepare("
    SELECT e.id, e.description, e.category, e.created_at, es.amount as split_amount, 
           e.paid_by as creditor, es.user_id as debtor
    FROM expenses e
    JOIN expense_splits es ON e.id = es.expense_id
    WHERE ((e.paid_by = ? AND es.user_id = ?) OR (e.paid_by = ? AND es.user_id = ?))
    AND e.category != 'Listrik'
    ORDER BY e.created_at ASC
");
$stmt->execute([$creditorId, $debtorId, $debtorId, $creditorId]);
$allExpenses = $stmt->fetchAll();

// Settlements between both users (both directions)
$stmt = $pdo->prepare("
    SELECT id, from_user, to_user, amount, created_at
    FROM settlements
    WHERE (from_user = ? AND to_user = ?) OR (from_user = ? AND to_user = ?)
    ORDER BY created_at ASC
");
$stmt->execute([$creditorId, $debtorId, $debtorId, $creditorId]);
$allSettlements = $stmt->fetchAll();

// ============================================
// STEP 2: Build unified transaction list with NET effect on "creditor owes debtor"
// Positive = creditor is owed by debtor (debtor owes creditor)
// Negative = creditor owes debtor
// ============================================

$transactions = [];

foreach ($allExpenses as $e) {
    // Effect on NET balance (from perspective of requested creditor)
    // If creditor paid for debtor -> debtor owes more to creditor (+)
    // If debtor paid for creditor -> creditor owes more to debtor (-)
    $netEffect = 0;
    if ($e['creditor'] == $creditorId && $e['debtor'] == $debtorId) {
        $netEffect = (float)$e['split_amount']; // Debtor owes more
    } else {
        $netEffect = -(float)$e['split_amount']; // Creditor owes more to debtor
    }
    
    $transactions[] = [
        'date' => $e['created_at'],
        'timestamp' => strtotime($e['created_at']),
        'type' => 'expense',
        'direction' => ($e['creditor'] == $creditorId) ? 'creditor_paid' : 'debtor_paid',
        'net_effect' => $netEffect,
        'amount' => (float)$e['split_amount'],
        'data' => $e
    ];
}

foreach ($allSettlements as $s) {
    // Settlement from X to Y means X is paying their debt to Y
    // Settlement from debtor to creditor -> debtor owes less (-)
    // Settlement from creditor to debtor -> creditor paid debt, so creditor owes less to debtor (+)
    $netEffect = 0;
    if ($s['from_user'] == $debtorId && $s['to_user'] == $creditorId) {
        $netEffect = -(float)$s['amount']; // Debtor paid, owes less
    } else {
        $netEffect = (float)$s['amount']; // Creditor paid their debt to debtor
    }
    
    $transactions[] = [
        'date' => $s['created_at'],
        'timestamp' => strtotime($s['created_at']),
        'type' => 'settlement',
        'direction' => ($s['from_user'] == $debtorId) ? 'debtor_paid' : 'creditor_paid',
        'net_effect' => $netEffect,
        'amount' => (float)$s['amount'],
        'data' => $s
    ];
}

// Sort by date, then expenses before settlements on same timestamp
usort($transactions, function($a, $b) {
    $diff = $a['timestamp'] - $b['timestamp'];
    if ($diff !== 0) return $diff;
    if ($a['type'] === 'expense' && $b['type'] === 'settlement') return -1;
    if ($a['type'] === 'settlement' && $b['type'] === 'expense') return 1;
    return 0;
});

// ============================================
// STEP 3: Find last RESET point (when NET balance hit 0 or crossed 0)
// ============================================

$runningBalance = 0; // + means debtor owes creditor, - means creditor owes debtor
$lastResetIndex = -1;
$lastResetDate = null;
$debugLog = [];

for ($i = 0; $i < count($transactions); $i++) {
    $t = $transactions[$i];
    $before = $runningBalance;
    $runningBalance += $t['net_effect'];
    
    // Check if balance crossed zero or is zero
    $isReset = abs($runningBalance) < 0.01;
    
    $debugLog[] = [
        'idx' => $i,
        'type' => $t['type'],
        'direction' => $t['direction'],
        'amount' => $t['amount'],
        'net_effect' => $t['net_effect'],
        'date' => $t['date'],
        'before' => round($before, 2),
        'after' => round($runningBalance, 2),
        'is_reset' => $isReset
    ];
    
    if ($isReset) {
        $lastResetIndex = $i;
        $lastResetDate = $t['date'];
        $runningBalance = 0;
    }
}

// ============================================
// STEP 4: Extract only transactions AFTER reset that are relevant to THIS direction
// (creditor paid for debtor = expenses we show, debtor paid creditor = settlements we show)
// ============================================

$activeExpenses = [];
$activeSettlements = [];
$totalExpenses = 0;
$totalSettled = 0;

for ($i = $lastResetIndex + 1; $i < count($transactions); $i++) {
    $t = $transactions[$i];
    
    // Only include transactions in the requested direction
    if ($t['type'] === 'expense' && $t['direction'] === 'creditor_paid') {
        $t['data']['remaining_amount'] = $t['amount'];
        $activeExpenses[] = $t['data'];
        $totalExpenses += $t['amount'];
    } else if ($t['type'] === 'settlement' && $t['direction'] === 'debtor_paid') {
        $activeSettlements[] = $t['data'];
        $totalSettled += $t['amount'];
    }
}

// Sort newest first for display
$activeExpenses = array_reverse($activeExpenses);
$activeSettlements = array_reverse($activeSettlements);

$netRemaining = $totalExpenses - $totalSettled;

$response = [
    'expenses' => array_slice($activeExpenses, 0, 10),
    'settlements' => array_slice($activeSettlements, 0, 5),
    'total_expenses' => round($totalExpenses, 2),
    'total_settled' => round($totalSettled, 2),
    'remaining' => round(max(0, $netRemaining), 2),
    'is_clear' => $netRemaining <= 0.01,
    'last_reset_date' => $lastResetDate
];

if ($debug) {
    $response['debug'] = [
        'last_reset_index' => $lastResetIndex,
        'last_reset_date' => $lastResetDate,
        'total_transactions' => count($transactions),
        'active_expense_count' => count($activeExpenses),
        'active_settlement_count' => count($activeSettlements),
        'log' => $debugLog
    ];
}

jsonResponse($response);
