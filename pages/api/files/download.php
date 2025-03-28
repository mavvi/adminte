<?php
session_start();
require_once '../../../config/database.php';
require_once '../../../models/File.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Oturum açmanız gerekiyor']);
    exit();
}

// Dosya ID kontrolü
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Dosya ID\'si gerekli']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$file = new File($db);

// Dosya bilgilerini getir
$file_info = $file->getFile($_GET['id'], $_SESSION['user_id']);

if (!$file_info) {
    http_response_code(404);
    echo json_encode(['message' => 'Dosya bulunamadı']);
    exit();
}

// Dosya yolu kontrolü
if (!file_exists($file_info['path'])) {
    http_response_code(404);
    echo json_encode(['message' => 'Dosya bulunamadı']);
    exit();
}

// Dosyayı indir
header('Content-Type: ' . $file_info['mime_type']);
header('Content-Disposition: attachment; filename="' . $file_info['original_name'] . '"');
header('Content-Length: ' . $file_info['size']);
header('Cache-Control: no-cache');
readfile($file_info['path']); 