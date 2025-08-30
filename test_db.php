<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "Database bağlantısı uğurludur!\n";
        
        // Test query
        $stmt = $db->query("SELECT COUNT(*) as count FROM products");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Məhsul sayı: " . $result['count'] . "\n";
        
    } else {
        echo "Database bağlantısı uğursuz!\n";
    }
} catch (Exception $e) {
    echo "Xəta: " . $e->getMessage() . "\n";
}
?>