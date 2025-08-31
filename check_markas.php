<?php
try {
    $db = new PDO('sqlite:proline.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Markas Table Check</h2>";
    
    // Check if markas table exists
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='markas'");
    $tableExists = $tables->fetch();
    if ($tableExists) {
        echo "<p>✓ Markas table exists</p>";
        
        // Get table structure
        $structure = $db->query("PRAGMA table_info(markas)");
        echo "<h3>Table Structure:</h3>";
        echo "<pre>";
        while ($column = $structure->fetch(PDO::FETCH_ASSOC)) {
            print_r($column);
        }
        echo "</pre>";
        
        // Get row count
        $count = $db->query("SELECT COUNT(*) as count FROM markas")->fetch();
        echo "<p>Total rows: " . $count['count'] . "</p>";
        
        // Get first 5 rows
        $result = $db->query("SELECT * FROM markas LIMIT 5");
        echo "<h3>Sample Data:</h3>";
        echo "<pre>";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "<p>✗ Markas table does not exist</p>";
        
        // List all tables
        $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
        echo "<h3>Available tables:</h3>";
        echo "<ul>";
        while ($table = $tables->fetch()) {
            echo "<li>" . $table['name'] . "</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>