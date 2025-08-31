<?php
/**
 * Main API Router for PHP Application
 * Bütün API sorğularını idarə edir
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Set CORS headers
setCorsHeaders();

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => 'Verilənlər bazası bağlantı xətası',
        'message' => 'Database connection failed'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Create tables if they don't exist
$database->createTables();

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api', '', $path); // Remove /api prefix
$pathParts = explode('/', trim($path, '/'));

// Route the request
switch ($pathParts[0]) {
    case 'test':
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'API is working!',
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint' => $pathParts[0],
            'method' => $_SERVER['REQUEST_METHOD']
        ]);
        break;
        
    case 'auth':
        require_once __DIR__ . '/auth.php';
        break;
    case 'products':
        require_once __DIR__ . '/products.php';
        break;
    case 'categories':
        require_once __DIR__ . '/categories.php';
        break;
    case 'markas':
        require_once __DIR__ . '/markas.php';
        break;
    case 'brands':
        require_once __DIR__ . '/markas.php';
        break;
    case 'users':
        require_once __DIR__ . '/users.php';
        break;
    case 'dashboard':
        require_once __DIR__ . '/dashboard.php';
        break;
    case 'featured-products':
        require_once __DIR__ . '/featured-products.php';
        break;
    case 'featured-categories':
        require_once __DIR__ . '/featured-categories.php';
        break;
    case 'featured-brands':
        require_once __DIR__ . '/featured-brands.php';
        break;
    case 'upload':
        require_once __DIR__ . '/upload.php';
        break;
    default:
        errorResponse('API endpoint tapılmadı', 404);
}



?>