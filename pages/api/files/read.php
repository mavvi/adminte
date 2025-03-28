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

$database = new Database();
$db = $database->getConnection();

$file = new File($db);

// Sayfalama parametreleri
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Dosyaları getir
$files = $file->getUserFiles($_SESSION['user_id'], $limit, $offset);

// Dosya boyutlarını formatla
foreach ($files as &$f) {
    $f['formatted_size'] = $file->formatSize($f['size']);
}

echo json_encode([
    'files' => $files,
    'page' => $page,
    'limit' => $limit
]); 