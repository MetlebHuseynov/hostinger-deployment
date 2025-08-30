<?php
// Simple database connection test
echo "<h2>Database Connection Test</h2>";

// Load environment variables
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
    echo "<p>✓ .env file loaded</p>";
} else {
    echo "<p>✗ .env file not found</p>";
}

// Show environment variables
echo "<h3>Database Configuration:</h3>";
echo "<p>DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "</p>";
echo "<p>DB_NAME: " . ($_ENV['DB_NAME'] ?? 'NOT SET') . "</p>";
echo "<p>DB_USER: " . ($_ENV['DB_USER'] ?? 'NOT SET') . "</p>";
echo "<p>DB_PASSWORD: " . (isset($_ENV['DB_PASSWORD']) ? 'SET' : 'NOT SET') . "</p>";

// Try to connect
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? '';
    $username = $_ENV['DB_USER'] ?? '';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    echo "<p>Attempting connection with:</p>";
    echo "<p>Host: $host</p>";
    echo "<p>Database: $dbname</p>";
    echo "<p>Username: $username</p>";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Test query
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Available Tables:</h3>";
    if (empty($tables)) {
        echo "<p>No tables found</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}
?>