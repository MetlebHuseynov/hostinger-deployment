<?php
/**
 * Admin Login Debug Test
 * Admin giriş problemini həll etmək üçün test
 */

require_once 'config/config.php';
require_once 'config/database.php';

// Clean output
ob_clean();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

echo "<h2>Admin Login Debug Test</h2>";

// 1. Database bağlantısını test et
echo "<h3>1. Database Bağlantısı:</h3>";
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Database bağlantısı uğurlu<br>";
    
    // Users cədvəlini yoxla
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ Users cədvəlində {$result['count']} istifadəçi var<br>";
    
    // Admin user-ləri göstər
    $stmt = $db->query("SELECT id, username, email, role FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll();
    echo "<h4>Admin İstifadəçilər:</h4>";
    foreach ($admins as $admin) {
        echo "- ID: {$admin['id']}, Username: {$admin['username']}, Email: {$admin['email']}<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database xətası: " . $e->getMessage() . "<br>";
}

// 2. API endpoint-ini test et
echo "<h3>2. Auth API Test:</h3>";

// Test login məlumatları
$testEmail = 'admin@prolinege.com';
$testPassword = 'admin123';

echo "Test məlumatları: Email: {$testEmail}, Password: {$testPassword}<br>";

// Manual login test
try {
    $stmt = $db->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$testEmail, $testEmail]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ İstifadəçi tapıldı: {$user['username']} ({$user['email']})<br>";
        echo "Stored password hash: " . substr($user['password'], 0, 20) . "...<br>";
        
        // Parol yoxlaması
        if (password_verify($testPassword, $user['password'])) {
            echo "✅ Parol düzgündür<br>";
        } else {
            echo "❌ Parol yanlışdır<br>";
            
            // Yeni hash yarat
            $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
            echo "Yeni hash: " . substr($newHash, 0, 20) . "...<br>";
            
            // Hash-i yenilə
            $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($updateStmt->execute([$newHash, $user['id']])) {
                echo "✅ Parol hash-i yeniləndi<br>";
            }
        }
    } else {
        echo "❌ İstifadəçi tapılmadı<br>";
        
        // Admin user yarat
        $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
        $createStmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        if ($createStmt->execute(['admin', $testEmail, $hashedPassword, 'admin'])) {
            echo "✅ Yeni admin user yaradıldı<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Login test xətası: " . $e->getMessage() . "<br>";
}

// 3. JWT test
echo "<h3>3. JWT Test:</h3>";
try {
    // JWT class-ını yüklə
    require_once 'config/config.php';
    
    $payload = [
        'id' => 1,
        'username' => 'admin',
        'email' => $testEmail,
        'role' => 'admin',
        'iat' => time(),
        'exp' => time() + (24 * 60 * 60)
    ];
    
    $token = JWT::encode($payload);
    echo "✅ JWT token yaradıldı: " . substr($token, 0, 50) . "...<br>";
    
} catch (Exception $e) {
    echo "❌ JWT xətası: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Tövsiyələr:</h3>";
echo "1. Bu test faylını serverə yükləyin<br>";
echo "2. Browser-də açın: https://prolinege.com/test_admin_login_debug.php<br>";
echo "3. Nəticələri yoxlayın<br>";
echo "4. Admin panelə yenidən giriş cəhd edin<br>";
?>