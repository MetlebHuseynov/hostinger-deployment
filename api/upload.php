<?php
/**
 * File Upload API
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Set CORS headers
setCorsHeaders();

header('Content-Type: application/json; charset=utf-8');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Fayl yüklənmədi və ya xəta baş verdi');
    }
    
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception('Yalnız şəkil faylları qəbul edilir (JPEG, PNG, GIF, WebP)');
    }
    
    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($fileSize > $maxSize) {
        throw new Exception('Fayl ölçüsü 5MB-dan böyük ola bilməz');
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $uniqueFileName;
    
    // Move uploaded file
    if (!move_uploaded_file($fileTmpName, $uploadPath)) {
        throw new Exception('Fayl yüklənərkən xəta baş verdi');
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Fayl uğurla yükləndi',
        'data' => [
            'filename' => $uniqueFileName,
            'original_name' => $fileName,
            'size' => $fileSize,
            'type' => $fileType,
            'url' => '/uploads/' . $uniqueFileName
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>