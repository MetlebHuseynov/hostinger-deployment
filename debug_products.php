<?php
try {
    $pdo = new PDO('sqlite:database/proline.db');
    $stmt = $pdo->query('SELECT * FROM products ORDER BY id DESC LIMIT 2');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Products in Database:</h2>";
    echo "<pre>" . json_encode($products, JSON_PRETTY_PRINT) . "</pre>";
    
    // Also check the table structure
    $stmt = $pdo->query('PRAGMA table_info(products)');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Products Table Structure:</h2>";
    echo "<pre>" . json_encode($columns, JSON_PRETTY_PRINT) . "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>