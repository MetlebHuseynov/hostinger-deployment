<?php
/**
 * Authentication API Endpoints
 * Login və user authentication
 */

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Ensure clean JSON output
ob_clean();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

// Get variables from parent scope if not defined
if (!isset($pathParts)) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = str_replace('/api', '', $path);
    $pathParts = explode('/', trim($path, '/'));
}
if (!isset($method)) {
    $method = $_SERVER['REQUEST_METHOD'];
}

// Handle different auth endpoints
if (count($pathParts) > 1) {
    $authAction = $pathParts[1];
} else {
    $authAction = '';
}

switch ($method) {
    case 'POST':
        if ($authAction === 'login') {
            handleLogin($db);
        } elseif ($authAction === 'refresh') {
            handleRefreshToken($db);
        } else {
            errorResponse('Auth endpoint tapılmadı', 404);
        }
        break;
    case 'GET':
        if ($authAction === 'me') {
            handleGetMe($db);
        } else {
            errorResponse('Auth endpoint tapılmadı', 404);
        }
        break;
    default:
        errorResponse('Method dəstəklənmir', 405);
}

function handleLogin($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $emailOrUsername = $input['email'] ?? $input['username'] ?? null;
    $password = $input['password'] ?? null;
    
    if (!$emailOrUsername || !$password) {
        errorResponse('Email/İstifadəçi adı və parol tələb olunur');
    }
    
    try {
        $stmt = $db->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$emailOrUsername, $emailOrUsername]);
        $user = $stmt->fetch();
        
        if (!$user) {
            errorResponse('İstifadəçi tapılmadı', 401);
        }
        
        if (!password_verify($password, $user['password'])) {
            errorResponse('Yanlış parol', 401);
        }
        
        $payload = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60)
        ];
        
        $token = createJWT($payload);
        
        successResponse([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ], 'Giriş uğurlu');
        
    } catch (Exception $e) {
        errorResponse('Server xətası', 500);
    }
}

function handleGetMe($db) {
    $user = authenticateToken();
    
    try {
        $stmt = $db->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userData = $stmt->fetch();
        
        if (!$userData) {
            errorResponse('İstifadəçi tapılmadı', 404);
        }
        
        successResponse($userData);
        
    } catch (Exception $e) {
        errorResponse('Server xətası', 500);
    }
}

function handleRefreshToken($db) {
    $user = authenticateToken();
    
    try {
        // Create new token with extended expiration
        $payload = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];
        
        $newToken = createJWT($payload);
        
        successResponse([
            'token' => $newToken
        ], 'Token yeniləndi');
        
    } catch (Exception $e) {
        errorResponse('Token yenilənmədi', 500);
    }
}
?>