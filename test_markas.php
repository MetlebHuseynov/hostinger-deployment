<?php
// Test markas table and API
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';

echo "<h2>Markas Table Test</h2>";

try {
    $db = getDBConnection();
    echo "<p>✓ Database connection successful</p>";
    
    // Check if markas table exists
    $stmt = $db->query("SHOW TABLES LIKE 'markas'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✓ Markas table exists</p>";
        
        // Get table structure
        $stmt = $db->query("DESCRIBE markas");
        $columns = $stmt->fetchAll();
        echo "<h3>Table structure:</h3>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li><strong>" . $column['Field'] . ":</strong> " . $column['Type'] . "</li>";
        }
        echo "</ul>";
        
        // Count markas
        $stmt = $db->query("SELECT COUNT(*) as count FROM markas");
        $count = $stmt->fetch();
        echo "<p><strong>Markas count:</strong> " . $count['count'] . "</p>";
        
        // Get all markas
        if ($count['count'] > 0) {
            $stmt = $db->query("SELECT * FROM markas ORDER BY name ASC");
            $markas = $stmt->fetchAll();
            echo "<h3>Markas in database:</h3>";
            foreach ($markas as $marka) {
                echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
                echo "<strong>ID:</strong> " . $marka['id'] . "<br>";
                echo "<strong>Name:</strong> " . $marka['name'] . "<br>";
                if (isset($marka['description'])) {
                    echo "<strong>Description:</strong> " . $marka['description'] . "<br>";
                }
                if (isset($marka['logo'])) {
                    echo "<strong>Logo:</strong> " . $marka['logo'] . "<br>";
                }
                echo "</div>";
            }
        } else {
            echo "<p>No markas found in database</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Markas table does not exist</p>";
        
        // Try to create markas table
        echo "<p>Attempting to create markas table...</p>";
        $sql = "CREATE TABLE IF NOT EXISTS markas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            logo VARCHAR(255),
            website VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $db->exec($sql);
        echo "<p>✓ Markas table created successfully</p>";
        
        // Insert some sample data
        $sampleMarkas = [
            ['ABB', 'ABB elektrik avadanlıqları', 'images/abb-logo.svg', 'https://www.abb.com'],
            ['Siemens', 'Siemens sənaye həlləri', 'images/siemens-logo.svg', 'https://www.siemens.com'],
            ['Schneider Electric', 'Schneider Electric enerji idarəetməsi', 'images/schneider-logo.svg', 'https://www.schneider-electric.com']
        ];
        
        $stmt = $db->prepare("INSERT INTO markas (name, description, logo, website) VALUES (?, ?, ?, ?)");
        foreach ($sampleMarkas as $marka) {
            $stmt->execute($marka);
        }
        echo "<p>✓ Sample markas inserted</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>API Test</h3>";
echo "<p>Test the API at: <a href='/api/markas' target='_blank'>/api/markas</a></p>";
echo "<p>Test completed at: " . date('Y-m-d H:i:s') . "</p>";
?>