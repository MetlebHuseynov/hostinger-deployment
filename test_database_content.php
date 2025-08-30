<?php
require_once 'config/config.php';
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Database Content Test</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        echo "<p style='color: red;'>Database connection failed!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>Database connected successfully!</p>";
    
    // Check Products table
    echo "<h3>Products Table:</h3>";
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM products");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total products: " . $result['count'] . "</p>";
    
    if ($result['count'] > 0) {
        $stmt = $db->prepare("SELECT id, name, category_id, marka_id, price FROM products LIMIT 5");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Category ID</th><th>Marka ID</th><th>Price</th></tr>";
        foreach ($products as $product) {
            echo "<tr><td>{$product['id']}</td><td>{$product['name']}</td><td>{$product['category_id']}</td><td>{$product['marka_id']}</td><td>{$product['price']}</td></tr>";
        }
        echo "</table>";
    }
    
    // Check Categories table
    echo "<h3>Categories Table:</h3>";
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM categories");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total categories: " . $result['count'] . "</p>";
    
    if ($result['count'] > 0) {
        $stmt = $db->prepare("SELECT id, name, description FROM categories LIMIT 5");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Description</th></tr>";
        foreach ($categories as $category) {
            echo "<tr><td>{$category['id']}</td><td>{$category['name']}</td><td>{$category['description']}</td></tr>";
        }
        echo "</table>";
    }
    
    // Check Markas table
    echo "<h3>Markas Table:</h3>";
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM markas");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total markas: " . $result['count'] . "</p>";
    
    if ($result['count'] > 0) {
        $stmt = $db->prepare("SELECT id, name, description FROM markas LIMIT 5");
        $stmt->execute();
        $markas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Description</th></tr>";
        foreach ($markas as $marka) {
            echo "<tr><td>{$marka['id']}</td><td>{$marka['name']}</td><td>{$marka['description']}</td></tr>";
        }
        echo "</table>";
    }
    
    // Check table structure
    echo "<h3>Table Structure Check:</h3>";
    $tables = ['products', 'categories', 'markas'];
    foreach ($tables as $table) {
        echo "<h4>{$table} table columns:</h4>";
        $stmt = $db->prepare("DESCRIBE {$table}");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr><td>{$column['Field']}</td><td>{$column['Type']}</td><td>{$column['Null']}</td><td>{$column['Key']}</td><td>{$column['Default']}</td></tr>";
        }
        echo "</table><br>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>