<?php
require_once 'config/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== PRODUCTS DEBUG ===\n";
    
    // Check for duplicate products
    echo "\n1. Checking for duplicate products:\n";
    $stmt = $pdo->query("SELECT name, COUNT(*) as count FROM products GROUP BY name HAVING COUNT(*) > 1");
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "No duplicate product names found.\n";
    } else {
        echo "Found duplicate products:\n";
        foreach ($duplicates as $dup) {
            echo "- {$dup['name']}: {$dup['count']} times\n";
        }
    }
    
    // Check all products with details
    echo "\n2. All products with details:\n";
    $stmt = $pdo->query("SELECT id, name, price, image, category_id, marka_id, stock FROM products ORDER BY id");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        echo "ID: {$product['id']}, Name: {$product['name']}, Price: {$product['price']}, Image: {$product['image']}, Category: {$product['category_id']}, Marka: {$product['marka_id']}, Stock: {$product['stock']}\n";
    }
    
    // Check categories
    echo "\n3. Categories:\n";
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY id");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($categories)) {
        echo "No categories found!\n";
    } else {
        foreach ($categories as $cat) {
            echo "ID: {$cat['id']}, Name: {$cat['name']}\n";
        }
    }
    
    // Check markas
    echo "\n4. Markas:\n";
    $stmt = $pdo->query("SELECT id, name FROM markas ORDER BY id");
    $markas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($markas)) {
        echo "No markas found!\n";
    } else {
        foreach ($markas as $marka) {
            echo "ID: {$marka['id']}, Name: {$marka['name']}\n";
        }
    }
    
    // Check product images directory
    echo "\n5. Checking product images:\n";
    $imageDir = 'uploads/products/';
    if (is_dir($imageDir)) {
        $images = scandir($imageDir);
        $imageFiles = array_filter($images, function($file) {
            return !in_array($file, ['.', '..']) && is_file('uploads/products/' . $file);
        });
        echo "Found " . count($imageFiles) . " image files:\n";
        foreach ($imageFiles as $img) {
            echo "- $img\n";
        }
    } else {
        echo "Image directory 'uploads/products/' does not exist!\n";
    }
    
    // Check for orphaned products (products with invalid category_id or marka_id)
    echo "\n6. Checking for orphaned products:\n";
    $stmt = $pdo->query("
        SELECT p.id, p.name, p.category_id, p.marka_id, 
               c.name as category_name, m.name as marka_name
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN markas m ON p.marka_id = m.id
        WHERE c.id IS NULL OR m.id IS NULL
    ");
    $orphaned = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($orphaned)) {
        echo "No orphaned products found.\n";
    } else {
        echo "Found orphaned products:\n";
        foreach ($orphaned as $orphan) {
            echo "- Product ID: {$orphan['id']}, Name: {$orphan['name']}, Category ID: {$orphan['category_id']}, Marka ID: {$orphan['marka_id']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>