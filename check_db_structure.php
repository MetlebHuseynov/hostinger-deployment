<?php
try {
    $db = new PDO('sqlite:proline.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>Database Tables:</h3>";
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<pre>" . print_r($tables, true) . "</pre>";
    
    foreach($tables as $table) {
        echo "<h4>Table: $table</h4>";
        $stmt = $db->query("PRAGMA table_info($table)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($columns, true) . "</pre>";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        echo "Row count: $count<br><br>";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>