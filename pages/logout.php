<?php
session_start();
require_once '../config/database.php';

// Oturum kontrolü
if (isset($_SESSION['user_id'])) {
    // Veritabanı bağlantısı
    $database = new Database();
    $db = $database->getConnection();

    // Aktivite logunu kaydet
    $query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
             VALUES (?, 'logout', 'Kullanıcı çıkışı yapıldı', ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR']]);
}

// Oturumu sonlandır
session_destroy();

// Giriş sayfasına yönlendir
header('Location: login.php');
exit(); 