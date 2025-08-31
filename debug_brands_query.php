<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Test the exact query from getBrands function
    $sql = "SELECT m.*, 
                   COUNT(p.id) as product_count
            FROM markas m 
            LEFT JOIN products p ON m.id = p.marka_id 
            GROUP BY m.id 
            ORDER BY m.created_at DESC";
    
    echo "Executing query: $sql\n\n";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $brands = $stmt->fetchAll();
    
    echo "Number of brands found: " . count($brands) . "\n\n";
    
    foreach ($brands as $brand) {
        echo "Brand ID: " . $brand['id'] . "\n";
        echo "Name: '" . $brand['name'] . "'\n";
        echo "Description: '" . $brand['description'] . "'\n";
        echo "Image: '" . $brand['image'] . "'\n";
        echo "Product Count: " . $brand['product_count'] . "\n";
        echo "Created At: " . $brand['created_at'] . "\n";
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>