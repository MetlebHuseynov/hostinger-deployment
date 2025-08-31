<?php
/**
 * Brands API Endpoints - CRUD Sistemi
 * Markalar üçün tam CRUD əməliyyatları
 */

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Set content type
header('Content-Type: application/json; charset=utf-8');

// Helper functions
function sendResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function getJsonInput() {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $input = $_POST;
    }
    
    return $input ?: [];
}

// Initialize database if not already set
if (!isset($db)) {
    $database = new Database();
    $db = $database->getConnection();
}

// Get request method and path if not already set
if (!isset($method)) {
    $method = $_SERVER['REQUEST_METHOD'];
}
if (!isset($pathParts)) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = str_replace('/api', '', $path);
    $pathParts = explode('/', trim($path, '/'));
}

// Get brand ID if provided
$brandId = isset($pathParts[1]) && is_numeric($pathParts[1]) ? (int)$pathParts[1] : null;

// For PUT and DELETE, also check request body for ID if not in URL
if (!$brandId && ($method === 'PUT' || $method === 'DELETE')) {
    $input = getJsonInput();
    if (isset($input['id']) && is_numeric($input['id'])) {
        $brandId = (int)$input['id'];
    }
}

// Route requests
switch ($method) {
    case 'GET':
        if ($brandId) {
            getBrand($db, $brandId);
        } else {
            getBrands($db);
        }
        break;
    case 'POST':
        createBrand($db);
        break;
    case 'PUT':
        if ($brandId) {
            updateBrand($db, $brandId);
        } else {
            sendResponse(false, 'Brand ID tələb olunur', null, 400);
        }
        break;
    case 'DELETE':
        if ($brandId) {
            deleteBrand($db, $brandId);
        } else {
            sendResponse(false, 'Brand ID tələb olunur', null, 400);
        }
        break;
    default:
        sendResponse(false, 'Dəstəklənməyən metod', null, 405);
        break;
}

/**
 * Get all brands
 */
function getBrands($db) {
    try {
        $query = "SELECT m.*, 
                         COUNT(p.id) as product_count
                  FROM markas m 
                  LEFT JOIN products p ON m.id = p.marka_id 
                  GROUP BY m.id 
                  ORDER BY m.name ASC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendResponse(true, 'Uğurlu', $brands);
    } catch (Exception $e) {
        sendResponse(false, 'Xəta baş verdi: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Get single brand
 */
function getBrand($db, $id) {
    try {
        $query = "SELECT m.*, 
                         COUNT(p.id) as product_count
                  FROM markas m 
                  LEFT JOIN products p ON m.id = p.marka_id 
                  WHERE m.id = ? 
                  GROUP BY m.id";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        $brand = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($brand) {
            sendResponse(true, 'Uğurlu', $brand);
        } else {
            sendResponse(false, 'Marka tapılmadı', null, 404);
        }
    } catch (Exception $e) {
        sendResponse(false, 'Xəta baş verdi: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Create new brand
 */
function createBrand($db) {
    try {
        $input = getJsonInput();
        
        // Validate required fields
        if (empty($input['name'])) {
            sendResponse(false, 'Marka adı tələb olunur', null, 400);
            return;
        }
        
        $query = "INSERT INTO markas (name, description, image) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $input['name'],
            $input['description'] ?? '',
            $input['image'] ?? ''
        ]);
        
        $newId = $db->lastInsertId();
        
        // Get the created brand
        getBrand($db, $newId);
        
    } catch (Exception $e) {
        sendResponse(false, 'Xəta baş verdi: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Update brand
 */
function updateBrand($db, $id) {
    try {
        $input = getJsonInput();
        
        // Check if brand exists
        $checkQuery = "SELECT id FROM markas WHERE id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$id]);
        
        if (!$checkStmt->fetch()) {
            sendResponse(false, 'Marka tapılmadı', null, 404);
            return;
        }
        
        // Validate required fields
        if (empty($input['name'])) {
            sendResponse(false, 'Marka adı tələb olunur', null, 400);
            return;
        }
        
        $query = "UPDATE markas SET 
                    name = ?, 
                    description = ?, 
                    image = ?, 
                    updated_at = CURRENT_TIMESTAMP 
                  WHERE id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $input['name'],
            $input['description'] ?? '',
            $input['image'] ?? '',
            $id
        ]);
        
        // Get the updated brand
        getBrand($db, $id);
        
    } catch (Exception $e) {
        sendResponse(false, 'Xəta baş verdi: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Delete brand
 */
function deleteBrand($db, $id) {
    try {
        // Check if brand exists
        $checkQuery = "SELECT id FROM markas WHERE id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$id]);
        
        if (!$checkStmt->fetch()) {
            sendResponse(false, 'Marka tapılmadı', null, 404);
            return;
        }
        
        // Check if brand has products
        $productQuery = "SELECT COUNT(*) as count FROM products WHERE marka_id = ?";
        $productStmt = $db->prepare($productQuery);
        $productStmt->execute([$id]);
        $productCount = $productStmt->fetch()['count'];
        
        if ($productCount > 0) {
            sendResponse(false, 'Bu marka məhsulları olan marka silinə bilməz', null, 400);
            return;
        }
        
        $query = "DELETE FROM markas WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        sendResponse(true, 'Marka uğurla silindi');
        
    } catch (Exception $e) {
        sendResponse(false, 'Xəta baş verdi: ' . $e->getMessage(), null, 500);
    }
}
?>