<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

echo "<h2>API Test</h2>";

// Load environment variables
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
    echo "<p>✓ .env loaded</p>";
} else {
    echo "<p>✗ .env not found</p>";
}

try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? '';
    $username = $_ENV['DB_USER'] ?? '';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✓ Database connected</p>";
    
    // Test products query
    $stmt = $pdo->prepare("
        SELECT p.*, 
               c.name as category_name, 
               m.name as marka_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN markas m ON p.marka_id = m.id 
        LIMIT 5
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Products (" . count($products) . "):</h3>";
    foreach ($products as $product) {
        echo "<p>ID: {$product['id']}, Name: {$product['name']}, Category: {$product['category_name']}, Marka: {$product['marka_name']}</p>";
    }
    
    // Check for duplicates
    $stmt = $pdo->prepare("SELECT name, COUNT(*) as count FROM products GROUP BY name HAVING count > 1");
    $stmt->execute();
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Duplicate Products (" . count($duplicates) . "):</h3>";
    foreach ($duplicates as $dup) {
        echo "<p>Name: {$dup['name']}, Count: {$dup['count']}</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>