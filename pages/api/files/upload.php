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

// Dosya kontrolü
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Dosya yüklenmedi']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$file = new File($db);

try {
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $is_public = isset($_POST['is_public']) ? (bool)$_POST['is_public'] : false;
    
    $file_id = $file->upload($_FILES['file'], $_SESSION['user_id'], $description, $is_public);
    
    echo json_encode([
        'message' => 'Dosya başarıyla yüklendi',
        'file_id' => $file_id
    ]);
} catch (RuntimeException $e) {
    http_response_code(400);
    echo json_encode(['message' => $e->getMessage()]);
} 