<?php
/**
 * Main Configuration File for PHP Application
 * Hostinger deployment üçün əsas konfiqurasiya
 */

// Error reporting - production üçün kapatılmalıdır
if ($_ENV['NODE_ENV'] ?? 'production' === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Timezone
date_default_timezone_set('Asia/Baku');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', ($_ENV['NODE_ENV'] ?? 'production') === 'production' ? 1 : 0);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORS headers
function setCorsHeaders() {
    $origin = $_ENV['CORS_ORIGIN'] ?? 'http://localhost:3000';
    
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Check if origin is allowed
        $allowedOrigins = [
            'http://localhost:3000',
            'https://prolinege.com',
            'https://www.prolinege.com'
        ];
        
        if (in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        }
    }
    
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
    
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        
        exit(0);
    }
}

// JSON response helper
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Error response helper
function errorResponse($message, $statusCode = 400) {
    jsonResponse(['error' => $message], $statusCode);
}

// Success response helper
function successResponse($data = [], $message = 'Uğurlu') {
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

// JWT Helper functions
class JWT {
    private static $secret;
    
    public static function init() {
        self::$secret = $_ENV['JWT_SECRET'] ?? 'proline_secret_key_2024';
    }
    
    public static function encode($payload) {
        self::init();
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::$secret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    public static function decode($jwt) {
        self::init();
        $tokenParts = explode('.', $jwt);
        
        if (count($tokenParts) !== 3) {
            return false;
        }
        
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $signatureProvided = $tokenParts[2];
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::$secret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if ($base64Signature === $signatureProvided) {
            return json_decode($payload, true);
        }
        
        return false;
    }
}

// Authentication middleware
// Simple JWT implementation
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function createJWT($payload, $secret = 'your-secret-key') {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode($payload);
    
    $base64Header = base64UrlEncode($header);
    $base64Payload = base64UrlEncode($payload);
    
    $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
    $base64Signature = base64UrlEncode($signature);
    
    return $base64Header . "." . $base64Payload . "." . $base64Signature;
}

function verifyJWT($jwt, $secret = 'your-secret-key') {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        return false;
    }
    
    list($base64Header, $base64Payload, $base64Signature) = $parts;
    
    $signature = base64UrlDecode($base64Signature);
    $expectedSignature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
    
    if (!hash_equals($signature, $expectedSignature)) {
        return false;
    }
    
    $payload = json_decode(base64UrlDecode($base64Payload), true);
    return $payload;
}

function authenticateToken() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
    if (!$authHeader) {
        errorResponse('Token təqdim edilməyib', 401);
    }
    
    $token = str_replace('Bearer ', '', $authHeader);
    $decoded = verifyJWT($token);
    
    if (!$decoded) {
        errorResponse('Etibarsız token', 403);
    }
    
    // Check token expiration
    if (isset($decoded['exp']) && $decoded['exp'] < time()) {
        errorResponse('Token vaxtı bitib', 403);
    }
    
    return $decoded;
}

// File upload helper
function handleFileUpload($fileKey, $uploadDir = 'uploads') {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $file = $_FILES[$fileKey];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        errorResponse('Yalnız şəkil faylları qəbul edilir');
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB
        errorResponse('Fayl ölçüsü 5MB-dan böyük ola bilməz');
    }
    
    $uploadPath = __DIR__ . '/../public/' . $uploadDir;
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadPath . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $uploadDir . '/' . $filename;
    }
    
    return null;
}

// Generate JWT token helper
function generateJWT($payload) {
    $payload['exp'] = time() + (24 * 60 * 60); // 24 hours
    return createJWT($payload);
}

// Set CORS headers for all requests
setCorsHeaders();
?>