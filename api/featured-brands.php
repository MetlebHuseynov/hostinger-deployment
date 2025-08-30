<?php
require_once __DIR__ . '/../config/config.php';

// Handle the request based on method and path
switch ($method) {
    case 'GET':
        if (isset($pathParts[1]) && $pathParts[1] === 'public') {
            getFeaturedBrands();
        } else {
            errorResponse('Invalid endpoint', 404);
        }
        break;
    default:
        errorResponse('Method not allowed', 405);
}

function getFeaturedBrands() {
    global $pdo;
    
    try {
        // Get featured brands from database
        $stmt = $pdo->prepare("SELECT * FROM markas WHERE featured = 1 ORDER BY name ASC LIMIT 6");
        $stmt->execute();
        $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no brands found in database, return static data
        if (empty($brands)) {
            $staticBrands = [
                [
                    'id' => '1',
                    'name' => 'Bosch',
                    'logo' => 'images/bosch-logo.svg',
                    'featured' => true
                ],
                [
                    'id' => '2',
                    'name' => 'Siemens',
                    'logo' => 'images/siemens-logo.svg',
                    'featured' => true
                ],
                [
                    'id' => '3',
                    'name' => 'Schneider Electric',
                    'logo' => 'images/schneider-logo.svg',
                    'featured' => true
                ],
                [
                    'id' => '4',
                    'name' => 'ABB',
                    'logo' => 'images/abb-logo.svg',
                    'featured' => true
                ],
                [
                    'id' => '5',
                    'name' => 'Danfoss',
                    'logo' => 'images/danfoss-logo.svg',
                    'featured' => true
                ],
                [
                    'id' => '6',
                    'name' => 'Honeywell',
                    'logo' => 'images/honeywell-logo.svg',
                    'featured' => true
                ]
            ];
            
            successResponse($staticBrands);
        } else {
            successResponse($brands);
        }
        
    } catch (PDOException $e) {
        errorResponse('Database error: ' . $e->getMessage(), 500);
    }
}
?>