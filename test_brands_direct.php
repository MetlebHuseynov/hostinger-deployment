<?php
// Direct test of brands API
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h2>Direct Brands API Test</h2>";

// Initialize database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "<p>Database connection failed</p>";
    exit;
}

echo "<p>Database connected successfully</p>";

// Test markas table query directly
try {
    $query = "SELECT m.*, 
                     COUNT(p.id) as product_count
              FROM markas m 
              LEFT JOIN products p ON m.id = p.marka_id 
              GROUP BY m.id 
              ORDER BY m.name ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Query Result:</h3>";
    echo "<p>Found " . count($brands) . " brands</p>";
    echo "<pre>";
    print_r($brands);
    echo "</pre>";
    
    // Test API response format
    $response = [
        'success' => true,
        'message' => 'Uğurlu',
        'data' => $brands
    ];
    
    echo "<h3>API Response Format:</h3>";
    echo "<pre>";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>