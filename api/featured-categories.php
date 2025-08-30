<?php
require_once __DIR__ . '/../config/config.php';

// Handle the request based on method and path
switch ($method) {
    case 'GET':
        if (isset($pathParts[1]) && $pathParts[1] === 'public') {
            getFeaturedCategories();
        } else {
            errorResponse('Invalid endpoint', 404);
        }
        break;
    default:
        errorResponse('Method not allowed', 405);
}

function getFeaturedCategories() {
    global $pdo;
    
    try {
        // Get featured categories from database
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE featured = 1 ORDER BY name ASC LIMIT 6");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no categories found in database, return static data
        if (empty($categories)) {
            $staticCategories = [
                [
                    'id' => '1',
                    'name' => 'Çelik Məhsulları',
                    'description' => 'Yüksək keyfiyyətli çelik məhsulları',
                    'image' => 'images/category-placeholder.svg',
                    'featured' => true
                ],
                [
                    'id' => '2',
                    'name' => 'İnşaat Materialları',
                    'description' => 'Müxtəlif inşaat materialları',
                    'image' => 'images/category-placeholder.svg',
                    'featured' => true
                ],
                [
                    'id' => '3',
                    'name' => 'Elektrik Avadanlıqları',
                    'description' => 'Elektrik və avtomatlaşdırma avadanlıqları',
                    'image' => 'images/category-placeholder.svg',
                    'featured' => true
                ],
                [
                    'id' => '4',
                    'name' => 'Kaynak Avadanlıqları',
                    'description' => 'Peşəkar kaynak avadanlıqları',
                    'image' => 'images/category-placeholder.svg',
                    'featured' => true
                ]
            ];
            
            successResponse($staticCategories);
        } else {
            successResponse($categories);
        }
        
    } catch (PDOException $e) {
        errorResponse('Database error: ' . $e->getMessage(), 500);
    }
}
?>