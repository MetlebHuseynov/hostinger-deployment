<?php
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Dublikat Məhsulları Təmizləmə</h2>";

// Load environment variables
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
    echo "<p>✓ .env yükləndi</p>";
}

try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? '';
    $username = $_ENV['DB_USER'] ?? '';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✓ Database bağlantısı uğurlu</p>";
    
    // Find duplicates
    $stmt = $pdo->prepare("
        SELECT name, COUNT(*) as count, GROUP_CONCAT(id ORDER BY id) as ids
        FROM products 
        GROUP BY name 
        HAVING count > 1
    ");
    $stmt->execute();
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Tapılan Dublikatlar:</h3>";
    $totalDeleted = 0;
    
    foreach ($duplicates as $duplicate) {
        $ids = explode(',', $duplicate['ids']);
        $keepId = array_shift($ids); // Keep the first one
        $deleteIds = $ids; // Delete the rest
        
        echo "<p><strong>{$duplicate['name']}</strong> - {$duplicate['count']} dəfə</p>";
        echo "<p>Saxlanılan ID: $keepId</p>";
        echo "<p>Silinəcək ID-lər: " . implode(', ', $deleteIds) . "</p>";
        
        // Delete duplicates
        if (!empty($deleteIds)) {
            $placeholders = str_repeat('?,', count($deleteIds) - 1) . '?';
            $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders)");
            $deleteStmt->execute($deleteIds);
            
            $deletedCount = $deleteStmt->rowCount();
            $totalDeleted += $deletedCount;
            echo "<p style='color: green;'>✓ $deletedCount məhsul silindi</p>";
        }
        
        echo "<hr>";
    }
    
    echo "<h3>Nəticə:</h3>";
    echo "<p style='color: green; font-weight: bold;'>Cəmi $totalDeleted dublikat məhsul silindi!</p>";
    
    // Show final count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<p>Qalan məhsul sayı: $total</p>";
    
    // Check if any duplicates remain
    $stmt = $pdo->prepare("SELECT name, COUNT(*) as count FROM products GROUP BY name HAVING count > 1");
    $stmt->execute();
    $remainingDuplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($remainingDuplicates)) {
        echo "<p style='color: green; font-weight: bold;'>✓ Bütün dublikatlar təmizləndi!</p>";
    } else {
        echo "<p style='color: orange;'>Qalan dublikatlar:</p>";
        foreach ($remainingDuplicates as $dup) {
            echo "<p>- {$dup['name']}: {$dup['count']} dəfə</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Xəta: " . $e->getMessage() . "</p>";
}

echo "<p><a href='test_api_simple.php'>Test API-ni yenidən yoxla</a></p>";
?>