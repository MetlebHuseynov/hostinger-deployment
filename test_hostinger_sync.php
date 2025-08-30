<?php
// Hostinger Database Connection Test for Sync

// Load environment variables
function loadEnv() {
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                }
            }
        }
    }
}

loadEnv();

echo "🔍 HOSTINGER DATABASE CONNECTION TEST\n";
echo "=====================================\n\n";

// Hostinger database credentials
$host = 'localhost';
$dbname = 'u482576391_yUuh0';
$username = 'u482576391_Md1PB';
$password = '0993991019Metleb';
$port = 3306;

echo "📋 Connection Details:\n";
echo "Host: $host\n";
echo "Database: $dbname\n";
echo "Username: $username\n";
echo "Port: $port\n\n";

try {
    // Test MySQL connection
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    echo "🔗 Attempting to connect to Hostinger MySQL...\n";
    
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "✅ Successfully connected to Hostinger database!\n\n";
    
    // Test database structure
    echo "📊 CHECKING DATABASE STRUCTURE:\n";
    echo "==============================\n";
    
    $tables = ['categories', 'markas', 'products', 'users'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "✅ Table '$table': {$result['count']} records\n";
        } catch (PDOException $e) {
            echo "❌ Table '$table': Not found or error - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📋 SAMPLE DATA FROM TABLES:\n";
    echo "===========================\n";
    
    // Check categories
    try {
        $stmt = $pdo->query("SELECT * FROM categories LIMIT 3");
        $categories = $stmt->fetchAll();
        echo "\n🏷️ Categories (first 3):\n";
        foreach ($categories as $cat) {
            echo "  - ID: {$cat['id']}, Name: {$cat['name']}\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error reading categories: " . $e->getMessage() . "\n";
    }
    
    // Check markas
    try {
        $stmt = $pdo->query("SELECT * FROM markas LIMIT 3");
        $markas = $stmt->fetchAll();
        echo "\n🏢 Markas (first 3):\n";
        foreach ($markas as $marka) {
            echo "  - ID: {$marka['id']}, Name: {$marka['name']}\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error reading markas: " . $e->getMessage() . "\n";
    }
    
    // Check products
    try {
        $stmt = $pdo->query("SELECT p.*, c.name as category_name, m.name as marka_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN markas m ON p.marka_id = m.id LIMIT 3");
        $products = $stmt->fetchAll();
        echo "\n📦 Products (first 3):\n";
        foreach ($products as $product) {
            echo "  - ID: {$product['id']}, Name: {$product['name']}, Price: {$product['price']}, Category: {$product['category_name']}, Marka: {$product['marka_name']}\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error reading products: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎯 CONNECTION TEST COMPLETED SUCCESSFULLY!\n";
    echo "Ready for database synchronization.\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    echo "\n🔧 Troubleshooting tips:\n";
    echo "1. Check if database credentials are correct\n";
    echo "2. Verify database server is running\n";
    echo "3. Check network connectivity\n";
    echo "4. Ensure database exists and user has permissions\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
?>