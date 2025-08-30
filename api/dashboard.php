<?php
/**
 * Dashboard API Endpoints
 * Dashboard statistikaları üçün
 */

switch ($method) {
    case 'GET':
        getDashboardStats($db);
        break;
    default:
        errorResponse('Method dəstəklənmir', 405);
}

function getDashboardStats($db) {
    $user = authenticateToken();
    
    // Only admin can view dashboard stats
    if ($user['role'] !== 'admin') {
        errorResponse('Bu əməliyyat üçün admin hüququ tələb olunur', 403);
    }
    
    try {
        $stats = [];
        
        // Get total products count
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM products");
        $stmt->execute();
        $stats['totalProducts'] = $stmt->fetch()['count'];
        
        // Get total categories count
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM categories");
        $stmt->execute();
        $stats['totalCategories'] = $stmt->fetch()['count'];
        
        // Get total markas count
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM markas");
        $stmt->execute();
        $stats['totalMarkas'] = $stmt->fetch()['count'];
        
        // Get total users count
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $stats['totalUsers'] = $stmt->fetch()['count'];
        
        // Get recent products (last 5)
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name, m.name as marka_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN markas m ON p.marka_id = m.id 
            ORDER BY p.created_at DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $stats['recentProducts'] = $stmt->fetchAll();
        
        // Get products by category
        $stmt = $db->prepare("
            SELECT c.name, COUNT(p.id) as count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id, c.name 
            ORDER BY count DESC
        ");
        $stmt->execute();
        $stats['productsByCategory'] = $stmt->fetchAll();
        
        // Get products by marka
        $stmt = $db->prepare("
            SELECT m.name, COUNT(p.id) as count 
            FROM markas m 
            LEFT JOIN products p ON m.id = p.marka_id 
            GROUP BY m.id, m.name 
            ORDER BY count DESC
        ");
        $stmt->execute();
        $stats['productsByMarka'] = $stmt->fetchAll();
        
        // Get monthly product creation stats (last 6 months)
        $stmt = $db->prepare("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM products 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
        ");
        $stmt->execute();
        $stats['monthlyProducts'] = $stmt->fetchAll();
        
        successResponse($stats);
        
    } catch (Exception $e) {
        errorResponse('Server xətası: ' . $e->getMessage(), 500);
    }
}
?>