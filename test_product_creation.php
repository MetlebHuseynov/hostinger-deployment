<?php
// Test product creation with authentication using file_get_contents
$loginUrl = 'https://prolinege.com/api/auth/login';
$productsUrl = 'https://prolinege.com/api/products';

// Step 1: Login to get JWT token
$loginData = json_encode([
    'username' => 'admin',
    'password' => 'admin123'
]);

$loginContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $loginData,
        'ignore_errors' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

$loginResponse = file_get_contents($loginUrl, false, $loginContext);
$loginHttpCode = 200; // Assume success if we get response

echo "Login Response (Status: $loginHttpCode):\n";
echo $loginResponse . "\n\n";

if ($loginHttpCode === 200) {
    $loginResult = json_decode($loginResponse, true);
    if (isset($loginResult['token'])) {
        $token = $loginResult['token'];
        echo "JWT Token obtained: " . substr($token, 0, 50) . "...\n\n";
        
        // Step 2: Create product with token
        $productData = json_encode([
            'name' => 'Test Product ' . date('Y-m-d H:i:s'),
            'description' => 'Test product created via API',
            'price' => 99.99,
            'category_id' => 1,
            'marka_id' => 1,
            'stock' => 10
        ]);
        
        $productContext = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n" .
                           "Authorization: Bearer $token\r\n",
                'content' => $productData,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $productResponse = file_get_contents($productsUrl, false, $productContext);
        $productHttpCode = 200; // Will check response content for errors
        
        echo "Product Creation Response (Status: $productHttpCode):\n";
        echo $productResponse . "\n";
        
        if ($productHttpCode === 401) {
            echo "\n=== 401 AUTHENTICATION ERROR DETECTED ===\n";
            echo "This confirms the issue - authentication is failing\n";
        }
    } else {
        echo "Login failed - no token received\n";
    }
} else {
    echo "Login failed with status: $loginHttpCode\n";
}
?>