<?php
try {
    $db = new PDO('sqlite:database/proline.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking markas table in SQLite database...\n\n";
    
    // Check if table exists
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='markas'");
    if ($stmt->fetch()) {
        echo "✓ markas table exists\n\n";
        
        // Get table structure
        $stmt = $db->query("PRAGMA table_info(markas)");
        $columns = $stmt->fetchAll();
        
        echo "Table structure:\n";
        foreach ($columns as $column) {
            echo "- {$column['name']} ({$column['type']})\n";
        }
        echo "\n";
        
        // Get all data
        $stmt = $db->query("SELECT * FROM markas ORDER BY id");
        $markas = $stmt->fetchAll();
        
        echo "Total rows: " . count($markas) . "\n\n";
        
        if (count($markas) > 0) {
            echo "Sample data:\n";
            foreach ($markas as $index => $marka) {
                echo "Row " . ($index + 1) . ":\n";
                foreach ($marka as $key => $value) {
                    if (!is_numeric($key)) {
                        echo "  $key: '" . ($value ?? 'NULL') . "'\n";
                    }
                }
                echo "\n";
                if ($index >= 4) break; // Show only first 5 rows
            }
        }
    } else {
        echo "✗ markas table does not exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>