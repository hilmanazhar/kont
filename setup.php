<?php
/**
 * Database Setup Script
 * Jalankan sekali untuk setup tables
 * HAPUS FILE INI SETELAH SELESAI!
 */

// Railway environment variables
$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$db = getenv('MYSQLDATABASE') ?: 'railway';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';

echo "<h2>Database Setup</h2>";
echo "<pre>";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Connected to database!\n\n";

    // Create tables
    $sql = "
    -- Users table
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        display_name VARCHAR(100) NOT NULL,
        phone_wa VARCHAR(20) DEFAULT NULL,
        role ENUM('admin', 'member') DEFAULT 'member',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Expenses table
    CREATE TABLE IF NOT EXISTS expenses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        paid_by INT NOT NULL,
        amount DECIMAL(12, 2) NOT NULL,
        description VARCHAR(255) NOT NULL,
        category VARCHAR(50) NOT NULL,
        receipt_image VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE CASCADE
    );

    -- Expense splits
    CREATE TABLE IF NOT EXISTS expense_splits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        expense_id INT NOT NULL,
        user_id INT NOT NULL,
        amount DECIMAL(12, 2) NOT NULL,
        is_paid BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );

    -- Settlements
    CREATE TABLE IF NOT EXISTS settlements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        from_user INT NOT NULL,
        to_user INT NOT NULL,
        amount DECIMAL(12, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (from_user) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (to_user) REFERENCES users(id) ON DELETE CASCADE
    );

    -- Notifications
    CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('expense', 'settlement', 'info') DEFAULT 'info',
        related_id INT DEFAULT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );

    -- Info table
    CREATE TABLE IF NOT EXISTS info_kontrakan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );

    -- Payment info table
    CREATE TABLE IF NOT EXISTS payment_info (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        bank_name VARCHAR(100) DEFAULT NULL,
        bank_account VARCHAR(50) DEFAULT NULL,
        ewallet_type VARCHAR(50) DEFAULT NULL,
        ewallet_number VARCHAR(50) DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    ";

    // Execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if (!empty($stmt) && strpos($stmt, '--') !== 0) {
            $pdo->exec($stmt);
            echo "✅ Executed: " . substr($stmt, 0, 50) . "...\n";
        }
    }

    // Check if users exist
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    if ($count == 0) {
        echo "\n📝 Inserting default users...\n";
        
        // Insert default users - password: kontrakan123
        $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        
        $users = [
            ['admin', $hash, 'Admin Kontrakan', 'admin'],
            ['hilman', $hash, 'Hilman', 'member'],
            ['arkan', $hash, 'Arkan', 'member'],
            ['rafli', $hash, 'Rafli', 'member'],
            ['rafi', $hash, 'Rafi', 'member'],
            ['kahfi', $hash, 'Kahfi', 'member'],
            ['alromy', $hash, 'Al Romy', 'member'],
            ['lutfan', $hash, 'Lutfan', 'member'],
        ];
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, display_name, role) VALUES (?, ?, ?, ?)");
        foreach ($users as $u) {
            $stmt->execute($u);
            echo "✅ Created user: {$u[0]}\n";
        }
    } else {
        echo "\n✅ Users already exist ($count users)\n";
    }

    echo "\n🎉 SETUP COMPLETE!\n";
    echo "\n⚠️  HAPUS FILE INI SETELAH SELESAI (setup.php)\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
