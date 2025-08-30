<?php
/**
 * Test Admin Users in Database
 * Database-də admin istifadəçilərini yoxlayır
 */

require_once __DIR__ . '/config/database.php';

// Initialize database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "Database bağlantı xətası!\n";
    exit(1);
}

echo "=== ADMIN USERS TEST ===\n";

try {
    // Get all users
    $stmt = $db->prepare("SELECT id, username, email, role, status, created_at FROM users ORDER BY id");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo "Total users: " . count($users) . "\n\n";
    
    foreach ($users as $user) {
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Status: " . $user['status'] . "\n";
        echo "Created: " . $user['created_at'] . "\n";
        echo "---\n";
    }
    
    // Test password verification for admin users
    echo "\n=== PASSWORD VERIFICATION TEST ===\n";
    
    $testPasswords = [
        'admin@prolinege.com' => 'admin123',
        'info@prolinege.com' => '123456'
    ];
    
    foreach ($testPasswords as $email => $password) {
        $stmt = $db->prepare("SELECT username, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $isValid = password_verify($password, $user['password']);
            echo "User: " . $user['username'] . " (" . $user['email'] . ")\n";
            echo "Password '" . $password . "': " . ($isValid ? 'VALID' : 'INVALID') . "\n";
            echo "Hash: " . $user['password'] . "\n\n";
        } else {
            echo "User not found: " . $email . "\n\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";
?>