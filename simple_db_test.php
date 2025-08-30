<?php
// Very simple database connection test
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Simple Database Test</h2>";
echo "<p>Testing database connection...</p>";

try {
    // Check if config file exists
    if (!file_exists('config/database.php')) {
        throw new Exception('Database config file not found');
    }
    
    echo "<p>✓ Config file exists</p>";
    
    // Include config
    require_once 'config/database.php';
    echo "<p>✓ Config file loaded</p>";
    
    // Test database connection
    $db = getDBConnection();
    echo "<p>✓ Database connection successful</p>";
    
    // Test simple query
    $stmt = $db->query('SELECT 1 as test');
    $result = $stmt->fetch();
    echo "<p>✓ Simple query works: " . $result['test'] . "</p>";
    
    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✓ Users table exists</p>";
        
        // Count users
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch();
        echo "<p>✓ Users count: " . $count['count'] . "</p>";
    } else {
        echo "<p>✗ Users table does not exist</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p style='color: red;'>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}

echo "<p>Test completed at: " . date('Y-m-d H:i:s') . "</p>";
?>