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

// Gerekli alanları kontrol et
if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['full_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tüm alanları doldurun!']);
    exit();
}

// Veritabanı bağlantısı
$database = new Database();
$db = $database->getConnection();

// Kullanıcı nesnesi
$user = new User($db);

// Kullanıcı adı ve e-posta kontrolü
$check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
$check_stmt = $db->prepare($check_query);
$check_stmt->execute([$data['username'], $data['email']]);

if ($check_stmt->rowCount() > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor!']);
    exit();
}

// Kullanıcı verilerini ayarla
$user->username = $data['username'];
$user->email = $data['email'];
$user->password = $data['password'];
$user->full_name = $data['full_name'];
$user->role = $data['role'] ?? 'user';
$user->status = $data['status'] ?? 'active';

// Kullanıcıyı oluştur
if ($user->create()) {
    echo json_encode(['success' => true, 'message' => 'Kullanıcı başarıyla oluşturuldu.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Kullanıcı oluşturulurken bir hata oluştu!']);
} 