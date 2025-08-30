<?php
header('Content-Type: text/html; charset=utf-8');

echo "<h2>API Endpoints Test</h2>";

$base_url = 'https://prolinege.com/api';
$endpoints = [
    'products' => '/products',
    'categories' => '/categories', 
    'markas' => '/markas',
    'featured-products' => '/featured-products',
    'featured-categories' => '/featured-categories',
    'featured-brands' => '/featured-brands'
];

function testAPI($url, $name) {
    echo "<h3>Testing {$name} API:</h3>";
    echo "<p>URL: {$url}</p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<p><strong>HTTP Code:</strong> {$http_code}</p>";
    
    if ($error) {
        echo "<p style='color: red;'><strong>cURL Error:</strong> {$error}</p>";
        return;
    }
    
    if ($http_code === 200) {
        echo "<p style='color: green;'><strong>Status:</strong> Success</p>";
        
        $json_data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p style='color: green;'><strong>Response Format:</strong> Valid JSON</p>";
            
            if (isset($json_data['data']) && is_array($json_data['data'])) {
                $count = count($json_data['data']);
                echo "<p><strong>Data Count:</strong> {$count} items</p>";
                
                if ($count > 0) {
                    echo "<p><strong>Sample Data:</strong></p>";
                    echo "<pre>" . json_encode(array_slice($json_data['data'], 0, 2), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                } else {
                    echo "<p style='color: orange;'><strong>Warning:</strong> No data found in response</p>";
                }
            } else {
                echo "<p style='color: orange;'><strong>Warning:</strong> Response structure unexpected</p>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
            }
        } else {
            echo "<p style='color: red;'><strong>JSON Error:</strong> " . json_last_error_msg() . "</p>";
            echo "<p><strong>Raw Response:</strong></p>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "</pre>";
        }
    } else {
        echo "<p style='color: red;'><strong>Status:</strong> Error (HTTP {$http_code})</p>";
        echo "<p><strong>Response:</strong></p>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "</pre>";
    }
    
    echo "<hr>";
}

foreach ($endpoints as $name => $endpoint) {
    $url = $base_url . $endpoint;
    testAPI($url, $name);
}

echo "<h3>Direct Database Check:</h3>";
echo "<p>Also check: <a href='test_database_content.php' target='_blank'>Database Content Test</a></p>";
?>