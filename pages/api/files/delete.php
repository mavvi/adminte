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

// POST verilerini al
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->file_id)) {
    http_response_code(400);
    echo json_encode(['message' => 'Dosya ID\'si gerekli']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$file = new File($db);

// Dosyayı sil
if ($file->delete($data->file_id, $_SESSION['user_id'])) {
    echo json_encode(['message' => 'Dosya başarıyla silindi']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Dosya silinirken bir hata oluştu']);
} 