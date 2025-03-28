<?php
class Statistics {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Kullanıcı istatistikleri
    public function getUserStats() {
        $stats = [];

        // Toplam kullanıcı sayısı
        $query = "SELECT COUNT(*) FROM users";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_users'] = $stmt->fetchColumn();

        // Aktif kullanıcı sayısı
        $query = "SELECT COUNT(*) FROM users WHERE status = 'active'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['active_users'] = $stmt->fetchColumn();

        // Admin kullanıcı sayısı
        $query = "SELECT COUNT(*) FROM users WHERE role = 'admin'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['admin_users'] = $stmt->fetchColumn();

        // Son 7 günde kayıt olan kullanıcı sayısı
        $query = "SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['new_users_7days'] = $stmt->fetchColumn();

        return $stats;
    }

    // Aktivite istatistikleri
    public function getActivityStats() {
        $stats = [];

        // Son 7 günlük aktivite sayısı
        $query = "SELECT DATE(created_at) as date, COUNT(*) as count 
                 FROM activity_logs 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY date";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['activity_7days'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // En çok yapılan aktiviteler
        $query = "SELECT action, COUNT(*) as count 
                 FROM activity_logs 
                 GROUP BY action 
                 ORDER BY count DESC 
                 LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['top_activities'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // En aktif kullanıcılar
        $query = "SELECT u.username, COUNT(*) as count 
                 FROM activity_logs al 
                 JOIN users u ON al.user_id = u.id 
                 GROUP BY al.user_id 
                 ORDER BY count DESC 
                 LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['top_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    // Sistem durumu
    public function getSystemStats() {
        $stats = [];

        // PHP sürümü
        $stats['php_version'] = PHP_VERSION;

        // Sunucu yazılımı
        $stats['server_software'] = $_SERVER['SERVER_SOFTWARE'];

        // Veritabanı boyutu
        $query = "SELECT SUM(data_length + index_length) as size 
                 FROM information_schema.tables 
                 WHERE table_schema = DATABASE()";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['database_size'] = $this->formatSize($stmt->fetchColumn());

        // Disk kullanımı
        $stats['disk_free'] = $this->formatSize(disk_free_space('/'));
        $stats['disk_total'] = $this->formatSize(disk_total_space('/'));

        // Bellek kullanımı
        $stats['memory_usage'] = $this->formatSize(memory_get_usage(true));
        $stats['memory_peak'] = $this->formatSize(memory_get_peak_usage(true));

        return $stats;
    }

    // Dosya boyutu formatla
    private function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 