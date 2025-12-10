<?php
/**
 * Database Setup Script - Import from kontrakan_db.sql
 * HAPUS FILE INI SETELAH SELESAI!
 */

$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$db = getenv('MYSQLDATABASE') ?: 'railway';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';

echo "<h2>🚀 Database Setup</h2>";
echo "<pre style='background:#111;color:#0f0;padding:20px;'>";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Connected to database: $db@$host:$port\n\n";

    // Read and execute SQL file
    $sqlFile = __DIR__ . '/database/kontrakan_db.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);
    
    // Remove comments and split by semicolon
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $count = 0;
    $errors = 0;
    
    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        
        // Skip some statements that might cause issues
        if (stripos($stmt, 'SET SQL_MODE') !== false) continue;
        if (stripos($stmt, 'SET time_zone') !== false) continue;
        if (stripos($stmt, '/*!') !== false) continue;
        
        try {
            $pdo->exec($stmt);
            $count++;
            
            // Show progress for important statements
            if (stripos($stmt, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?`(\w+)`/i', $stmt, $m);
                echo "✅ Created table: " . ($m[1] ?? 'unknown') . "\n";
            } elseif (stripos($stmt, 'INSERT INTO') !== false) {
                preg_match('/INSERT INTO `(\w+)`/i', $stmt, $m);
                echo "📝 Inserted data into: " . ($m[1] ?? 'unknown') . "\n";
            }
        } catch (PDOException $e) {
            // Ignore duplicate/exists errors
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate') === false) {
                echo "⚠️ Warning: " . substr($e->getMessage(), 0, 80) . "\n";
                $errors++;
            }
        }
    }

    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🎉 SETUP COMPLETE!\n";
    echo "✅ Executed: $count statements\n";
    if ($errors > 0) echo "⚠️ Warnings: $errors\n";
    
    // Verify tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "\n📋 Tables created: " . implode(', ', $tables) . "\n";
    
    // Count users
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "👥 Users: $userCount\n";
    
    echo "\n⚠️ HAPUS FILE INI SETELAH SELESAI!\n";
    echo "Delete setup.php from your repo\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
