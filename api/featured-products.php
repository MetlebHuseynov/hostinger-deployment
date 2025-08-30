<?php
require_once __DIR__ . '/../config/config.php';

// Handle the request based on method and path
switch ($method) {
    case 'GET':
        if (isset($pathParts[1]) && $pathParts[1] === 'public') {
            getFeaturedProducts();
        } else {
            errorResponse('Invalid endpoint', 404);
        }
        break;
    default:
        errorResponse('Method not allowed', 405);
}

function getFeaturedProducts() {
    global $pdo;
    
    try {
        // Get featured products from database
        $stmt = $pdo->prepare("SELECT * FROM products WHERE featured = 1 ORDER BY created_at DESC LIMIT 8");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no products found in database, return static data
        if (empty($products)) {
            $staticProducts = [
                [
                    'id' => '1',
                    'name' => 'Çelik Boru',
                    'description' => 'Yüksek kaliteli çelik boru, endüstriyel kullanım için ideal',
                    'price' => 45.50,
                    'image' => 'images/steel-pipe.svg',
                    'featured' => true
                ],
                [
                    'id' => '2',
                    'name' => 'İnşaat Demiri',
                    'description' => 'Dayanıklı inşaat demiri, yapı güvenliği için',
                    'price' => 32.75,
                    'image' => 'images/rebar.svg',
                    'featured' => true
                ],
                [
                    'id' => '3',
                    'name' => 'Alüminyum Profil',
                    'description' => 'Hafif ve dayanıklı alüminyum profil sistemleri',
                    'price' => 28.90,
                    'image' => 'images/aluminum-profile.svg',
                    'featured' => true
                ],
                [
                    'id' => '4',
                    'name' => 'Kaynak Elektrodu',
                    'description' => 'Profesyonel kaynak işleri için yüksek kalite elektrodu',
                    'price' => 15.25,
                    'image' => 'images/welding-electrode.svg',
                    'featured' => true
                ]
            ];
            
            successResponse($staticProducts);
        } else {
            successResponse($products);
        }
        
    } catch (PDOException $e) {
        errorResponse('Database error: ' . $e->getMessage(), 500);
    }
}
?>