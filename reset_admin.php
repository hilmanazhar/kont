<?php
/**
 * One-time script to reset admin password
 * DELETE THIS FILE AFTER USE!
 */

require_once 'api/db.php';

try {
    $pdo = getDB();
    
    // New password: kontadmin123
    $hash = '$2y$10$oWJK77ji.fKiH7LjE6IrNOqBMF31WRVHZo.lq2U/0buwnioutX2w2';
    
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE role = 'admin'");
    $stmt->execute([$hash]);
    
    $affected = $stmt->rowCount();
    
    echo "<!DOCTYPE html><html><head><title>Admin Password Reset</title></head><body>";
    echo "<h1>✅ Password Admin Berhasil Direset!</h1>";
    echo "<p>$affected user(s) updated</p>";
    echo "<p>Password baru: <strong>kontadmin123</strong></p>";
    echo "<hr>";
    echo "<p style='color:red;'>⚠️ HAPUS FILE INI SETELAH SELESAI!</p>";
    echo "<p>Hapus file <code>reset_admin.php</code> dari repository.</p>";
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
