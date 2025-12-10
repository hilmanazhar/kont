<?php
/**
 * Setup script to add payment_methods column
 * Run once then DELETE this file!
 */

require_once 'api/db.php';

try {
    $pdo = getDB();
    
    echo "<h1>Adding payment_methods column...</h1>";
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'payment_methods'");
    if ($stmt->rowCount() == 0) {
        // Add column
        $pdo->exec("ALTER TABLE users ADD COLUMN payment_methods JSON DEFAULT NULL");
        echo "<p>✅ Column added!</p>";
        
        // Migrate existing data
        $pdo->exec("
            UPDATE users SET payment_methods = JSON_OBJECT(
                'banks', IF(bank_name IS NOT NULL AND bank_name != '', 
                    JSON_ARRAY(JSON_OBJECT('name', bank_name, 'account', COALESCE(bank_account, ''))), 
                    JSON_ARRAY()),
                'ewallets', IF(ewallet_type IS NOT NULL AND ewallet_type != '', 
                    JSON_ARRAY(JSON_OBJECT('type', ewallet_type, 'number', COALESCE(ewallet_number, ''))), 
                    JSON_ARRAY()),
                'qris', IF(qris_image IS NOT NULL AND qris_image != '', 
                    JSON_ARRAY(qris_image), 
                    JSON_ARRAY())
            ) WHERE payment_methods IS NULL
        ");
        echo "<p>✅ Existing data migrated!</p>";
    } else {
        echo "<p>Column already exists!</p>";
    }
    
    echo "<hr><p style='color:red;'>⚠️ DELETE THIS FILE NOW: setup_payment_methods.php</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
