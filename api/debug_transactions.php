<?php
/**
 * Debug script untuk analisis transaksi antara 2 user
 */
require_once 'db.php';

$creditorId = $_GET['creditor_id'] ?? 1;
$debtorId = $_GET['debtor_id'] ?? 2;

$pdo = getDB();

echo "<h2>Transaksi: Creditor=$creditorId, Debtor=$debtorId</h2>";

// Expenses where creditor paid for debtor
$stmt = $pdo->prepare("
    SELECT e.id, e.description, e.category, e.created_at, es.amount as split_amount
    FROM expenses e
    JOIN expense_splits es ON e.id = es.expense_id
    WHERE e.paid_by = ? AND es.user_id = ? AND e.category != 'Listrik'
    ORDER BY e.created_at ASC
");
$stmt->execute([$creditorId, $debtorId]);
$expenses = $stmt->fetchAll();

echo "<h3>Expenses (Creditor bayar untuk Debtor):</h3>";
echo "<table border='1'><tr><th>ID</th><th>Desc</th><th>Amount</th><th>Date</th></tr>";
$totalExp = 0;
foreach ($expenses as $e) {
    echo "<tr><td>{$e['id']}</td><td>{$e['description']}</td><td>{$e['split_amount']}</td><td>{$e['created_at']}</td></tr>";
    $totalExp += (float)$e['split_amount'];
}
echo "</table>";
echo "<p><strong>Total Expenses: $totalExp</strong></p>";

// Settlements where debtor paid creditor
$stmt = $pdo->prepare("
    SELECT id, amount, created_at
    FROM settlements
    WHERE from_user = ? AND to_user = ?
    ORDER BY created_at ASC
");
$stmt->execute([$debtorId, $creditorId]);
$settlements = $stmt->fetchAll();

echo "<h3>Settlements (Debtor bayar ke Creditor):</h3>";
echo "<table border='1'><tr><th>ID</th><th>Amount</th><th>Date</th></tr>";
$totalSet = 0;
foreach ($settlements as $s) {
    echo "<tr><td>{$s['id']}</td><td>{$s['amount']}</td><td>{$s['created_at']}</td></tr>";
    $totalSet += (float)$s['amount'];
}
echo "</table>";
echo "<p><strong>Total Settlements: $totalSet</strong></p>";

// Running balance simulation
echo "<h3>Running Balance:</h3>";
echo "<table border='1'><tr><th>#</th><th>Type</th><th>Amount</th><th>Date</th><th>Balance After</th><th>Reset?</th></tr>";

$transactions = [];
foreach ($expenses as $e) {
    $transactions[] = [
        'type' => 'expense',
        'amount' => (float)$e['split_amount'],
        'date' => $e['created_at'],
        'desc' => $e['description']
    ];
}
foreach ($settlements as $s) {
    $transactions[] = [
        'type' => 'settlement',
        'amount' => (float)$s['amount'],
        'date' => $s['created_at'],
        'desc' => 'Pembayaran'
    ];
}

usort($transactions, function($a, $b) {
    $diff = strtotime($a['date']) - strtotime($b['date']);
    if ($diff !== 0) return $diff;
    // Expenses before settlements on same timestamp
    if ($a['type'] === 'expense' && $b['type'] === 'settlement') return -1;
    if ($a['type'] === 'settlement' && $b['type'] === 'expense') return 1;
    return 0;
});

$balance = 0;
$lastResetIdx = -1;
$lastResetDate = null;

foreach ($transactions as $idx => $t) {
    if ($t['type'] === 'expense') {
        $balance += $t['amount'];
    } else {
        $balance -= $t['amount'];
    }
    
    $isReset = abs($balance) < 0.01 || $balance < 0;
    if ($isReset) {
        $lastResetIdx = $idx;
        $lastResetDate = $t['date'];
        $balance = 0;
    }
    
    $color = $isReset ? 'background:lightgreen' : '';
    echo "<tr style='$color'><td>$idx</td><td>{$t['type']}</td><td>{$t['amount']}</td><td>{$t['date']}</td><td>$balance</td><td>" . ($isReset ? 'YES' : '') . "</td></tr>";
}
echo "</table>";

echo "<h3>Result:</h3>";
echo "<p>Last Reset Index: $lastResetIdx</p>";
echo "<p>Last Reset Date: $lastResetDate</p>";
echo "<p>Net Remaining: " . ($totalExp - $totalSet) . "</p>";
