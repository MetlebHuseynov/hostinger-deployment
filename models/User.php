<?php
/**
 * User Model Class
 * İstifadəçi əməliyyatları üçün model sinifi
 */

class User {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * İstifadəçini username və ya email ilə tap
     */
    public function findByUsernameOrEmail($usernameOrEmail) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM users 
                WHERE username = ? OR email = ?
            ");
            $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * İstifadəçini ID ilə tap
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * İstifadəçini ID ilə tap (parolsuz)
     */
    public function findByIdWithoutPassword($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, role, created_at, updated_at 
                FROM users WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Yeni istifadəçi yarat
     */
    public function create($username, $email, $password, $role = 'user') {
        try {
            // Check if username exists
            if ($this->usernameExists($username)) {
                return ['error' => 'Bu istifadəçi adı artıq mövcuddur'];
            }
            
            // Check if email exists
            if ($this->emailExists($email)) {
                return ['error' => 'Bu email artıq mövcuddur'];
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, role) 
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([$username, $email, $hashedPassword, $role]);
            $userId = $this->db->lastInsertId();
            
            return ['success' => true, 'id' => $userId];
            
        } catch (Exception $e) {
            return ['error' => 'Server xətası: ' . $e->getMessage()];
        }
    }
    
    /**
     * İstifadəçini yenilə
     */
    public function update($id, $data) {
        try {
            $updateFields = [];
            $params = [];
            
            if (isset($data['username'])) {
                // Check if username is taken by another user
                if ($this->usernameExistsExcept($data['username'], $id)) {
                    return ['error' => 'Bu istifadəçi adı artıq mövcuddur'];
                }
                $updateFields[] = 'username = ?';
                $params[] = $data['username'];
            }
            
            if (isset($data['email'])) {
                // Check if email is taken by another user
                if ($this->emailExistsExcept($data['email'], $id)) {
                    return ['error' => 'Bu email artıq mövcuddur'];
                }
                $updateFields[] = 'email = ?';
                $params[] = $data['email'];
            }
            
            if (isset($data['password'])) {
                $updateFields[] = 'password = ?';
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            if (isset($data['role'])) {
                $updateFields[] = 'role = ?';
                $params[] = $data['role'];
            }
            
            if (empty($updateFields)) {
                return ['error' => 'Yeniləmək üçün heç bir məlumat verilməyib'];
            }
            
            $updateFields[] = 'updated_at = CURRENT_TIMESTAMP';
            $params[] = $id;
            
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['error' => 'Server xətası: ' . $e->getMessage()];
        }
    }
    
    /**
     * İstifadəçini sil
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['error' => 'Server xətası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Bütün istifadəçiləri əldə et
     */
    public function getAll($limit = 50, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, role, created_at, updated_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * İstifadəçi sayını əldə et
     */
    public function getCount() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users");
            $stmt->execute();
            return $stmt->fetch()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Parolun düzgünlüyünü yoxla
     */
    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }
    
    /**
     * İstifadəçi adının mövcudluğunu yoxla
     */
    private function usernameExists($username) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Email-in mövcudluğunu yoxla
     */
    private function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * İstifadəçi adının mövcudluğunu yoxla (müəyyən ID istisna olmaqla)
     */
    private function usernameExistsExcept($username, $exceptId) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $exceptId]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Email-in mövcudluğunu yoxla (müəyyən ID istisna olmaqla)
     */
    private function emailExistsExcept($email, $exceptId) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $exceptId]);
        return $stmt->fetch() !== false;
    }
}
?>