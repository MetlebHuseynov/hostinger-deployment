<?php
/**
 * Production Authentication Test
 * Production serverində authentication sistemini test etmək üçün
 */

// Load environment variables
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

require_once 'config/database.php';
require_once 'config/config.php';

echo "<h2>Production Authentication Test</h2>";

try {
    $db = getConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Users table exists</p>";
        
        // Check admin users
        $stmt = $db->query("SELECT id, username, email, role FROM users WHERE role = 'admin'");
        $adminUsers = $stmt->fetchAll();
        
        if (count($adminUsers) > 0) {
            echo "<p style='color: green;'>✓ Admin users found: " . count($adminUsers) . "</p>";
            echo "<ul>";
            foreach ($adminUsers as $user) {
                echo "<li>ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>✗ No admin users found</p>";
            
            // Create admin user
            echo "<p>Creating admin user...</p>";
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute(['admin', 'admin@prolinege.com', $hashedPassword, 'admin']);
            
            if ($result) {
                echo "<p style='color: green;'>✓ Admin user created successfully</p>";
                echo "<p><strong>Login credentials:</strong></p>";
                echo "<p>Email: admin@prolinege.com</p>";
                echo "<p>Password: admin123</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to create admin user</p>";
            }
        }
        
        // Test JWT token creation
        echo "<h3>JWT Token Test</h3>";
        $testPayload = [
            'id' => 1,
            'username' => 'admin',
            'email' => 'admin@prolinege.com',
            'role' => 'admin',
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60)
        ];
        
        $token = createJWT($testPayload);
        echo "<p style='color: green;'>✓ JWT token created: " . substr($token, 0, 50) . "...</p>";
        
        // Test token verification
        $decoded = verifyJWT($token);
        if ($decoded) {
            echo "<p style='color: green;'>✓ JWT token verification successful</p>";
        } else {
            echo "<p style='color: red;'>✗ JWT token verification failed</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Users table does not exist</p>";
        echo "<p>Creating users table...</p>";
        
        $createTableSQL = "
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'user') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($db->exec($createTableSQL)) {
            echo "<p style='color: green;'>✓ Users table created successfully</p>";
            
            // Create admin user
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute(['admin', 'admin@prolinege.com', $hashedPassword, 'admin']);
            
            if ($result) {
                echo "<p style='color: green;'>✓ Admin user created successfully</p>";
                echo "<p><strong>Login credentials:</strong></p>";
                echo "<p>Email: admin@prolinege.com</p>";
                echo "<p>Password: admin123</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Failed to create users table</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Environment:</strong> " . ($_ENV['NODE_ENV'] ?? 'not set') . "</p>";
echo "<p><strong>Database Host:</strong> " . ($_ENV['DB_HOST'] ?? 'not set') . "</p>";
echo "<p><strong>Database Name:</strong> " . ($_ENV['DB_NAME'] ?? 'not set') . "</p>";
?>