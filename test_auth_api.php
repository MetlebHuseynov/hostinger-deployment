<?php
/**
 * Test auth API directly
 */

require_once 'config/config.php';
require_once 'config/database.php';

echo "<h2>Auth API Test</h2>";
echo "<hr>";

// Test database connection
echo "<h3>1. Database Connection</h3>";
try {
    $db = getDBConnection();
    echo "✅ Database connected<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    exit;
}

// Test users table
echo "<h3>2. Users Table</h3>";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ Users table exists, count: " . $result['count'] . "<br>";
} catch (Exception $e) {
    echo "❌ Users table error: " . $e->getMessage() . "<br>";
}

// Test admin user
echo "<h3>3. Admin User Check</h3>";
try {
    $stmt = $db->prepare("SELECT id, username, email, password FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✅ Admin user found<br>";
        echo "ID: " . $admin['id'] . "<br>";
        echo "Username: " . $admin['username'] . "<br>";
        echo "Email: " . $admin['email'] . "<br>";
        echo "Password hash length: " . strlen($admin['password']) . "<br>";
        
        // Test password
        $testPassword = '0993991019Matlab?';
        $isValid = password_verify($testPassword, $admin['password']);
        echo "Password test ('0993991019Matlab?'): " . ($isValid ? "✅ VALID" : "❌ INVALID") . "<br>";
    } else {
        echo "❌ Admin user not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Admin user check error: " . $e->getMessage() . "<br>";
}

// Test login API directly
echo "<h3>4. Direct Login API Test</h3>";
try {
    // Simulate POST request
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [];
    
    // Create test input
    $testInput = json_encode([
        'email' => 'admin',
        'password' => '0993991019Matlab?'
    ]);
    
    echo "Test input: " . htmlspecialchars($testInput) . "<br>";
    
    // Test JSON decode
    $decoded = json_decode($testInput, true);
    echo "Decoded input: " . print_r($decoded, true) . "<br>";
    
    // Test auth logic manually
    $emailOrUsername = $decoded['email'] ?? $decoded['username'] ?? null;
    $password = $decoded['password'] ?? null;
    
    echo "Email/Username: " . htmlspecialchars($emailOrUsername) . "<br>";
    echo "Password: " . htmlspecialchars($password) . "<br>";
    
    if ($emailOrUsername && $password) {
        $stmt = $db->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$emailOrUsername, $emailOrUsername]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "✅ User found in database<br>";
            $passwordMatch = password_verify($password, $user['password']);
            echo "Password verification: " . ($passwordMatch ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
            
            if ($passwordMatch) {
                echo "✅ Login would be successful<br>";
                echo "User data: " . json_encode([
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]) . "<br>";
            }
        } else {
            echo "❌ User not found in database<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Login API test error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p>Test completed at: " . date('Y-m-d H:i:s') . "</p>";
?>