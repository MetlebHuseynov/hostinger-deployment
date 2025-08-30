<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database connection...\n";

try {
    require_once 'config/config.php';
    require_once 'config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        echo "Database connection failed!\n";
        exit;
    }
    
    echo "Database connected successfully!\n";
    
    // Test user query
    $stmt = $db->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
    $stmt->execute(['admin', 'admin']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "User found: " . $user['username'] . "\n";
        echo "Password hash: " . substr($user['password'], 0, 20) . "...\n";
        
        // Test password verification
        if (password_verify('admin123', $user['password'])) {
            echo "Password verification: SUCCESS\n";
        } else {
            echo "Password verification: FAILED\n";
        }
    } else {
        echo "User not found!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>