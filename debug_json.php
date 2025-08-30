<?php
// Debug JSON input test
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];
$rawInput = file_get_contents('php://input');
$parsedInput = json_decode($rawInput, true);

echo json_encode([
    'method' => $method,
    'raw_input' => $rawInput,
    'parsed_input' => $parsedInput,
    'post_data' => $_POST,
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not_set',
    'headers' => getallheaders()
]);
?>