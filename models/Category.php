<?php
/**
 * Category Model Class
 * Kateqoriya əməliyyatları üçün model sinifi
 */

class Category {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Bütün kateqoriyaları əldə et (paginasiya ilə)
     */
    public function getAll($page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            
            $stmt = $this->db->prepare("
                SELECT * FROM categories 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $categories = $stmt->fetchAll();
            
            // Get total count for pagination
            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM categories");
            $countStmt->execute();
            $total = $countStmt->fetch()['total'];
            
            return [
                'categories' => $categories,
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
     * Bütün kateqoriyaları əldə et (dropdown üçün)
     */
    public function getAllForDropdown() {
        try {
            $stmt = $this->db->prepare("SELECT id, name FROM categories ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Kateqoriyanı ID ilə əldə et
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Kateqoriyanı ad ilə əldə et
     */
    public function findByName($name) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM categories WHERE name = ?");
            $stmt->execute([$name]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Yeni kateqoriya yarat
     */
    public function create($data) {
        try {
            // Check if category name already exists
            if ($this->nameExists($data['name'])) {
                return ['error' => 'Bu kateqoriya adı artıq mövcuddur'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO categories (name, description, image) 
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['image'] ?? null
            ]);
            
            $categoryId = $this->db->lastInsertId();
            return ['success' => true, 'id' => $categoryId];
            
        } catch (Exception $e) {
            return ['error' => 'Server xətası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Kateqoriyanı yenilə
     */
    public function update($id, $data) {
        try {
            $updateFields = [];
            $params = [];
            
            if (isset($data['name'])) {
                // Check if name is taken by another category
                if ($this->nameExistsExcept($data['name'], $id)) {
                    return ['error' => 'Bu kateqoriya adı artıq mövcuddur'];
                }
                $updateFields[] = 'name = ?';
                $params[] = $data['name'];
            }
            
            if (isset($data['description'])) {
                $updateFields[] = 'description = ?';
                $params[] = $data['description'];
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
            
            $sql = "UPDATE categories SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['error' => 'Server xətası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Kateqoriyanı sil
     */
    public function delete($id) {
        try {
            // Check if category is used by any products
            if ($this->isUsedByProducts($id)) {
                return ['error' => 'Bu kateqoriya məhsullar tərəfindən istifadə olunur və silinə bilməz'];
            }
            
            $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['error' => 'Server xətası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Kateqoriya sayını əldə et
     */
    public function getCount() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM categories");
            $stmt->execute();
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Kateqoriya adının mövcudluğunu yoxla
     */
    private function nameExists($name) {
        $stmt = $this->db->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Kateqoriya adının mövcudluğunu yoxla (müəyyən ID istisna olmaqla)
     */
    private function nameExistsExcept($name, $exceptId) {
        $stmt = $this->db->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $stmt->execute([$name, $exceptId]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Kateqoriyanın məhsullar tərəfindən istifadə olunub-olunmadığını yoxla
     */
    private function isUsedByProducts($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Kateqoriyaya aid məhsul sayını əldə et
     */
    public function getProductCount($id) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Ən çox məhsulu olan kateqoriyaları əldə et
     */
    public function getTopCategories($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, COUNT(p.id) as product_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id 
                GROUP BY c.id 
                ORDER BY product_count DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
?>