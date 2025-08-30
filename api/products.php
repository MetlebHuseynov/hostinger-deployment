<?php
/**
 * Products API Endpoints - Yenilənmiş CRUD Sistemi
 * Məhsullar üçün tam CRUD əməliyyatları
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

// Get product ID if provided
$productId = isset($pathParts[1]) && is_numeric($pathParts[1]) ? (int)$pathParts[1] : null;

// For PUT and DELETE, also check request body for ID if not in URL
if (!$productId && ($method === 'PUT' || $method === 'DELETE')) {
    $input = getJsonInput();
    if (isset($input['id']) && is_numeric($input['id'])) {
        $productId = (int)$input['id'];
    }
}

// Route requests
switch ($method) {
    case 'GET':
        if ($productId) {
            getProduct($db, $productId);
        } else {
            getProducts($db);
        }
        break;
    case 'POST':
        createProduct($db);
        break;
    case 'PUT':
        if ($productId) {
            updateProduct($db, $productId);
        } else {
            errorResponse('Məhsul ID tələb olunur', 400);
        }
        break;
    case 'DELETE':
        if ($productId) {
            deleteProduct($db, $productId);
        } else {
            errorResponse('Məhsul ID tələb olunur', 400);
        }
        break;
    default:
        errorResponse('HTTP metodu dəstəklənmir', 405);
}

/**
 * Bütün məhsulları əldə et (filtrlər və paginasiya ilə)
 */
function getProducts($db) {
    try {
        // Pagination parametrləri
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 10;
        $offset = ($page - 1) * $limit;
        
        // Filter parametrləri
        $category = $_GET['category'] ?? null;
        $marka = $_GET['marka'] ?? null;
        $search = $_GET['search'] ?? null;
        $minPrice = $_GET['min_price'] ?? null;
        $maxPrice = $_GET['max_price'] ?? null;
        $sortBy = $_GET['sort_by'] ?? 'created_at';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        
        // Təhlükəsizlik: icazə verilən sort sahələri
        $allowedSortFields = ['id', 'name', 'price', 'stock', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }
        
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        // WHERE şərtlərini qur
        $whereConditions = [];
        $params = [];
        
        if ($category && is_numeric($category)) {
            $whereConditions[] = "p.category_id = ?";
            $params[] = (int)$category;
        }
        
        if ($marka && is_numeric($marka)) {
            $whereConditions[] = "p.marka_id = ?";
            $params[] = (int)$marka;
        }
        
        if ($search) {
            $whereConditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%" . $search . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($minPrice && is_numeric($minPrice)) {
            $whereConditions[] = "p.price >= ?";
            $params[] = (float)$minPrice;
        }
        
        if ($maxPrice && is_numeric($maxPrice)) {
            $whereConditions[] = "p.price <= ?";
            $params[] = (float)$maxPrice;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Ümumi sayı əldə et
        $countSql = "SELECT COUNT(*) as total FROM products p $whereClause";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // Məhsulları əldə et
        $sql = "SELECT p.*, 
                       c.name as category_name, 
                       m.name as marka_name,
                       m.logo as marka_logo
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN markas m ON p.marka_id = m.id 
                $whereClause 
                ORDER BY p.$sortBy $sortOrder 
                LIMIT $limit OFFSET $offset";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        // Məhsulları format et
        $formattedProducts = array_map('formatProduct', $products);
        
        successResponse([
            'data' => $formattedProducts,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => (int)$total,
                'total_pages' => ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1
            ],
            'filters' => [
                'category' => $category,
                'marka' => $marka,
                'search' => $search,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder
            ]
        ]);
        
    } catch (Exception $e) {
        errorResponse('Məhsulları əldə edərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Tək məhsul əldə et
 */
function getProduct($db, $id) {
    try {
        if (!is_numeric($id) || $id <= 0) {
            errorResponse('Yanlış məhsul ID', 400);
        }
        
        $sql = "SELECT p.*, 
                       c.name as category_name, 
                       c.description as category_description,
                       m.name as marka_name,
                       m.description as marka_description,
                       m.logo as marka_logo,
                       m.website as marka_website
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN markas m ON p.marka_id = m.id 
                WHERE p.id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            errorResponse('Məhsul tapılmadı', 404);
        }
        
        successResponse([
            'data' => formatProduct($product)
        ]);
        
    } catch (Exception $e) {
        errorResponse('Məhsul əldə edərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Yeni məhsul yarat
 */
function createProduct($db) {
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
        $validation = validateProductData($input);
        if (!$validation['valid']) {
            errorResponse('Validasiya xətası: ' . implode(', ', $validation['errors']), 400);
        }
        
        $data = $validation['data'];
        
        // Kateqoriya və marka mövcudluğunu yoxla
        if ($data['category_id'] && !categoryExists($db, $data['category_id'])) {
            errorResponse('Seçilmiş kateqoriya mövcud deyil', 400);
        }
        
        if ($data['marka_id'] && !markaExists($db, $data['marka_id'])) {
            errorResponse('Seçilmiş marka mövcud deyil', 400);
        }
        
        // Məhsulu verilənlər bazasına əlavə et
        $sql = "INSERT INTO products (name, description, price, category_id, marka_id, image, stock, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['category_id'],
            $data['marka_id'],
            $data['image'],
            $data['stock']
        ]);
        
        if (!$result) {
            errorResponse('Məhsul yaradılarkən xəta baş verdi', 500);
        }
        
        $productId = $db->lastInsertId();
        
        // Yaradılmış məhsulu geri qaytar
        $newProduct = getProductById($db, $productId);
        
        successResponse([
            'message' => 'Məhsul uğurla yaradıldı',
            'data' => $newProduct
        ], 201);
        
    } catch (Exception $e) {
        errorResponse('Məhsul yaradılarkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Məhsulu yenilə
 */
function updateProduct($db, $id) {
    try {
        // Authentication yoxla (production üçün)
        if ($_ENV['NODE_ENV'] === 'production') {
            $user = authenticateToken();
            if (!$user || $user['role'] !== 'admin') {
                errorResponse('Bu əməliyyat üçün admin hüquqları tələb olunur', 403);
            }
        }
        
        if (!is_numeric($id) || $id <= 0) {
            errorResponse('Yanlış məhsul ID', 400);
        }
        
        // Məhsulun mövcudluğunu yoxla
        if (!productExists($db, $id)) {
            errorResponse('Məhsul tapılmadı', 404);
        }
        
        // Input məlumatlarını əldə et
        $input = getJsonInput();
        
        // Validasiya
        $validation = validateProductData($input, false); // false = update üçün
        if (!$validation['valid']) {
            errorResponse('Validasiya xətası: ' . implode(', ', $validation['errors']), 400);
        }
        
        $data = $validation['data'];
        
        // Kateqoriya və marka mövcudluğunu yoxla
        if ($data['category_id'] && !categoryExists($db, $data['category_id'])) {
            errorResponse('Seçilmiş kateqoriya mövcud deyil', 400);
        }
        
        if ($data['marka_id'] && !markaExists($db, $data['marka_id'])) {
            errorResponse('Seçilmiş marka mövcud deyil', 400);
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
        
        $updateFields[] = "updated_at = datetime('now')";
        $params[] = $id;
        
        $sql = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);
        
        if (!$result) {
            errorResponse('Məhsul yenilənərkən xəta baş verdi', 500);
        }
        
        // Yenilənmiş məhsulu geri qaytar
        $updatedProduct = getProductById($db, $id);
        
        successResponse([
            'message' => 'Məhsul uğurla yeniləndi',
            'data' => $updatedProduct
        ]);
        
    } catch (Exception $e) {
        errorResponse('Məhsul yenilənərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Məhsulu sil
 */
function deleteProduct($db, $id) {
    try {
        // Authentication yoxla (production üçün)
        if ($_ENV['NODE_ENV'] === 'production') {
            $user = authenticateToken();
            if (!$user || $user['role'] !== 'admin') {
                errorResponse('Bu əməliyyat üçün admin hüquqları tələb olunur', 403);
            }
        }
        
        if (!is_numeric($id) || $id <= 0) {
            errorResponse('Yanlış məhsul ID', 400);
        }
        
        // Məhsulun mövcudluğunu yoxla
        if (!productExists($db, $id)) {
            errorResponse('Məhsul tapılmadı', 404);
        }
        
        // Məhsulu sil
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if (!$result) {
            errorResponse('Məhsul silinərkən xəta baş verdi', 500);
        }
        
        successResponse([
            'message' => 'Məhsul uğurla silindi'
        ]);
        
    } catch (Exception $e) {
        errorResponse('Məhsul silinərkən xəta: ' . $e->getMessage(), 500);
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
 * Məhsul məlumatlarını validasiya et
 */
function validateProductData($data, $isCreate = true) {
    $errors = [];
    $validatedData = [];
    
    // Name validasiyası
    if ($isCreate || isset($data['name'])) {
        if (empty($data['name'])) {
            if ($isCreate) $errors[] = 'Məhsul adı tələb olunur';
        } else {
            $name = trim($data['name']);
            if (strlen($name) < 2) {
                $errors[] = 'Məhsul adı ən azı 2 simvol olmalıdır';
            } elseif (strlen($name) > 255) {
                $errors[] = 'Məhsul adı 255 simvoldan çox ola bilməz';
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
    
    // Price validasiyası
    if ($isCreate || isset($data['price'])) {
        if (!isset($data['price']) || $data['price'] === '') {
            if ($isCreate) $errors[] = 'Qiymət tələb olunur';
        } else {
            $price = filter_var($data['price'], FILTER_VALIDATE_FLOAT);
            if ($price === false || $price < 0) {
                $errors[] = 'Qiymət müsbət rəqəm olmalıdır';
            } elseif ($price > 999999.99) {
                $errors[] = 'Qiymət çox yüksəkdir';
            } else {
                $validatedData['price'] = $price;
            }
        }
    }
    
    // Category ID validasiyası
    if (isset($data['category_id'])) {
        if ($data['category_id'] === '' || $data['category_id'] === null) {
            $validatedData['category_id'] = null;
        } else {
            $categoryId = filter_var($data['category_id'], FILTER_VALIDATE_INT);
            if ($categoryId === false || $categoryId <= 0) {
                $errors[] = 'Kateqoriya ID müsbət tam ədəd olmalıdır';
            } else {
                $validatedData['category_id'] = $categoryId;
            }
        }
    }
    
    // Marka ID validasiyası
    if (isset($data['marka_id'])) {
        if ($data['marka_id'] === '' || $data['marka_id'] === null) {
            $validatedData['marka_id'] = null;
        } else {
            $markaId = filter_var($data['marka_id'], FILTER_VALIDATE_INT);
            if ($markaId === false || $markaId <= 0) {
                $errors[] = 'Marka ID müsbət tam ədəd olmalıdır';
            } else {
                $validatedData['marka_id'] = $markaId;
            }
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
    
    // Stock validasiyası
    if (isset($data['stock'])) {
        $stock = filter_var($data['stock'], FILTER_VALIDATE_INT);
        if ($stock === false || $stock < 0) {
            $errors[] = 'Stok miqdarı müsbət tam ədəd olmalıdır';
        } else {
            $validatedData['stock'] = $stock;
        }
    } elseif ($isCreate) {
        $validatedData['stock'] = 0;
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $validatedData
    ];
}

/**
 * Məhsul mövcudluğunu yoxla
 */
function productExists($db, $id) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
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
 * Marka mövcudluğunu yoxla
 */
function markaExists($db, $id) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM markas WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

/**
 * ID ilə məhsul əldə et
 */
function getProductById($db, $id) {
    $sql = "SELECT p.*, 
                   c.name as category_name, 
                   m.name as marka_name,
                   m.logo as marka_logo
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN markas m ON p.marka_id = m.id 
            WHERE p.id = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    return $product ? formatProduct($product) : null;
}

/**
 * Məhsul məlumatlarını format et
 */
function formatProduct($product) {
    return [
        'id' => (int)$product['id'],
        'name' => $product['name'],
        'description' => $product['description'],
        'price' => (float)$product['price'],
        'category_id' => $product['category_id'] ? (int)$product['category_id'] : null,
        'category_name' => $product['category_name'] ?? null,
        'marka_id' => $product['marka_id'] ? (int)$product['marka_id'] : null,
        'marka_name' => $product['marka_name'] ?? null,
        'marka_logo' => $product['marka_logo'] ?? null,
        'image' => $product['image'],
        'stock' => (int)$product['stock'],
        'created_at' => $product['created_at'],
        'updated_at' => $product['updated_at']
    ];
}

?>