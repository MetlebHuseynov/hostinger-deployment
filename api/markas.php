<?php
/**
 * Brands/Markas API Endpoints - Yenilənmiş CRUD Sistemi
 * Markalar üçün tam CRUD əməliyyatları
 */

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

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
            errorResponse('Marka ID tələb olunur', 400);
        }
        break;
    case 'DELETE':
        if ($brandId) {
            deleteBrand($db, $brandId);
        } else {
            errorResponse('Marka ID tələb olunur', 400);
        }
        break;
    default:
        errorResponse('HTTP metodu dəstəklənmir', 405);
}

/**
 * Bütün markaları əldə et (filtrlər və paginasiya ilə)
 */
function getBrands($db) {
    try {
        // Pagination parametrləri
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 10;
        $offset = ($page - 1) * $limit;
        
        // Filter parametrləri
        $search = $_GET['search'] ?? null;
        $sortBy = $_GET['sort_by'] ?? 'created_at';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        
        // Təhlükəsizlik: icazə verilən sort sahələri
        $allowedSortFields = ['id', 'name', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }
        
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        // WHERE şərtlərini qur
        $whereConditions = [];
        $params = [];
        
        if ($search) {
            $whereConditions[] = "(name LIKE ? OR description LIKE ?)";
            $searchTerm = "%" . $search . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Ümumi sayı əldə et
        $countSql = "SELECT COUNT(*) as total FROM markas $whereClause";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // Markaları əldə et (məhsul sayı ilə)
        $sql = "SELECT m.*, 
                       COUNT(p.id) as product_count
                FROM markas m 
                LEFT JOIN products p ON m.id = p.marka_id 
                $whereClause 
                GROUP BY m.id 
                ORDER BY m.$sortBy $sortOrder 
                LIMIT $limit OFFSET $offset";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $brands = $stmt->fetchAll();
        
        // Markaları format et
        $formattedBrands = array_map('formatBrand', $brands);
        
        successResponse([
            'data' => $formattedBrands,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => (int)$total,
                'total_pages' => ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1
            ],
            'filters' => [
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder
            ]
        ]);
        
    } catch (Exception $e) {
        errorResponse('Markaları əldə edərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Tək marka əldə et
 */
function getBrand($db, $id) {
    try {
        if (!is_numeric($id) || $id <= 0) {
            errorResponse('Yanlış marka ID', 400);
        }
        
        $sql = "SELECT m.*, 
                       COUNT(p.id) as product_count
                FROM markas m 
                LEFT JOIN products p ON m.id = p.brand_id 
                WHERE m.id = ? 
                GROUP BY m.id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $brand = $stmt->fetch();
        
        if (!$brand) {
            errorResponse('Marka tapılmadı', 404);
        }
        
        successResponse([
            'data' => formatBrand($brand)
        ]);
        
    } catch (Exception $e) {
        errorResponse('Marka əldə edərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Yeni marka yarat
 */
function createBrand($db) {
    try {
        // Authentication yoxla (production üçün)
        if ($_ENV['NODE_ENV'] === 'production') {
            $user = authenticateToken();
            if (!$user || $user['role'] !== 'admin') {
                errorResponse('Bu əməliyyat üçün admin hüquqları tələb olunur', 403);
            }
        }
        
        // Input məlumatlarını əldə et
        $input = getJsonInput();
        
        // Validasiya
        $validation = validateBrandData($input);
        if (!$validation['valid']) {
            errorResponse('Validasiya xətası: ' . implode(', ', $validation['errors']), 400);
        }
        
        $data = $validation['data'];
        
        // Marka adının unikallığını yoxla
        if (brandNameExists($db, $data['name'])) {
            errorResponse('Bu adda marka artıq mövcuddur', 400);
        }
        
        // Markanı verilənlər bazasına əlavə et
        $sql = "INSERT INTO markas (name, description, logo, website, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $data['name'],
            $data['description'],
            $data['logo'],
            $data['website']
        ]);
        
        if (!$result) {
            errorResponse('Marka yaradılarkən xəta baş verdi', 500);
        }
        
        $brandId = $db->lastInsertId();
        
        // Yaradılmış markanı geri qaytar
        $newBrand = getBrandById($db, $brandId);
        
        successResponse([
            'message' => 'Marka uğurla yaradıldı',
            'data' => $newBrand
        ], 201);
        
    } catch (Exception $e) {
        errorResponse('Marka yaradılarkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Markanı yenilə
 */
function updateBrand($db, $id) {
    try {
        // Authentication yoxla (production üçün)
        if ($_ENV['NODE_ENV'] === 'production') {
            $user = authenticateToken();
            if (!$user || $user['role'] !== 'admin') {
                errorResponse('Bu əməliyyat üçün admin hüquqları tələb olunur', 403);
            }
        }
        
        if (!is_numeric($id) || $id <= 0) {
            errorResponse('Yanlış marka ID', 400);
        }
        
        // Markanın mövcudluğunu yoxla
        if (!brandExists($db, $id)) {
            errorResponse('Marka tapılmadı', 404);
        }
        
        // Input məlumatlarını əldə et
        $input = getJsonInput();
        
        // Validasiya
        $validation = validateBrandData($input, false); // false = update üçün
        if (!$validation['valid']) {
            errorResponse('Validasiya xətası: ' . implode(', ', $validation['errors']), 400);
        }
        
        $data = $validation['data'];
        
        // Marka adının unikallığını yoxla (özündən başqa)
        if (isset($data['name']) && brandNameExists($db, $data['name'], $id)) {
            errorResponse('Bu adda marka artıq mövcuddur', 400);
        }
        
        // Update sorğusunu qur
        $updateFields = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            if ($value !== null) {
                $updateFields[] = "$field = ?";
                $params[] = $value;
            }
        }
        
        if (empty($updateFields)) {
            errorResponse('Yeniləmək üçün heç bir sahə təqdim edilməyib', 400);
        }
        
        $updateFields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE markas SET " . implode(', ', $updateFields) . " WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);
        
        if (!$result) {
            errorResponse('Marka yenilənərkən xəta baş verdi', 500);
        }
        
        // Yenilənmiş markanı geri qaytar
        $updatedBrand = getBrandById($db, $id);
        
        successResponse([
            'message' => 'Marka uğurla yeniləndi',
            'data' => $updatedBrand
        ]);
        
    } catch (Exception $e) {
        errorResponse('Marka yenilənərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Markanı sil
 */
function deleteBrand($db, $id) {
    try {
        // Authentication yoxla (production üçün)
        if ($_ENV['NODE_ENV'] === 'production') {
            $user = authenticateToken();
            if (!$user || $user['role'] !== 'admin') {
                errorResponse('Bu əməliyyat üçün admin hüquqları tələb olunur', 403);
            }
        }
        
        if (!is_numeric($id) || $id <= 0) {
            errorResponse('Yanlış marka ID', 400);
        }
        
        // Markanın mövcudluğunu yoxla
        if (!brandExists($db, $id)) {
            errorResponse('Marka tapılmadı', 404);
        }
        
        // Bu markada məhsul olub-olmadığını yoxla
        $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE brand_id = ?");
        $stmt->execute([$id]);
        $productCount = $stmt->fetchColumn();
        
        if ($productCount > 0) {
            errorResponse('Bu markada məhsullar mövcuddur. Əvvəlcə məhsulları silin və ya başqa markaya köçürün', 400);
        }
        
        // Markanı sil
        $stmt = $db->prepare("DELETE FROM markas WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if (!$result) {
            errorResponse('Marka silinərkən xəta baş verdi', 500);
        }
        
        successResponse([
            'message' => 'Marka uğurla silindi'
        ]);
        
    } catch (Exception $e) {
        errorResponse('Marka silinərkən xəta: ' . $e->getMessage(), 500);
    }
}

// Helper Functions

/**
 * JSON input əldə et
 */
function getJsonInput() {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        // JSON parse edilə bilmirsə, POST məlumatlarını yoxla
        $input = $_POST;
    }
    
    return $input ?: [];
}

/**
 * Marka məlumatlarını validasiya et
 */
function validateBrandData($data, $isCreate = true) {
    $errors = [];
    $validatedData = [];
    
    // Name validasiyası
    if ($isCreate || isset($data['name'])) {
        if (empty($data['name'])) {
            if ($isCreate) $errors[] = 'Marka adı tələb olunur';
        } else {
            $name = trim($data['name']);
            if (strlen($name) < 2) {
                $errors[] = 'Marka adı ən azı 2 simvol olmalıdır';
            } elseif (strlen($name) > 255) {
                $errors[] = 'Marka adı 255 simvoldan çox ola bilməz';
            } else {
                $validatedData['name'] = $name;
            }
        }
    }
    
    // Description validasiyası
    if (isset($data['description'])) {
        $description = trim($data['description']);
        if (strlen($description) > 1000) {
            $errors[] = 'Təsvir 1000 simvoldan çox ola bilməz';
        } else {
            $validatedData['description'] = $description ?: null;
        }
    }
    
    // Logo validasiyası
    if (isset($data['logo'])) {
        $logo = trim($data['logo']);
        if (strlen($logo) > 255) {
            $errors[] = 'Logo URL-i 255 simvoldan çox ola bilməz';
        } else {
            $validatedData['logo'] = $logo ?: null;
        }
    }
    
    // Website validasiyası
    if (isset($data['website'])) {
        $website = trim($data['website']);
        if (!empty($website)) {
            if (!filter_var($website, FILTER_VALIDATE_URL)) {
                $errors[] = 'Yanlış website URL formatı';
            } elseif (strlen($website) > 255) {
                $errors[] = 'Website URL-i 255 simvoldan çox ola bilməz';
            } else {
                $validatedData['website'] = $website;
            }
        } else {
            $validatedData['website'] = null;
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $validatedData
    ];
}

/**
 * Marka mövcudluğunu yoxla
 */
function brandExists($db, $id) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM markas WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Marka adının mövcudluğunu yoxla
 */
function brandNameExists($db, $name, $excludeId = null) {
    $sql = "SELECT COUNT(*) FROM markas WHERE name = ?";
    $params = [$name];
    
    if ($excludeId) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

/**
 * ID ilə marka əldə et
 */
function getBrandById($db, $id) {
    $sql = "SELECT m.*, 
                   COUNT(p.id) as product_count
            FROM markas m 
            LEFT JOIN products p ON m.id = p.brand_id 
            WHERE m.id = ? 
            GROUP BY m.id";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    $brand = $stmt->fetch();
    
    return $brand ? formatBrand($brand) : null;
}

/**
 * Marka məlumatlarını format et
 */
function formatBrand($brand) {
    return [
        'id' => (int)$brand['id'],
        'name' => $brand['name'],
        'description' => $brand['description'],
        'logo' => $brand['image'] ?? null,
        'website' => null,
        'product_count' => (int)($brand['product_count'] ?? 0),
        'created_at' => $brand['created_at'],
        'updated_at' => $brand['updated_at']
    ];
}

?>