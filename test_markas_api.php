<?php
// Test markas API endpoint
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Markas API Test</h2>";
echo "<p>Testing /api/markas endpoint...</p>";

// Get the base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . '://' . $host;
$api_url = $base_url . '/api/markas';

echo "<p><strong>API URL:</strong> $api_url</p>";

// Test the API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h3>API Response:</h3>";
echo "<p><strong>HTTP Status Code:</strong> $http_code</p>";

if ($error) {
    echo "<p><strong>cURL Error:</strong> $error</p>";
} else {
    echo "<p><strong>Response:</strong></p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Try to decode JSON
    $data = json_decode($response, true);
    if ($data !== null) {
        echo "<h3>Parsed JSON Data:</h3>";
        if (isset($data['success']) && $data['success']) {
            echo "<p>✓ API call successful</p>";
            if (isset($data['data'])) {
                echo "<p><strong>Number of markas:</strong> " . count($data['data']) . "</p>";
                echo "<h4>Markas List:</h4>";
                echo "<ul>";
                foreach ($data['data'] as $marka) {
                    echo "<li>ID: {$marka['id']} - {$marka['name']} ({$marka['description']})</li>";
                }
                echo "</ul>";
            }
        } else {
            echo "<p>✗ API call failed</p>";
            if (isset($data['message'])) {
                echo "<p><strong>Error:</strong> {$data['message']}</p>";
            }
        }
    } else {
        echo "<p><strong>JSON Parse Error:</strong> Invalid JSON response</p>";
    }
}

echo "<hr>";
echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Add a direct link to test in browser
echo "<p><a href='$api_url' target='_blank'>Click here to test API directly in browser</a></p>";
?>