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

// POST verilerini al
$data = $_POST;

// ID kontrolü
if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Kullanıcı ID\'si gerekli!']);
    exit();
}

// Veritabanı bağlantısı
$database = new Database();
$db = $database->getConnection();

// Kullanıcı nesnesi
$user = new User($db);
$user->id = $data['id'];

// Kullanıcıyı oku
if (!$user->readOne()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı!']);
    exit();
}

// Kullanıcı adı ve e-posta kontrolü
$check_query = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
$check_stmt = $db->prepare($check_query);
$check_stmt->execute([$data['username'], $data['email'], $data['id']]);

if ($check_stmt->rowCount() > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor!']);
    exit();
}

// Kullanıcı verilerini ayarla
$user->username = $data['username'];
$user->email = $data['email'];
$user->full_name = $data['full_name'];
$user->role = $data['role'];
$user->status = $data['status'];

// Şifre değişikliği varsa güncelle
if (!empty($data['password'])) {
    $user->password = $data['password'];
    if (!$user->updatePassword()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Şifre güncellenirken bir hata oluştu!']);
        exit();
    }
}

// Kullanıcıyı güncelle
if ($user->update()) {
    echo json_encode(['success' => true, 'message' => 'Kullanıcı başarıyla güncellendi.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Kullanıcı güncellenirken bir hata oluştu!']);
} 