<?php
// Test products API and database
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Products API Test</h2>";

try {
    $db = getDBConnection();
    echo "✅ Database bağlantısı uğurlu<br>";
    
    // Check if products table exists
    echo "<h3>Products cədvəli yoxlanır:</h3>";
    $stmt = $db->prepare("SHOW TABLES LIKE 'products'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "✅ Products cədvəli mövcuddur<br>";
        
        // Check table structure
        echo "<h3>Cədvəl strukturu:</h3>";
        $stmt = $db->prepare("DESCRIBE products");
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // Count products
        echo "<h3>Məhsul sayı:</h3>";
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM products");
        $stmt->execute();
        $count = $stmt->fetch()['count'];
        echo "Cəmi məhsul: $count<br>";
        
        if ($count > 0) {
            echo "<h3>İlk 5 məhsul:</h3>";
            $stmt = $db->prepare("SELECT id, name, price, category, marka FROM products LIMIT 5");
            $stmt->execute();
            $products = $stmt->fetchAll();
            
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Ad</th><th>Qiymət</th><th>Kateqoriya</th><th>Marka</th></tr>";
            foreach ($products as $product) {
                echo "<tr>";
                echo "<td>" . $product['id'] . "</td>";
                echo "<td>" . $product['name'] . "</td>";
                echo "<td>" . $product['price'] . "</td>";
                echo "<td>" . $product['category'] . "</td>";
                echo "<td>" . $product['marka'] . "</td>";
                echo "</tr>";
            }
            echo "</table><br>";
        } else {
            echo "❌ Heç bir məhsul tapılmadı!<br>";
        }
        
    } else {
        echo "❌ Products cədvəli mövcud deyil!<br>";
        echo "<strong style='color: red;'>Products cədvəli yaradılmalıdır!</strong><br>";
    }
    
} catch (Exception $e) {
    echo "❌ Xəta: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p>Test tamamlandı: " . date('Y-m-d H:i:s') . "</p>";
?>