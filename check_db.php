<?php
// Hostinger MySQL connection using mysqli
$host = 'srv1622.hstgr.io';
$dbname = 'u482576391_proline';
$username = 'u482576391_proline';
$password = 'Proline2024!';

try {
    $mysqli = new mysqli($host, $username, $password, $dbname);
    
    if ($mysqli->connect_error) {
        throw new Exception('Connection failed: ' . $mysqli->connect_error);
    }
    
    echo "Successfully connected to Hostinger MySQL database!\n";
    
    // Get all tables
    $result = $mysqli->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    echo "\nTables in database:\n";
    foreach($tables as $table) {
        echo "- $table\n";
    }
    
    // Check products count
    if (in_array('products', $tables)) {
        $result = $mysqli->query("SELECT COUNT(*) as count FROM products");
        $row = $result->fetch_assoc();
        echo "\nProducts count: " . $row['count'] . "\n";
    }
    
    // Check categories count
    if (in_array('categories', $tables)) {
        $result = $mysqli->query("SELECT COUNT(*) as count FROM categories");
        $row = $result->fetch_assoc();
        echo "Categories count: " . $row['count'] . "\n";
    }
    
    // Check markas count
    if (in_array('markas', $tables)) {
        $result = $mysqli->query("SELECT COUNT(*) as count FROM markas");
        $row = $result->fetch_assoc();
        echo "Markas count: " . $row['count'] . "\n";
    }
    
    $mysqli->close();
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>