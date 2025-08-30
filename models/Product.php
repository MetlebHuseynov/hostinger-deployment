<?php
/**
 * Product Model Class
 * Məhsul əməliyyatları üçün model sinifi
 */

class Product {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Bütün məhsulları əldə et (filtrlər və paginasiya ilə)
     */
    public function getAll($filters = []) {
        try {
            $page = $filters['page'] ?? 1;
            $limit = $filters['limit'] ?? 10;
            $offset = ($page - 1) * $limit;
            
            $whereConditions = [];
            $params = [];
            
            // Category filter
            if (!empty($filters['category'])) {
                $whereConditions[] = "p.category_id = ?";
                $params[] = $filters['category'];
            }
            
            // Marka filter
            if (!empty($filters['marka'])) {
                $whereConditions[] = "p.marka_id = ?";
                $params[] = $filters['marka'];
            }
            
            // Search filter
            if (!empty($filters['search'])) {
                $whereConditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            $sql = "
                SELECT p.*, c.name as category_name, m.name as marka_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN markas m ON p.marka_id = m.id 
                {$whereClause}
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll();
            
            // Get total count for pagination
            $countSql = "
                SELECT COUNT(*) as total 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN markas m ON p.marka_id = m.id 
                {$whereClause}
            ";
            
            $countParams = array_slice($params, 0, -2); // Remove limit and offset
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($countParams);
            $total = $countStmt->fetch()['total'];
            
            return [
                'products' => $products,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'totalPages' => ceil($total / $limit)
                ]
            ];
            
        } catch (Exception $e) {
            return ['error' => 'Server xətası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Məhsulu ID ilə əldə et
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, c.name as category_name, m.name as marka_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN markas m ON p.marka_id = m.id 
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Yeni məhsul yarat
     */
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO products (name, description, price, category_id, marka_id, image) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['price'],
                $data['category_id'] ?? null,
                $data['marka_id'] ?? null,
                $data['image'] ?? null
            ]);
            
            $productId = $this->db->lastInsertId();
            return ['success' => true, 'id' => $productId];
            
        } catch (Exception $e) {
            return ['error' => 'Server xətası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Məhsulu yenilə
     */
    public function update($id, $data) {
        try {
            $updateFields = [];
            $params = [];
            
            if (isset($data['name'])) {
                $updateFields[] = 'name = ?';
                $params[] = $data['name'];
            }
            
            if (isset($data['description'])) {
                $updateFields[] = 'description = ?';
                $params[] = $data['description'];
            }
            
            if (isset($data['price'])) {
                $updateFields[] = 'price = ?';
                $params[] = $data['price'];
            }
            
            if (isset($data['category_id'])) {
                $updateFields[] = 'category_id = ?';
                $params[] = $data['category_id'];
            }
            
            if (isset($data['marka_id'])) {
                $updateFields[] = 'marka_id = ?';
                $params[] = $data['marka_id'];
            }
            
            if (isset($data['image'])) {
                $updateFields[] = 'image = ?';
                $params[] = $data['image'];
            }
            
            if (empty($updateFields)) {
                return ['error' => 'Yeniləmək üçün heç bir məlumat verilməyib'];
            }
            
            $updateFields[] = 'updated_at = CURRENT_TIMESTAMP';
            $params[] = $id;
            
            $sql = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['error' => 'Server xətası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Məhsulu sil
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['error' => 'Server xətası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Məhsul sayını əldə et
     */
    public function getCount() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM products");
            $stmt->execute();
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Kateqoriyaya görə məhsul sayını əldə et
     */
    public function getCountByCategory() {
        try {
            $stmt = $this->db->prepare("
                SELECT c.name, COUNT(p.id) as count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id 
                GROUP BY c.id, c.name 
                ORDER BY count DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Markaya görə məhsul sayını əldə et
     */
    public function getCountByMarka() {
        try {
            $stmt = $this->db->prepare("
                SELECT m.name, COUNT(p.id) as count 
                FROM markas m 
                LEFT JOIN products p ON m.id = p.marka_id 
                GROUP BY m.id, m.name 
                ORDER BY count DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Son əlavə edilən məhsulları əldə et
     */
    public function getRecent($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, c.name as category_name, m.name as marka_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN markas m ON p.marka_id = m.id 
                ORDER BY p.created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Aylıq məhsul yaratma statistikası
     */
    public function getMonthlyStats($months = 6) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count
                FROM products 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC
            ");
            $stmt->execute([$months]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
?>