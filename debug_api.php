<?php
/**
 * API Debug Tool - Server-də problemləri araşdırmaq üçün
 */

// Error reporting aktiv et
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>API Debug Tool</h2>";
echo "<hr>";

// 1. PHP versiyasını yoxla
echo "<h3>1. PHP Version</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "<br>";

// 2. .env faylını yoxla
echo "<h3>2. Environment File Check</h3>";
if (file_exists(__DIR__ . '/.env')) {
    echo "✓ .env file exists<br>";
    $envContent = file_get_contents(__DIR__ . '/.env');
    echo "<pre>" . htmlspecialchars($envContent) . "</pre>";
} else {
    echo "✗ .env file not found<br>";
}
echo "<br>";

// 3. Config fayllarını yoxla
echo "<h3>3. Config Files Check</h3>";
if (file_exists(__DIR__ . '/config/database.php')) {
    echo "✓ database.php exists<br>";
} else {
    echo "✗ database.php not found<br>";
}

if (file_exists(__DIR__ . '/config/config.php')) {
    echo "✓ config.php exists<br>";
} else {
    echo "✗ config.php not found<br>";
}
echo "<br>";

// 4. Database bağlantısını test et
echo "<h3>4. Database Connection Test</h3>";
try {
    require_once __DIR__ . '/config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✓ Database connection successful<br>";
        
        // Database adını al
        $stmt = $db->query("SELECT DATABASE() as db_name");
        $result = $stmt->fetch();
        echo "Database name: " . $result['db_name'] . "<br>";
        
        // Cədvəlləri yoxla
        echo "<h4>Tables Check:</h4>";
        $tables = ['products', 'categories', 'markas', 'users'];
        foreach ($tables as $table) {
            try {
                $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
                $result = $stmt->fetch();
                echo "✓ Table '$table': " . $result['count'] . " records<br>";
                
                // Featured sütununu yoxla
                if (in_array($table, ['products', 'categories', 'markas'])) {
                    try {
                        $stmt = $db->query("SHOW COLUMNS FROM $table LIKE 'featured'");
                        $column = $stmt->fetch();
                        if ($column) {
                            echo "&nbsp;&nbsp;✓ 'featured' column exists<br>";
                            
                            // Featured məlumatları say
                            $stmt = $db->query("SELECT COUNT(*) as count FROM $table WHERE featured = 1");
                            $result = $stmt->fetch();
                            echo "&nbsp;&nbsp;Featured items: " . $result['count'] . "<br>";
                        } else {
                            echo "&nbsp;&nbsp;✗ 'featured' column missing<br>";
                        }
                    } catch (Exception $e) {
                        echo "&nbsp;&nbsp;✗ Error checking 'featured' column: " . $e->getMessage() . "<br>";
                    }
                }
            } catch (Exception $e) {
                echo "✗ Table '$table': " . $e->getMessage() . "<br>";
            }
        }
    } else {
        echo "✗ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 5. API endpoint-lərini test et
echo "<h3>5. API Endpoints Test</h3>";
$endpoints = [
    '/api/test',
    '/api/products',
    '/api/categories',
    '/api/markas',
    '/api/featured-products/public',
    '/api/featured-categories/public',
    '/api/featured-brands/public'
];

foreach ($endpoints as $endpoint) {
    $url = 'http://' . $_SERVER['HTTP_HOST'] . $endpoint;
    echo "Testing: $endpoint<br>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "&nbsp;&nbsp;✗ cURL Error: $error<br>";
    } else {
        echo "&nbsp;&nbsp;HTTP Code: $httpCode<br>";
        if ($httpCode == 200) {
            echo "&nbsp;&nbsp;✓ Success<br>";
        } else {
            echo "&nbsp;&nbsp;✗ Failed<br>";
            // Response body-ni göstər
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($response, $headerSize);
            echo "&nbsp;&nbsp;Response: " . htmlspecialchars(substr($body, 0, 200)) . "...<br>";
        }
    }
    echo "<br>";
}

// 6. Server məlumatları
echo "<h3>6. Server Information</h3>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "HTTP Host: " . $_SERVER['HTTP_HOST'] . "<br>";

echo "<br><hr>";
echo "<p><strong>Debug completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>