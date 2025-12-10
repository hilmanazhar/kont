<?php
/**
 * Setup script to add receipt_image column to settlements table
 * Run once then DELETE this file!
 */

require_once 'api/db.php';

try {
    $pdo = getDB();
    
    echo "<h1>Adding receipt_image column to settlements...</h1>";
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM settlements LIKE 'receipt_image'");
    if ($stmt->rowCount() == 0) {
        // Add column
        $pdo->exec("ALTER TABLE settlements ADD COLUMN receipt_image VARCHAR(255) DEFAULT NULL AFTER amount");
        echo "<p>✅ Column receipt_image added!</p>";
    } else {
        echo "<p>Column already exists!</p>";
    }
    
    echo "<hr><p style='color:red;'>⚠️ DELETE THIS FILE NOW: setup_receipt.php</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
