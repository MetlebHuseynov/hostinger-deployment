<?php
try {
    // Connect to SQLite database
    $pdo = new PDO('sqlite:database/proline.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to SQLite database successfully!\n";
    
    // Clear existing data
    echo "Clearing existing data...\n";
    $pdo->exec("DELETE FROM products");
    $pdo->exec("DELETE FROM categories");
    $pdo->exec("DELETE FROM markas");
    
    // Read and execute SQL file
    $sql = file_get_contents('hostinger_data.sql');
    
    // Split SQL into individual statements
    $statements = explode(';', $sql);
    
    foreach($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !str_starts_with($statement, '--')) {
            try {
                $pdo->exec($statement);
                echo "Executed: " . substr($statement, 0, 50) . "...\n";
            } catch(Exception $e) {
                echo "Error executing statement: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Verify data import
    echo "\n=== Verification ===\n";
    
    $result = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $count = $result->fetch();
    echo "Categories imported: " . $count['count'] . "\n";
    
    $result = $pdo->query("SELECT COUNT(*) as count FROM markas");
    $count = $result->fetch();
    echo "Markas imported: " . $count['count'] . "\n";
    
    $result = $pdo->query("SELECT COUNT(*) as count FROM products");
    $count = $result->fetch();
    echo "Products imported: " . $count['count'] . "\n";
    
    echo "\nData import completed successfully!\n";
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>