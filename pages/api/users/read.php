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

// Kullanıcıyı oku
if ($user->readOne()) {
    // Hassas bilgileri kaldır
    unset($user->password);
    
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'role' => $user->role,
            'status' => $user->status,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ]
    ]);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı!']);
} 