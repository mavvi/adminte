<?php
session_start();
require_once '../../../config/database.php';
require_once '../../../models/User.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim!']);
    exit();
}

// ID kontrolü
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Kullanıcı ID\'si gerekli!']);
    exit();
}

// Veritabanı bağlantısı
$database = new Database();
$db = $database->getConnection();

// Kullanıcı nesnesi
$user = new User($db);
$user->id = $_GET['id'];

// Kendini silmeye çalışıyor mu kontrol et
if ($user->id == $_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Kendi hesabınızı silemezsiniz!']);
    exit();
}

// Kullanıcıyı sil
if ($user->delete()) {
    echo json_encode(['success' => true, 'message' => 'Kullanıcı başarıyla silindi.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Kullanıcı silinirken bir hata oluştu!']);
} 