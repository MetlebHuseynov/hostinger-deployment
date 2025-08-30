<?php
// Simple login test without JWT
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Sadə Login Test</h2>";

try {
    $db = getDBConnection();
    echo "✅ Database bağlantısı uğurlu<br>";
    
    // Test credentials
    $email = 'admin@prolinege.com';
    $password = 'admin123';
    
    echo "<h3>Test məlumatları:</h3>";
    echo "Email: $email<br>";
    echo "Password: $password<br><br>";
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ İstifadəçi tapıldı<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "Role: " . $user['role'] . "<br>";
        echo "Password hash: " . substr($user['password'], 0, 20) . "...<br><br>";
        
        // Test password verification
        echo "<h3>Parol yoxlaması:</h3>";
        $passwordMatch = password_verify($password, $user['password']);
        
        if ($passwordMatch) {
            echo "✅ Parol düzgündür!<br>";
            echo "<strong style='color: green;'>Login uğurlu olmalıdır!</strong><br>";
        } else {
            echo "❌ Parol yanlışdır!<br>";
            echo "<strong style='color: red;'>Login uğursuz!</strong><br>";
            
            // Try to create correct hash
            echo "<br><h3>Düzgün hash yaradılır:</h3>";
            $correctHash = password_hash($password, PASSWORD_DEFAULT);
            echo "Yeni hash: $correctHash<br>";
            
            // Update user with correct hash
            $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($updateStmt->execute([$correctHash, $user['id']])) {
                echo "✅ Parol hash-i yeniləndi!<br>";
                echo "<strong style='color: green;'>İndi login işləməlidir!</strong><br>";
            } else {
                echo "❌ Parol hash-i yenilənə bilmədi!<br>";
            }
        }
    } else {
        echo "❌ İstifadəçi tapılmadı!<br>";
        echo "<strong style='color: red;'>Admin user yaradılmalıdır!</strong><br>";
    }
    
} catch (Exception $e) {
    echo "❌ Xəta: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p>Test tamamlandı: " . date('Y-m-d H:i:s') . "</p>";
?>