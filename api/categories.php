<?php
/**
 * Categories API Endpoints - Yenilənmiş CRUD Sistemi
 * Kateqoriyalar üçün tam CRUD əməliyyatları
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

// Get category ID if provided
$categoryId = isset($pathParts[1]) && is_numeric($pathParts[1]) ? (int)$pathParts[1] : null;

// For PUT and DELETE, also check request body for ID if not in URL
if (!$categoryId && ($method === 'PUT' || $method === 'DELETE')) {
    $input = getJsonInput();
    if (isset($input['id']) && is_numeric($input['id'])) {
        $categoryId = (int)$input['id'];
    }
}

// Route requests
switch ($method) {
    case 'GET':
        if ($categoryId) {
            getCategory($db, $categoryId);
        } else {
            getCategories($db);
        }
        break;
    case 'POST':
        createCategory($db);
        break;
    case 'PUT':
        if ($categoryId) {
            updateCategory($db, $categoryId);
        } else {
            errorResponse('Kateqoriya ID tələb olunur', 400);
        }
        break;
    case 'DELETE':
        if ($categoryId) {
            deleteCategory($db, $categoryId);
        } else {
            errorResponse('Kateqoriya ID tələb olunur', 400);
        }
        break;
    default:
        errorResponse('HTTP metodu dəstəklənmir', 405);
}

/**
 * Bütün kateqoriyaları əldə et (filtrlər və paginasiya ilə)
 */
function getCategories($db) {
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
        $countSql = "SELECT COUNT(*) as total FROM categories $whereClause";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // Kateqoriyaları əldə et (məhsul sayı ilə)
        $sql = "SELECT c.*, 
                       COUNT(p.id) as product_count
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id 
                $whereClause 
                GROUP BY c.id 
                ORDER BY c.$sortBy $sortOrder 
                LIMIT $limit OFFSET $offset";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $categories = $stmt->fetchAll();
        
        // Kateqoriyaları format et
        $formattedCategories = array_map('formatCategory', $categories);
        
        successResponse([
            'data' => $formattedCategories,
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
        errorResponse('Kateqoriyaları əldə edərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Tək kateqoriya əldə et
 */
function getCategory($db, $id) {
    try {
        if (!is_numeric($id) || $id <= 0) {
            errorResponse('Yanlış kateqoriya ID', 400);
        }
        
        $sql = "SELECT c.*, 
                       COUNT(p.id) as product_count
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id 
                WHERE c.id = ? 
                GROUP BY c.id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $category = $stmt->fetch();
        
        if (!$category) {
            errorResponse('Kateqoriya tapılmadı', 404);
        }
        
        successResponse([
            'data' => formatCategory($category)
        ]);
        
    } catch (Exception $e) {
        errorResponse('Kateqoriya əldə edərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Yeni kateqoriya yarat
 */
function createCategory($db) {
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
        $validation = validateCategoryData($input);
        if (!$validation['valid']) {
            errorResponse('Validasiya xətası: ' . implode(', ', $validation['errors']), 400);
        }
        
        $data = $validation['data'];
        
        // Kateqoriya adının unikallığını yoxla
        if (categoryNameExists($db, $data['name'])) {
            errorResponse('Bu adda kateqoriya artıq mövcuddur', 400);
        }
        
        // Kateqoriyanı verilənlər bazasına əlavə et
        $sql = "INSERT INTO categories (name, description, image, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $data['name'],
            $data['description'],
            $data['image']
        ]);
        
        if (!$result) {
            errorResponse('Kateqoriya yaradılarkən xəta baş verdi', 500);
        }
        
        $categoryId = $db->lastInsertId();
        
        // Yaradılmış kateqoriyanı geri qaytar
        $newCategory = getCategoryById($db, $categoryId);
        
        successResponse([
            'message' => 'Kateqoriya uğurla yaradıldı',
            'data' => $newCategory
        ], 201);
        
    } catch (Exception $e) {
        errorResponse('Kateqoriya yaradılarkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Kateqoriyanı yenilə
 */
function updateCategory($db, $id) {
    try {
        // Authentication yoxla (production üçün)
        if ($_ENV['NODE_ENV'] === 'production') {
            $user = authenticateToken();
            if (!$user || $user['role'] !== 'admin') {
                errorResponse('Bu əməliyyat üçün admin hüquqları tələb olunur', 403);
            }
        }
        
        if (!is_numeric($id) || $id <= 0) {
            errorResponse('Yanlış kateqoriya ID', 400);
        }
        
        // Kateqoriyanın mövcudluğunu yoxla
        if (!categoryExists($db, $id)) {
            errorResponse('Kateqoriya tapılmadı', 404);
        }
        
        // Input məlumatlarını əldə et
        $input = getJsonInput();
        
        // Validasiya
        $validation = validateCategoryData($input, false); // false = update üçün
        if (!$validation['valid']) {
            errorResponse('Validasiya xətası: ' . implode(', ', $validation['errors']), 400);
        }
        
        $data = $validation['data'];
        
        // Kateqoriya adının unikallığını yoxla (özündən başqa)
        if (isset($data['name']) && categoryNameExists($db, $data['name'], $id)) {
            errorResponse('Bu adda kateqoriya artıq mövcuddur', 400);
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
        
        $sql = "UPDATE categories SET " . implode(', ', $updateFields) . " WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);
        
        if (!$result) {
            errorResponse('Kateqoriya yenilənərkən xəta baş verdi', 500);
        }
        
        // Yenilənmiş kateqoriyanı geri qaytar
        $updatedCategory = getCategoryById($db, $id);
        
        successResponse([
            'message' => 'Kateqoriya uğurla yeniləndi',
            'data' => $updatedCategory
        ]);
        
    } catch (Exception $e) {
        errorResponse('Kateqoriya yenilənərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Kateqoriyanı sil
 */
function deleteCategory($db, $id) {
    try {
        // Authentication yoxla (production üçün)
        if ($_ENV['NODE_ENV'] === 'production') {
            $user = authenticateToken();
            if (!$user || $user['role'] !== 'admin') {
                errorResponse('Bu əməliyyat üçün admin hüquqları tələb olunur', 403);
            }
        }
        
        if (!is_numeric($id) || $id <= 0) {
            errorResponse('Yanlış kateqoriya ID', 400);
        }
        
        // Kateqoriyanın mövcudluğunu yoxla
        if (!categoryExists($db, $id)) {
            errorResponse('Kateqoriya tapılmadı', 404);
        }
        
        // Bu kateqoriyada məhsul olub-olmadığını yoxla
        $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        $productCount = $stmt->fetchColumn();
        
        if ($productCount > 0) {
            errorResponse('Bu kateqoriyada məhsullar mövcuddur. Əvvəlcə məhsulları silin və ya başqa kateqoriyaya köçürün', 400);
        }
        
        // Kateqoriyanı sil
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if (!$result) {
            errorResponse('Kateqoriya silinərkən xəta baş verdi', 500);
        }
        
        successResponse([
            'message' => 'Kateqoriya uğurla silindi'
        ]);
        
    } catch (Exception $e) {
        errorResponse('Kateqoriya silinərkən xəta: ' . $e->getMessage(), 500);
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
 * Kateqoriya məlumatlarını validasiya et
 */
function validateCategoryData($data, $isCreate = true) {
    $errors = [];
    $validatedData = [];
    
    // Name validasiyası
    if ($isCreate || isset($data['name'])) {
        if (empty($data['name'])) {
            if ($isCreate) $errors[] = 'Kateqoriya adı tələb olunur';
        } else {
            $name = trim($data['name']);
            if (strlen($name) < 2) {
                $errors[] = 'Kateqoriya adı ən azı 2 simvol olmalıdır';
            } elseif (strlen($name) > 255) {
                $errors[] = 'Kateqoriya adı 255 simvoldan çox ola bilməz';
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
    
    // Image validasiyası
    if (isset($data['image'])) {
        $image = trim($data['image']);
        if (strlen($image) > 255) {
            $errors[] = 'Şəkil URL-i 255 simvoldan çox ola bilməz';
        } else {
            $validatedData['image'] = $image ?: null;
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $validatedData
    ];
}

/**
 * Kateqoriya mövcudluğunu yoxla
 */
function categoryExists($db, $id) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Kateqoriya adının mövcudluğunu yoxla
 */
function categoryNameExists($db, $name, $excludeId = null) {
    $sql = "SELECT COUNT(*) FROM categories WHERE name = ?";
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
 * ID ilə kateqoriya əldə et
 */
function getCategoryById($db, $id) {
    $sql = "SELECT c.*, 
                   COUNT(p.id) as product_count
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            WHERE c.id = ? 
            GROUP BY c.id";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    
    return $category ? formatCategory($category) : null;
}

/**
 * Kateqoriya məlumatlarını format et
 */
function formatCategory($category) {
    return [
        'id' => (int)$category['id'],
        'name' => $category['name'],
        'description' => $category['description'],
        'image' => $category['image'],
        'product_count' => (int)($category['product_count'] ?? 0),
        'created_at' => $category['created_at'],
        'updated_at' => $category['updated_at']
    ];
}

?>