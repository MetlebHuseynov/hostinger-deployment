<?php
/**
 * Users API Endpoints - Yenilənmiş CRUD Sistemi
 * İstifadəçilər üçün tam CRUD əməliyyatları və authentication
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

// Get user ID if provided
$userId = isset($pathParts[1]) && is_numeric($pathParts[1]) ? (int)$pathParts[1] : null;

// Special endpoints
if (isset($pathParts[1])) {
    switch ($pathParts[1]) {
        case 'login':
            if ($method === 'POST') {
                loginUser($db);
            } else {
                errorResponse('Yalnız POST metodu dəstəklənir', 405);
            }
            return;
        case 'register':
            if ($method === 'POST') {
                registerUser($db);
            } else {
                errorResponse('Yalnız POST metodu dəstəklənir', 405);
            }
            return;
        case 'profile':
            if ($method === 'GET') {
                getUserProfile($db);
            } elseif ($method === 'PUT') {
                updateUserProfile($db);
            } else {
                errorResponse('GET və ya PUT metodu dəstəklənir', 405);
            }
            return;
        case 'change-password':
            if ($method === 'POST') {
                changePassword($db);
            } else {
                errorResponse('Yalnız POST metodu dəstəklənir', 405);
            }
            return;
    }
}

// Route requests for CRUD operations
switch ($method) {
    case 'GET':
        if ($userId) {
            getUser($db, $userId);
        } else {
            getUsers($db);
        }
        break;
    case 'POST':
        createUser($db);
        break;
    case 'PUT':
        if ($userId) {
            updateUser($db, $userId);
        } else {
            errorResponse('İstifadəçi ID tələb olunur', 400);
        }
        break;
    case 'DELETE':
        if ($userId) {
            deleteUser($db, $userId);
        } else {
            errorResponse('İstifadəçi ID tələb olunur', 400);
        }
        break;
    default:
        errorResponse('HTTP metodu dəstəklənmir', 405);
}

/**
 * İstifadəçi girişi
 */
function loginUser($db) {
    try {
        $input = getJsonInput();
        
        if (empty($input['email']) || empty($input['password'])) {
            errorResponse('Email və şifrə tələb olunur', 400);
        }
        
        $email = trim($input['email']);
        $password = $input['password'];
        
        // Email validasiyası
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            errorResponse('Yanlış email formatı', 400);
        }
        
        // İstifadəçini tap
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password'])) {
            errorResponse('Yanlış email və ya şifrə', 401);
        }
        
        // İstifadəçi aktiv olub-olmadığını yoxla
        if ($user['status'] !== 'active') {
            errorResponse('Hesab aktiv deyil', 403);
        }
        
        // JWT token yarat
        $token = generateJWT([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);
        
        // Son giriş vaxtını yenilə
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        successResponse([
            'message' => 'Uğurla daxil oldunuz',
            'token' => $token,
            'user' => formatUserForResponse($user)
        ]);
        
    } catch (Exception $e) {
        errorResponse('Giriş zamanı xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * İstifadəçi qeydiyyatı
 */
function registerUser($db) {
    try {
        $input = getJsonInput();
        
        // Validasiya
        $validation = validateUserData($input, true, true); // true = create, true = registration
        if (!$validation['valid']) {
            errorResponse('Validasiya xətası: ' . implode(', ', $validation['errors']), 400);
        }
        
        $data = $validation['data'];
        
        // Email unikallığını yoxla
        if (userEmailExists($db, $data['email'])) {
            errorResponse('Bu email artıq istifadə olunur', 400);
        }
        
        // Şifrəni hash et
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // İstifadəçini yarat
        $sql = "INSERT INTO users (name, email, password, role, status, created_at, updated_at) 
                VALUES (?, ?, ?, 'user', 'active', NOW(), NOW())";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $data['name'],
            $data['email'],
            $hashedPassword
        ]);
        
        if (!$result) {
            errorResponse('İstifadəçi yaradılarkən xəta baş verdi', 500);
        }
        
        $userId = $db->lastInsertId();
        
        // JWT token yarat
        $token = generateJWT([
            'user_id' => $userId,
            'email' => $data['email'],
            'role' => 'user'
        ]);
        
        // Yaradılmış istifadəçini geri qaytar
        $newUser = getUserById($db, $userId);
        
        successResponse([
            'message' => 'Qeydiyyat uğurla tamamlandı',
            'token' => $token,
            'user' => $newUser
        ], 201);
        
    } catch (Exception $e) {
        errorResponse('Qeydiyyat zamanı xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * İstifadəçi profilini əldə et
 */
function getUserProfile($db) {
    try {
        $user = authenticateToken();
        if (!$user) {
            errorResponse('Authentication tələb olunur', 401);
        }
        
        $userProfile = getUserById($db, $user['user_id']);
        
        successResponse([
            'data' => $userProfile
        ]);
        
    } catch (Exception $e) {
        errorResponse('Profil əldə edərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * İstifadəçi profilini yenilə
 */
function updateUserProfile($db) {
    try {
        $user = authenticateToken();
        if (!$user) {
            errorResponse('Authentication tələb olunur', 401);
        }
        
        $input = getJsonInput();
        
        // Profil yeniləmə üçün validasiya (şifrə olmadan)
        $validation = validateUserData($input, false, false, true); // false = update, false = not registration, true = profile update
        if (!$validation['valid']) {
            errorResponse('Validasiya xətası: ' . implode(', ', $validation['errors']), 400);
        }
        
        $data = $validation['data'];
        
        // Email dəyişdirilirsə, unikallığını yoxla
        if (isset($data['email']) && userEmailExists($db, $data['email'], $user['user_id'])) {
            errorResponse('Bu email artıq istifadə olunur', 400);
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
        $params[] = $user['user_id'];
        
        $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);
        
        if (!$result) {
            errorResponse('Profil yenilənərkən xəta baş verdi', 500);
        }
        
        // Yenilənmiş profili geri qaytar
        $updatedUser = getUserById($db, $user['user_id']);
        
        successResponse([
            'message' => 'Profil uğurla yeniləndi',
            'data' => $updatedUser
        ]);
        
    } catch (Exception $e) {
        errorResponse('Profil yenilənərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Şifrə dəyişdir
 */
function changePassword($db) {
    try {
        $user = authenticateToken();
        if (!$user) {
            errorResponse('Authentication tələb olunur', 401);
        }
        
        $input = getJsonInput();
        
        if (empty($input['current_password']) || empty($input['new_password'])) {
            errorResponse('Cari şifrə və yeni şifrə tələb olunur', 400);
        }
        
        // Cari şifrəni yoxla
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user['user_id']]);
        $currentUser = $stmt->fetch();
        
        if (!password_verify($input['current_password'], $currentUser['password'])) {
            errorResponse('Cari şifrə yanlışdır', 400);
        }
        
        // Yeni şifrəni validasiya et
        if (strlen($input['new_password']) < 6) {
            errorResponse('Yeni şifrə ən azı 6 simvol olmalıdır', 400);
        }
        
        // Yeni şifrəni hash et və yenilə
        $hashedPassword = password_hash($input['new_password'], PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$hashedPassword, $user['user_id']]);
        
        if (!$result) {
            errorResponse('Şifrə dəyişdirilərkən xəta baş verdi', 500);
        }
        
        successResponse([
            'message' => 'Şifrə uğurla dəyişdirildi'
        ]);
        
    } catch (Exception $e) {
        errorResponse('Şifrə dəyişdirilərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Bütün istifadəçiləri əldə et (admin üçün)
 */
function getUsers($db) {
    try {
        // Admin authentication
        $user = authenticateToken();
        if (!$user || $user['role'] !== 'admin') {
            errorResponse('Bu əməliyyat üçün admin hüquqları tələb olunur', 403);
        }
        
        // Pagination parametrləri
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 10;
        $offset = ($page - 1) * $limit;
        
        // Filter parametrləri
        $search = $_GET['search'] ?? null;
        $role = $_GET['role'] ?? null;
        $status = $_GET['status'] ?? null;
        $sortBy = $_GET['sort_by'] ?? 'created_at';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        
        // Təhlükəsizlik: icazə verilən sort sahələri
        $allowedSortFields = ['id', 'name', 'email', 'role', 'status', 'created_at', 'last_login'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }
        
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        // WHERE şərtlərini qur
        $whereConditions = [];
        $params = [];
        
        if ($search) {
            $whereConditions[] = "(name LIKE ? OR email LIKE ?)";
            $searchTerm = "%" . $search . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($role) {
            $whereConditions[] = "role = ?";
            $params[] = $role;
        }
        
        if ($status) {
            $whereConditions[] = "status = ?";
            $params[] = $status;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Ümumi sayı əldə et
        $countSql = "SELECT COUNT(*) as total FROM users $whereClause";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // İstifadəçiləri əldə et
        $sql = "SELECT id, name, email, role, status, created_at, updated_at, last_login 
                FROM users 
                $whereClause 
                ORDER BY $sortBy $sortOrder 
                LIMIT $limit OFFSET $offset";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        // İstifadəçiləri format et
        $formattedUsers = array_map('formatUserForResponse', $users);
        
        successResponse([
            'data' => $formattedUsers,
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
                'role' => $role,
                'status' => $status,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder
            ]
        ]);
        
    } catch (Exception $e) {
        errorResponse('İstifadəçiləri əldə edərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Tək istifadəçi əldə et (admin üçün)
 */
function getUser($db, $id) {
    try {
        // Admin authentication
        $user = authenticateToken();
        if (!$user || $user['role'] !== 'admin') {
            errorResponse('Bu əməliyyat üçün admin hüquqları tələb olunur', 403);
        }
        
        if (!is_numeric($id) || $id <= 0) {
            errorResponse('Yanlış istifadəçi ID', 400);
        }
        
        $userData = getUserById($db, $id);
        
        if (!$userData) {
            errorResponse('İstifadəçi tapılmadı', 404);
        }
        
        successResponse([
            'data' => $userData
        ]);
        
    } catch (Exception $e) {
        errorResponse('İstifadəçi əldə edərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * Yeni istifadəçi yarat (admin üçün)
 */
function createUser($db) {
    try {
        // Admin authentication
        $user = authenticateToken();
        if (!$user || $user['role'] !== 'admin') {
            errorResponse('Bu əməliyyat üçün admin hüquqları tələb olunur', 403);
        }
        
        $input = getJsonInput();
        
        // Validasiya
        $validation = validateUserData($input, true, false); // true = create, false = not registration
        if (!$validation['valid']) {
            errorResponse('Validasiya xətası: ' . implode(', ', $validation['errors']), 400);
        }
        
        $data = $validation['data'];
        
        // Email unikallığını yoxla
        if (userEmailExists($db, $data['email'])) {
            errorResponse('Bu email artıq istifadə olunur', 400);
        }
        
        // Şifrəni hash et
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // İstifadəçini yarat
        $sql = "INSERT INTO users (name, email, password, role, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $data['name'],
            $data['email'],
            $hashedPassword,
            $data['role'],
            $data['status']
        ]);
        
        if (!$result) {
            errorResponse('İstifadəçi yaradılarkən xəta baş verdi', 500);
        }
        
        $userId = $db->lastInsertId();
        
        // Yaradılmış istifadəçini geri qaytar
        $newUser = getUserById($db, $userId);
        
        successResponse([
            'message' => 'İstifadəçi uğurla yaradıldı',
            'data' => $newUser
        ], 201);
        
    } catch (Exception $e) {
        errorResponse('İstifadəçi yaradılarkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * İstifadəçini yenilə (admin üçün)
 */
function updateUser($db, $id) {
    try {
        // Admin authentication
        $user = authenticateToken();
        if (!$user || $user['role'] !== 'admin') {
            errorResponse('Bu əməliyyat üçün admin hüquqları tələb olunur', 403);
        }
        
        if (!is_numeric($id) || $id <= 0) {
            errorResponse('Yanlış istifadəçi ID', 400);
        }
        
        // İstifadəçinin mövcudluğunu yoxla
        if (!userExists($db, $id)) {
            errorResponse('İstifadəçi tapılmadı', 404);
        }
        
        $input = getJsonInput();
        
        // Validasiya
        $validation = validateUserData($input, false, false); // false = update, false = not registration
        if (!$validation['valid']) {
            errorResponse('Validasiya xətası: ' . implode(', ', $validation['errors']), 400);
        }
        
        $data = $validation['data'];
        
        // Email dəyişdirilirsə, unikallığını yoxla
        if (isset($data['email']) && userEmailExists($db, $data['email'], $id)) {
            errorResponse('Bu email artıq istifadə olunur', 400);
        }
        
        // Şifrə dəyişdirilirsə, hash et
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
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
        
        $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);
        
        if (!$result) {
            errorResponse('İstifadəçi yenilənərkən xəta baş verdi', 500);
        }
        
        // Yenilənmiş istifadəçini geri qaytar
        $updatedUser = getUserById($db, $id);
        
        successResponse([
            'message' => 'İstifadəçi uğurla yeniləndi',
            'data' => $updatedUser
        ]);
        
    } catch (Exception $e) {
        errorResponse('İstifadəçi yenilənərkən xəta: ' . $e->getMessage(), 500);
    }
}

/**
 * İstifadəçini sil (admin üçün)
 */
function deleteUser($db, $id) {
    try {
        // Admin authentication
        $user = authenticateToken();
        if (!$user || $user['role'] !== 'admin') {
            errorResponse('Bu əməliyyat üçün admin hüquqları tələb olunur', 403);
        }
        
        if (!is_numeric($id) || $id <= 0) {
            errorResponse('Yanlış istifadəçi ID', 400);
        }
        
        // Özünü silməyə icazə vermə
        if ($id == $user['user_id']) {
            errorResponse('Öz hesabınızı silə bilməzsiniz', 400);
        }
        
        // İstifadəçinin mövcudluğunu yoxla
        if (!userExists($db, $id)) {
            errorResponse('İstifadəçi tapılmadı', 404);
        }
        
        // İstifadəçini sil
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if (!$result) {
            errorResponse('İstifadəçi silinərkən xəta baş verdi', 500);
        }
        
        successResponse([
            'message' => 'İstifadəçi uğurla silindi'
        ]);
        
    } catch (Exception $e) {
        errorResponse('İstifadəçi silinərkən xəta: ' . $e->getMessage(), 500);
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
 * İstifadəçi məlumatlarını validasiya et
 */
function validateUserData($data, $isCreate = true, $isRegistration = false, $isProfileUpdate = false) {
    $errors = [];
    $validatedData = [];
    
    // Name validasiyası
    if ($isCreate || isset($data['name'])) {
        if (empty($data['name'])) {
            if ($isCreate) $errors[] = 'Ad tələb olunur';
        } else {
            $name = trim($data['name']);
            if (strlen($name) < 2) {
                $errors[] = 'Ad ən azı 2 simvol olmalıdır';
            } elseif (strlen($name) > 255) {
                $errors[] = 'Ad 255 simvoldan çox ola bilməz';
            } else {
                $validatedData['name'] = $name;
            }
        }
    }
    
    // Email validasiyası
    if ($isCreate || isset($data['email'])) {
        if (empty($data['email'])) {
            if ($isCreate) $errors[] = 'Email tələb olunur';
        } else {
            $email = trim($data['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Yanlış email formatı';
            } elseif (strlen($email) > 255) {
                $errors[] = 'Email 255 simvoldan çox ola bilməz';
            } else {
                $validatedData['email'] = $email;
            }
        }
    }
    
    // Password validasiyası (profil yeniləməsində şifrə tələb olunmur)
    if (!$isProfileUpdate && ($isCreate || isset($data['password']))) {
        if (empty($data['password'])) {
            if ($isCreate) $errors[] = 'Şifrə tələb olunur';
        } else {
            $password = $data['password'];
            if (strlen($password) < 6) {
                $errors[] = 'Şifrə ən azı 6 simvol olmalıdır';
            } elseif (strlen($password) > 255) {
                $errors[] = 'Şifrə 255 simvoldan çox ola bilməz';
            } else {
                $validatedData['password'] = $password;
            }
        }
    }
    
    // Role validasiyası (qeydiyyatda role təyin edilmir)
    if (!$isRegistration && !$isProfileUpdate && isset($data['role'])) {
        $allowedRoles = ['admin', 'user'];
        if (!in_array($data['role'], $allowedRoles)) {
            $errors[] = 'Yanlış rol. İcazə verilən rollər: ' . implode(', ', $allowedRoles);
        } else {
            $validatedData['role'] = $data['role'];
        }
    }
    
    // Status validasiyası (qeydiyyatda və profil yeniləməsində status təyin edilmir)
    if (!$isRegistration && !$isProfileUpdate && isset($data['status'])) {
        $allowedStatuses = ['active', 'inactive', 'suspended'];
        if (!in_array($data['status'], $allowedStatuses)) {
            $errors[] = 'Yanlış status. İcazə verilən statuslar: ' . implode(', ', $allowedStatuses);
        } else {
            $validatedData['status'] = $data['status'];
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $validatedData
    ];
}

/**
 * İstifadəçi mövcudluğunu yoxla
 */
function userExists($db, $id) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Email mövcudluğunu yoxla
 */
function userEmailExists($db, $email, $excludeId = null) {
    $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
    $params = [$email];
    
    if ($excludeId) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

/**
 * ID ilə istifadəçi əldə et
 */
function getUserById($db, $id) {
    $stmt = $db->prepare("SELECT id, name, email, role, status, created_at, updated_at, last_login FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    return $user ? formatUserForResponse($user) : null;
}

/**
 * İstifadəçi məlumatlarını response üçün format et
 */
function formatUserForResponse($user) {
    return [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'status' => $user['status'],
        'created_at' => $user['created_at'],
        'updated_at' => $user['updated_at'],
        'last_login' => $user['last_login']
    ];
}

?>