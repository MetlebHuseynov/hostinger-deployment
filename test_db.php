<?php
try {
    $db = new PDO('sqlite:proline.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>Products Table:</h3>";
    $stmt = $db->query("SELECT * FROM products LIMIT 10");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($products, true) . "</pre>";
    
    echo "<h3>Categories Table:</h3>";
    $stmt = $db->query("SELECT * FROM categories LIMIT 10");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($categories, true) . "</pre>";
    
    echo "<h3>Markas Table:</h3>";
    $stmt = $db->query("SELECT * FROM markas LIMIT 10");
    $markas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($markas, true) . "</pre>";
    
    echo "<h3>Table Counts:</h3>";
    $stmt = $db->query("SELECT COUNT(*) as count FROM products");
    $productCount = $stmt->fetch()['count'];
    echo "Products: $productCount<br>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM categories");
    $categoryCount = $stmt->fetch()['count'];
    echo "Categories: $categoryCount<br>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM markas");
    $markaCount = $stmt->fetch()['count'];
    echo "Markas: $markaCount<br>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>