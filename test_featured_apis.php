<?php
echo "<h2>Featured APIs Test</h2>";
echo "<p>Testing featured endpoints...</p>";

// Test database connection
try {
    require_once 'config/config.php';
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Check if featured columns exist
echo "<h3>Checking Featured Columns</h3>";

try {
    // Check products table
    $stmt = $pdo->query("DESCRIBE products");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $hasFeaturedProducts = in_array('featured', $columns);
    echo "<p>Products table has 'featured' column: " . ($hasFeaturedProducts ? "<span style='color: green;'>✓ Yes</span>" : "<span style='color: red;'>✗ No</span>") . "</p>";
    
    // Check categories table
    $stmt = $pdo->query("DESCRIBE categories");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $hasFeaturedCategories = in_array('featured', $columns);
    echo "<p>Categories table has 'featured' column: " . ($hasFeaturedCategories ? "<span style='color: green;'>✓ Yes</span>" : "<span style='color: red;'>✗ No</span>") . "</p>";
    
    // Check markas table
    $stmt = $pdo->query("DESCRIBE markas");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $hasFeaturedMarkas = in_array('featured', $columns);
    echo "<p>Markas table has 'featured' column: " . ($hasFeaturedMarkas ? "<span style='color: green;'>✓ Yes</span>" : "<span style='color: red;'>✗ No</span>") . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error checking columns: " . $e->getMessage() . "</p>";
}

// Test API endpoints
echo "<h3>Testing API Endpoints</h3>";

$endpoints = [
    '/api/featured-products/public' => 'Featured Products',
    '/api/featured-categories/public' => 'Featured Categories', 
    '/api/featured-brands/public' => 'Featured Brands'
];

foreach ($endpoints as $endpoint => $name) {
    echo "<h4>Testing $name</h4>";
    
    $url = 'http://' . $_SERVER['HTTP_HOST'] . $endpoint;
    echo "<p>URL: <a href='$url' target='_blank'>$url</a></p>";
    
    // Use cURL to test the endpoint
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    echo "<p>HTTP Status: <strong>$httpCode</strong></p>";
    
    if ($httpCode == 200) {
        echo "<p style='color: green;'>✓ Success</p>";
        $data = json_decode($body, true);
        if ($data && isset($data['data'])) {
            echo "<p>Items count: " . count($data['data']) . "</p>";
            echo "<details><summary>Response Data</summary><pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre></details>";
        } else {
            echo "<p>Response: <pre>" . htmlspecialchars($body) . "</pre></p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Failed</p>";
        echo "<p>Response: <pre>" . htmlspecialchars($body) . "</pre></p>";
    }
    
    echo "<hr>";
}

echo "<h3>Instructions</h3>";
echo "<p>If featured columns are missing, run <strong>add_featured_columns.sql</strong> in phpMyAdmin first.</p>";
echo "<p>Then refresh this page to test the APIs again.</p>";
?>