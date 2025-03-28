<?php
class Backup {
    private $db;
    private $backup_dir;
    private $tables = [];

    public function __construct($db) {
        $this->db = $db;
        $this->backup_dir = dirname(__DIR__) . '/backups';
        
        // Yedekleme dizinini oluştur
        if (!file_exists($this->backup_dir)) {
            mkdir($this->backup_dir, 0755, true);
        }
    }

    public function create() {
        try {
            // Tüm tabloları al
            $query = "SHOW TABLES";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $this->tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Yedek dosya adı
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $this->backup_dir . '/' . $filename;

            // Yedek dosyasını oluştur
            $handle = fopen($filepath, 'w');

            // Her tablo için
            foreach ($this->tables as $table) {
                // Tablo yapısını al
                $query = "SHOW CREATE TABLE `{$table}`";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_NUM);
                
                // Tablo oluşturma SQL'ini yaz
                fwrite($handle, "\n\n" . $row[1] . ";\n\n");

                // Tablo verilerini al
                $query = "SELECT * FROM `{$table}`";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Her satır için
                foreach ($rows as $row) {
                    // INSERT SQL'ini oluştur
                    $values = array_map(function($value) {
                        if ($value === null) {
                            return 'NULL';
                        }
                        return "'" . addslashes($value) . "'";
                    }, $row);

                    $sql = "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                    fwrite($handle, $sql);
                }
            }

            fclose($handle);
            return $filename;
        } catch (Exception $e) {
            throw new Exception('Yedekleme sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function restore($filename) {
        try {
            $filepath = $this->backup_dir . '/' . $filename;
            
            if (!file_exists($filepath)) {
                throw new Exception('Yedek dosyası bulunamadı.');
            }

            // SQL dosyasını oku
            $sql = file_get_contents($filepath);
            
            // SQL komutlarını ayır
            $queries = array_filter(array_map('trim', explode(';', $sql)));

            // Transaction başlat
            $this->db->beginTransaction();

            try {
                // Her SQL komutunu çalıştır
                foreach ($queries as $query) {
                    if (!empty($query)) {
                        $this->db->exec($query);
                    }
                }

                $this->db->commit();
                return true;
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            throw new Exception('Geri yükleme sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function listBackups() {
        $files = glob($this->backup_dir . '/backup_*.sql');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => $this->formatSize(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }

        // Tarihe göre sırala (en yeniden en eskiye)
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $backups;
    }

    public function delete($filename) {
        $filepath = $this->backup_dir . '/' . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }

    private function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 