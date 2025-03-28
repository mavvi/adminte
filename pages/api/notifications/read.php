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

// Sayfalama parametreleri
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Bildirimleri getir
$notifications = $notification->getUserNotifications($_SESSION['user_id'], $limit, $offset);
$unread_count = $notification->getUnreadCount($_SESSION['user_id']);

echo json_encode([
    'notifications' => $notifications,
    'unread_count' => $unread_count,
    'page' => $page,
    'limit' => $limit
]); 