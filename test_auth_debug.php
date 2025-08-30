<?php
// Test auth API and check for errors
require_once 'config/config.php';
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Auth API Debug Test</h2>";

try {
    // Test database connection
    $database = new Database();
    $db = $database->getDBConnection();
    
    if (!$db) {
        echo "❌ Database bağlantısı uğursuz!<br>";
        exit;
    }
    echo "✅ Database bağlantısı uğurlu<br>";
    
    // Test JWT class
    if (class_exists('JWT')) {
        echo "✅ JWT class mövcuddur<br>";
        
        // Test JWT encoding
        $testPayload = ['test' => 'data', 'iat' => time()];
        $token = JWT::encode($testPayload);
        echo "✅ JWT token yaradıldı: " . substr($token, 0, 50) . "...<br>";
    } else {
        echo "❌ JWT class tapılmadı!<br>";
    }
    
    // Test user lookup
    echo "<h3>User Test:</h3>";
    $stmt = $db->prepare("SELECT id, username, email, role FROM users WHERE email = ? OR username = ?");
    $stmt->execute(['admin@prolinege.com', 'admin@prolinege.com']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ Admin user tapıldı:<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "Role: " . $user['role'] . "<br>";
    } else {
        echo "❌ Admin user tapılmadı!<br>";
    }
    
    // Test direct login API call
    echo "<h3>Direct API Test:</h3>";
    echo "<form method='post' action='/api/auth/login'>";
    echo "<input type='hidden' name='email' value='admin@prolinege.com'>";
    echo "<input type='hidden' name='password' value='admin123'>";
    echo "<button type='submit'>Test Login API</button>";
    echo "</form>";
    
    // Test with CURL
    echo "<h3>CURL Test:</h3>";
    $loginData = json_encode([
        'email' => 'admin@prolinege.com',
        'password' => 'admin123'
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://prolinege.com/api/auth/login');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($loginData)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode<br>";
    echo "Response: <pre>" . htmlspecialchars($response) . "</pre>";
    
} catch (Exception $e) {
    echo "❌ Xəta: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p>Test tamamlandı: " . date('Y-m-d H:i:s') . "</p>";
?>