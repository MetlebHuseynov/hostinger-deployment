<?php
/**
 * Simple login test file
 * Tests database connection and password hashing
 */

require_once 'config/config.php';
require_once 'config/database.php';

echo "<h2>Login Test Results</h2>";
echo "<hr>";

// Test database connection
echo "<h3>1. Database Connection Test</h3>";
try {
    $db = getDBConnection();
    echo "✅ Database connection successful<br>";
    echo "Database type: " . $db->getAttribute(PDO::ATTR_DRIVER_NAME) . "<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test users table
echo "<h3>2. Users Table Test</h3>";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ Users table exists<br>";
    echo "Total users: " . $result['count'] . "<br>";
} catch (Exception $e) {
    echo "❌ Users table error: " . $e->getMessage() . "<br>";
}

// List all users
echo "<h3>3. All Users</h3>";
try {
    $stmt = $db->query("SELECT id, username, email, role, password FROM users");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "⚠️ No users found in database<br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Password Hash</th><th>Hash Length</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td style='font-family: monospace; font-size: 10px;'>" . htmlspecialchars(substr($user['password'], 0, 50)) . "...</td>";
            echo "<td>" . strlen($user['password']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "❌ Error fetching users: " . $e->getMessage() . "<br>";
}

// Test password verification
echo "<h3>4. Password Verification Test</h3>";
try {
    // Test with admin user
    $stmt = $db->prepare("SELECT username, password FROM users WHERE username = 'admin' OR email = 'admin@example.com' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "Found admin user: " . htmlspecialchars($admin['username']) . "<br>";
        
        // Test common passwords
        $testPasswords = ['admin', '123456', 'password', 'admin123', '0993991019Matlab?'];
        
        foreach ($testPasswords as $testPass) {
            $isValid = password_verify($testPass, $admin['password']);
            $status = $isValid ? "✅" : "❌";
            echo "$status Testing password '$testPass': " . ($isValid ? "MATCH" : "NO MATCH") . "<br>";
        }
    } else {
        echo "⚠️ No admin user found<br>";
    }
} catch (Exception $e) {
    echo "❌ Password test error: " . $e->getMessage() . "<br>";
}

// Test creating a new password hash
echo "<h3>5. Password Hash Generation Test</h3>";
$testPassword = '0993991019Matlab?';
$newHash = password_hash($testPassword, PASSWORD_DEFAULT);
echo "Test password: $testPassword<br>";
echo "Generated hash: $newHash<br>";
echo "Hash length: " . strlen($newHash) . "<br>";
echo "Verification test: " . (password_verify($testPassword, $newHash) ? "✅ SUCCESS" : "❌ FAILED") . "<br>";

echo "<hr>";
echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>