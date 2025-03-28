<?php
session_start();
require_once '../../../config/database.php';
require_once '../../../models/Notification.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Oturum açmanız gerekiyor']);
    exit();
}

// POST verilerini al
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->notification_id)) {
    http_response_code(400);
    echo json_encode(['message' => 'Bildirim ID\'si gerekli']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$notification = new Notification($db);

// Bildirimi sil
if ($notification->delete($data->notification_id, $_SESSION['user_id'])) {
    echo json_encode(['message' => 'Bildirim silindi']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Bildirim silinirken bir hata oluştu']);
} 