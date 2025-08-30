<?php
/**
 * Database Configuration for PHP
 * Hostinger MySQL bağlantısı üçün konfiqurasiya
 */

// Mock Database class for local development
class MockDatabase {
    private $dataFile;
    private $lastStatement = null;
    
    public function __construct() {
        $dataDir = __DIR__ . '/../database';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        $this->dataFile = $dataDir . '/products.json';
        
        // Initialize with empty array if file doesn't exist
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, json_encode([]));
        }
    }
    
    public function prepare($sql) {
        $this->lastStatement = new MockStatement($this->dataFile, $sql);
        return $this->lastStatement;
    }
    
    public function lastInsertId() {
        if ($this->lastStatement && $this->lastStatement->lastInsertId !== null) {
            return $this->lastStatement->lastInsertId;
        }
        // Fallback to counting data
        $data = json_decode(file_get_contents($this->dataFile), true) ?: [];
        return count($data);
    }
    
    public function createTables() {
        // Mock method - no actual tables needed for JSON storage
        return true;
    }
}

// Mock PDO Statement for local development
class MockStatement {
    private $dataFile;
    private $sql;
    private $params = [];
    public $lastInsertId = null;
    
    public function __construct($dataFile, $sql) {
        $this->dataFile = $dataFile;
        $this->sql = $sql;
    }
    
    public function bindParam($param, $value) {
        $this->params[$param] = $value;
    }
    
    public function fetch($fetchStyle = PDO::FETCH_ASSOC) {
        // Handle COUNT queries
        if (stripos($this->sql, 'COUNT(*)') !== false) {
            $count = 0;
            
            if (stripos($this->sql, 'categories') !== false) {
                $categoriesFile = dirname($this->dataFile) . '/categories.json';
                if (file_exists($categoriesFile)) {
                    $categories = json_decode(file_get_contents($categoriesFile), true) ?: [];
                    $count = count($categories);
                }
            } elseif (stripos($this->sql, 'markas') !== false) {
                $markasFile = dirname($this->dataFile) . '/markas.json';
                if (file_exists($markasFile)) {
                    $markas = json_decode(file_get_contents($markasFile), true) ?: [];
                    $count = count($markas);
                }
            } elseif (stripos($this->sql, 'products') !== false) {
                $products = json_decode(file_get_contents($this->dataFile), true) ?: [];
                $count = count($products);
            } elseif (stripos($this->sql, 'users') !== false) {
                $count = 1; // Mock user count
            }
            
            return ['count' => $count];
        }
        
        // Mock fetch method for users table
        if (strpos($this->sql, 'users') !== false && strpos($this->sql, 'SELECT') !== false) {
            // Return mock admin user
            return [
                'id' => 1,
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin'
            ];
        }
        
        // Handle categories table
        if (stripos($this->sql, 'categories') !== false && stripos($this->sql, 'WHERE') !== false) {
            $categoriesFile = dirname($this->dataFile) . '/categories.json';
            if (file_exists($categoriesFile)) {
                $categories = json_decode(file_get_contents($categoriesFile), true) ?: [];
                
                // Find category by ID if WHERE id = ? is in the query
                if (!empty($this->params) && isset($this->params[0])) {
                    $searchId = (int)$this->params[0];
                    foreach ($categories as $category) {
                        if (isset($category['id']) && (int)$category['id'] === $searchId) {
                            $category['product_count'] = 0; // Mock product count
                            return $category;
                        }
                    }
                }
            }
            return false;
        }
        
        return false;
    }
    
    public function execute($params = null) {
        if ($params) {
            $this->params = array_merge($this->params, $params);
        }
        
        $data = json_decode(file_get_contents($this->dataFile), true) ?: [];
        
        if (stripos($this->sql, 'INSERT') === 0) {
            // Handle INSERT
            $newId = count($data) + 1;
            
            // Handle categories table
            if (stripos($this->sql, 'categories') !== false) {
                $categoriesFile = dirname($this->dataFile) . '/categories.json';
                $categories = [];
                if (file_exists($categoriesFile)) {
                    $categories = json_decode(file_get_contents($categoriesFile), true) ?: [];
                }
                $newId = count($categories) + 1;
                
                $newCategory = [
                    'id' => $newId,
                    'name' => $this->params[0] ?? '',
                    'description' => $this->params[1] ?? '',
                    'image' => $this->params[2] ?? '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $categories[] = $newCategory;
                file_put_contents($categoriesFile, json_encode($categories, JSON_PRETTY_PRINT));
                $this->lastInsertId = $newId;
                return true;
            }
            
            // Handle products table (existing logic)
            $newProduct = [
                'id' => $newId,
                'name' => $this->params[':name'] ?? '',
                'description' => $this->params[':description'] ?? '',
                'price' => $this->params[':price'] ?? 0,
                'category_id' => $this->params[':category_id'] ?? null,
                'marka_id' => $this->params[':marka_id'] ?? null,
                'image' => $this->params[':image'] ?? '',
                'stock' => $this->params[':stock'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $data[] = $newProduct;
            file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT));
            $this->lastInsertId = $newId;
            return true;
        } elseif (stripos($this->sql, 'SELECT') === 0) {
            // Handle SELECT - return true to indicate success, actual data fetched in fetchAll
            return true;
        } elseif (stripos($this->sql, 'DELETE') === 0) {
            // Handle DELETE
            if (stripos($this->sql, 'categories') !== false) {
                $categoriesFile = dirname($this->dataFile) . '/categories.json';
                if (file_exists($categoriesFile)) {
                    $categories = json_decode(file_get_contents($categoriesFile), true) ?: [];
                    
                    // Remove category by ID
                    if (!empty($this->params) && isset($this->params[0])) {
                        $deleteId = (int)$this->params[0];
                        $categories = array_filter($categories, function($category) use ($deleteId) {
                            return isset($category['id']) && (int)$category['id'] !== $deleteId;
                        });
                        
                        // Re-index array to maintain proper JSON structure
                        $categories = array_values($categories);
                        file_put_contents($categoriesFile, json_encode($categories, JSON_PRETTY_PRINT));
                        return true;
                    }
                }
            }
            return true;
        } elseif (stripos($this->sql, 'UPDATE') === 0) {
            // Handle UPDATE
            if (stripos($this->sql, 'categories') !== false) {
                $categoriesFile = dirname($this->dataFile) . '/categories.json';
                if (file_exists($categoriesFile)) {
                    $categories = json_decode(file_get_contents($categoriesFile), true) ?: [];
                    
                    // Update category by ID (assuming last parameter is the ID)
                    if (!empty($this->params)) {
                        $updateId = (int)end($this->params); // Last parameter is usually the ID
                        $updateFields = array_slice($this->params, 0, -1); // All except last parameter
                        
                        foreach ($categories as &$category) {
                            if (isset($category['id']) && (int)$category['id'] === $updateId) {
                                // Update fields based on the SQL structure
                                // This is a simplified approach - in real implementation, parse SQL properly
                                if (count($updateFields) >= 1) $category['name'] = $updateFields[0];
                                if (count($updateFields) >= 2) $category['description'] = $updateFields[1];
                                if (count($updateFields) >= 3) $category['image'] = $updateFields[2];
                                $category['updated_at'] = date('Y-m-d H:i:s');
                                break;
                            }
                        }
                        
                        file_put_contents($categoriesFile, json_encode($categories, JSON_PRETTY_PRINT));
                        return true;
                    }
                }
            }
            return true;
        }
        
        return true;
    }
    
    public function fetchAll() {
        // Handle categories table
        if (stripos($this->sql, 'categories') !== false) {
            $categoriesFile = dirname($this->dataFile) . '/categories.json';
            if (file_exists($categoriesFile)) {
                $categories = json_decode(file_get_contents($categoriesFile), true) ?: [];
                
                // Add product_count to each category (mock data)
                foreach ($categories as &$category) {
                    $category['product_count'] = 0; // Mock product count
                }
                
                return $categories;
            }
            return [];
        }
        
        // Handle products table (default)
        $data = json_decode(file_get_contents($this->dataFile), true) ?: [];
        return $data;
    }
    
    public function fetchColumn($columnIndex = 0) {
        // Handle COUNT(*) queries
        if (stripos($this->sql, 'COUNT(*)') !== false) {
            $data = json_decode(file_get_contents($this->dataFile), true) ?: [];
            
            // Handle categories table
            if (stripos($this->sql, 'categories') !== false) {
                $categoriesFile = dirname($this->dataFile) . '/categories.json';
                if (file_exists($categoriesFile)) {
                    $categories = json_decode(file_get_contents($categoriesFile), true) ?: [];
                    
                    // If checking for specific parameter (name or id)
                    if (!empty($this->params) && isset($this->params[0])) {
                        $searchValue = $this->params[0];
                        $count = 0;
                        
                        // Check if searching by ID (WHERE id = ?)
                        if (stripos($this->sql, 'WHERE id = ?') !== false) {
                            foreach ($categories as $category) {
                                if (isset($category['id']) && $category['id'] == $searchValue) {
                                    $count++;
                                }
                            }
                        }
                        // Check if searching by name (WHERE name = ?)
                        else if (stripos($this->sql, 'WHERE name = ?') !== false) {
                            foreach ($categories as $category) {
                                if (isset($category['name']) && $category['name'] === $searchValue) {
                                    $count++;
                                }
                            }
                        }
                        return $count;
                    }
                    return count($categories);
                }
                return 0;
            }
            
            // Handle markas table
            if (stripos($this->sql, 'markas') !== false) {
                $markasFile = dirname($this->dataFile) . '/markas.json';
                if (file_exists($markasFile)) {
                    $markas = json_decode(file_get_contents($markasFile), true) ?: [];
                    
                    // If checking for specific name
                    if (!empty($this->params) && isset($this->params[0])) {
                        $searchName = $this->params[0];
                        $count = 0;
                        foreach ($markas as $marka) {
                            if (isset($marka['name']) && $marka['name'] === $searchName) {
                                $count++;
                            }
                        }
                        return $count;
                    }
                    return count($markas);
                }
                return 0;
            }
            
            // Handle products table - check for specific category_id
            if (stripos($this->sql, 'products') !== false && stripos($this->sql, 'WHERE category_id = ?') !== false) {
                if (!empty($this->params) && isset($this->params[0])) {
                    $categoryId = (int)$this->params[0];
                    $count = 0;
                    foreach ($data as $product) {
                        if (isset($product['category_id']) && (int)$product['category_id'] === $categoryId) {
                            $count++;
                        }
                    }
                    return $count;
                }
            }
            
            // Default products count
            return count($data);
        }
        
        return 0;
    }
}

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;
    
    public function __construct() {
        // .env faylından məlumatları oxu
        $this->loadEnv();
        
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? 'u482576391_yUuh0';
        $this->username = $_ENV['DB_USER'] ?? 'u482576391_Md1PB';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        $this->port = $_ENV['DB_PORT'] ?? '3306';
        
        // Initialize connection
        $this->conn = $this->getConnection();
    }
    
    private function loadEnv() {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                }
            }
        }
    }
    
    public function getConnection() {
        // For local development without database, return a mock connection
        if ($_ENV['NODE_ENV'] === 'development') {
            return new MockDatabase();
        }
        
        $this->conn = null;
        
        try {
            // Production MySQL connection
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Create tables if they don't exist
            $this->createTables();
            
        } catch(PDOException $exception) {
            // Log the error for debugging
            error_log("Database connection error: " . $exception->getMessage());
            
            // Return JSON error response instead of HTML
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => 'Verilənlər bazası bağlantı xətası',
                'message' => 'Database connection failed: ' . $exception->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        return $this->conn;
    }
    
    public function createTables() {
        try {
            // Skip table creation for MockDatabase
            if ($this->conn instanceof MockDatabase) {
                return $this->conn->createTables();
            }
            
            // Check if we're using SQLite or MySQL
            $driver = $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
            
            if ($driver === 'sqlite') {
                // SQLite syntax
                $sql = "CREATE TABLE IF NOT EXISTS products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    description TEXT,
                    price REAL NOT NULL,
                    category_id INTEGER,
                    marka_id INTEGER,
                    image TEXT,
                    stock INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )";
            } else {
                // MySQL syntax
                $sql = "CREATE TABLE IF NOT EXISTS products (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    description TEXT,
                    price DECIMAL(10,2) NOT NULL,
                    category_id INT,
                    marka_id INT,
                    image VARCHAR(255),
                    stock INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
                    FOREIGN KEY (marka_id) REFERENCES markas(id) ON DELETE SET NULL
                )";
            }
            $this->conn->exec($sql);
            
            // Categories table
            $sql = "CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                image VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $this->conn->exec($sql);
            
            // Markas table
            $sql = "CREATE TABLE IF NOT EXISTS markas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                logo VARCHAR(255),
                website VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $this->conn->exec($sql);
            
            // Users table
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'user') DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $this->conn->exec($sql);
            
            return true;
        } catch(PDOException $exception) {
            // Log the error for debugging
            error_log("Table creation error: " . $exception->getMessage());
            
            // Return JSON error response instead of HTML
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => 'Cədvəl yaratma xətası',
                'message' => 'Table creation failed: ' . $exception->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

// Global function for backward compatibility
function getDBConnection() {
    $database = new Database();
    return $database->getConnection();
}
?>