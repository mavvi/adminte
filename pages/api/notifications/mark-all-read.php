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

$database = new Database();
$db = $database->getConnection();

$notification = new Notification($db);

// Tüm bildirimleri okundu olarak işaretle
if ($notification->markAllAsRead($_SESSION['user_id'])) {
    echo json_encode(['message' => 'Tüm bildirimler okundu olarak işaretlendi']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Bildirimler işaretlenirken bir hata oluştu']);
} 