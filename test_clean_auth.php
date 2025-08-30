<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
ob_clean();
echo json_encode(['status' => 'success', 'message' => 'Clean JSON response']);
?>