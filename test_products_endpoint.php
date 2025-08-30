<?php
/**
 * Simple Products Test Endpoint
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Set CORS headers
setCorsHeaders();

header('Content-Type: application/json; charset=utf-8');

try {
    // Initialize database
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }
    
    // Simple products query
    $stmt = $db->prepare("
        SELECT p.*, 
               c.name as category_name, 
               m.name as marka_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN markas m ON p.marka_id = m.id 
        LIMIT 10
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Products retrieved successfully',
        'count' => count($products),
        'data' => $products
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>