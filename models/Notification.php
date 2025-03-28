<?php
class Notification {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Yeni bildirim oluştur
    public function create($user_id, $title, $message, $type = 'info') {
        $query = "INSERT INTO notifications (user_id, title, message, type) VALUES (:user_id, :title, :message, :type)";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':type', $type);

        return $stmt->execute();
    }

    // Kullanıcının bildirimlerini getir
    public function getUserNotifications($user_id, $limit = 10, $offset = 0) {
        $query = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Okunmamış bildirim sayısını getir
    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = FALSE";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    // Bildirimi okundu olarak işaretle
    public function markAsRead($notification_id, $user_id) {
        $query = "UPDATE notifications SET is_read = TRUE WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':id', $notification_id);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }

    // Tüm bildirimleri okundu olarak işaretle
    public function markAllAsRead($user_id) {
        $query = "UPDATE notifications SET is_read = TRUE WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }

    // Bildirimi sil
    public function delete($notification_id, $user_id) {
        $query = "DELETE FROM notifications WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':id', $notification_id);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }

    // Tüm bildirimleri sil
    public function deleteAll($user_id) {
        $query = "DELETE FROM notifications WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }
} 