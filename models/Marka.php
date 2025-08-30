<?php
/**
 * Marka Model Class
 * Marka əməliyyatları üçün model sinifi
 */

class Marka {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Bütün markaları əldə et (paginasiya ilə)
     */
    public function getAll($page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            
            $stmt = $this->db->prepare("
                SELECT * FROM markas 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $markas = $stmt->fetchAll();
            
            // Get total count for pagination
            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM markas");
            $countStmt->execute();
            $total = $countStmt->fetch()['total'];
            
            return [
                'markas' => $markas,
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
     * Bütün markaları əldə et (dropdown üçün)
     */
    public function getAllForDropdown() {
        try {
            $stmt = $this->db->prepare("SELECT id, name FROM markas ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Markanı ID ilə əldə et
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM markas WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Markanı ad ilə əldə et
     */
    public function findByName($name) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM markas WHERE name = ?");
            $stmt->execute([$name]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Yeni marka yarat
     */
    public function create($data) {
        try {
            // Check if marka name already exists
            if ($this->nameExists($data['name'])) {
                return ['error' => 'Bu marka adı artıq mövcuddur'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO markas (name, description, image) 
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['image'] ?? null
            ]);
            
            $markaId = $this->db->lastInsertId();
            return ['success' => true, 'id' => $markaId];
            
        } catch (Exception $e) {
            return ['error' => 'Server xətası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Markanı yenilə
     */
    public function update($id, $data) {
        try {
            $updateFields = [];
            $params = [];
            
            if (isset($data['name'])) {
                // Check if name is taken by another marka
                if ($this->nameExistsExcept($data['name'], $id)) {
                    return ['error' => 'Bu marka adı artıq mövcuddur'];
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
            
            $sql = "UPDATE markas SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['error' => 'Server xətası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Markanı sil
     */
    public function delete($id) {
        try {
            // Check if marka is used by any products
            if ($this->isUsedByProducts($id)) {
                return ['error' => 'Bu marka məhsullar tərəfindən istifadə olunur və silinə bilməz'];
            }
            
            $stmt = $this->db->prepare("DELETE FROM markas WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['error' => 'Server xətası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Marka sayını əldə et
     */
    public function getCount() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM markas");
            $stmt->execute();
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Marka adının mövcudluğunu yoxla
     */
    private function nameExists($name) {
        $stmt = $this->db->prepare("SELECT id FROM markas WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Marka adının mövcudluğunu yoxla (müəyyən ID istisna olmaqla)
     */
    private function nameExistsExcept($name, $exceptId) {
        $stmt = $this->db->prepare("SELECT id FROM markas WHERE name = ? AND id != ?");
        $stmt->execute([$name, $exceptId]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Markanın məhsullar tərəfindən istifadə olunub-olunmadığını yoxla
     */
    private function isUsedByProducts($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM products WHERE marka_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Markaya aid məhsul sayını əldə et
     */
    public function getProductCount($id) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM products WHERE marka_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Ən çox məhsulu olan markaları əldə et
     */
    public function getTopMarkas($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT m.*, COUNT(p.id) as product_count 
                FROM markas m 
                LEFT JOIN products p ON m.id = p.marka_id 
                GROUP BY m.id 
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