<?php
/**
 * Create admin user with correct password hash
 */

require_once 'config/config.php';
require_once 'config/database.php';

echo "<h2>Creating Admin User</h2>";
echo "<hr>";

try {
    $db = getDBConnection();
    echo "✅ Database connected<br>";
    
    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Users table does not exist<br>";
        
        // Create users table
        echo "Creating users table...<br>";
        $createTable = "
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'user') DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $db->exec($createTable);
        echo "✅ Users table created<br>";
    } else {
        echo "✅ Users table exists<br>";
    }
    
    // Check if admin user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "⚠️ Admin user already exists<br>";
        
        // Update admin password
        $newPassword = '0993991019Matlab?';
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->execute([$hashedPassword]);
        
        echo "✅ Admin password updated<br>";
        echo "Username: admin<br>";
        echo "Password: $newPassword<br>";
        echo "Hash: $hashedPassword<br>";
        
    } else {
        // Create admin user
        echo "Creating admin user...<br>";
        
        $username = 'admin';
        $email = 'admin@prolinege.com';
        $password = '0993991019Matlab?';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin';
        
        $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword, $role]);
        
        echo "✅ Admin user created successfully<br>";
        echo "Username: $username<br>";
        echo "Email: $email<br>";
        echo "Password: $password<br>";
        echo "Hash: $hashedPassword<br>";
    }
    
    // Test password verification
    echo "<h3>Password Verification Test</h3>";
    $stmt = $db->prepare("SELECT password FROM users WHERE username = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch();
    
    $testPassword = '0993991019Matlab?';
    $isValid = password_verify($testPassword, $user['password']);
    
    echo "Test password: $testPassword<br>";
    echo "Stored hash: " . $user['password'] . "<br>";
    echo "Verification: " . ($isValid ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p>Completed at: " . date('Y-m-d H:i:s') . "</p>";
?>