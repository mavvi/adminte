<?php
class File {
    private $db;
    private $upload_dir = '../uploads/';
    private $allowed_types = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
        'archive' => ['zip', 'rar', '7z'],
        'video' => ['mp4', 'avi', 'mov', 'wmv'],
        'audio' => ['mp3', 'wav', 'ogg']
    ];

    public function __construct($db) {
        $this->db = $db;
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    // Dosya yükle
    public function upload($file, $user_id, $description = '', $is_public = false) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException('Geçersiz dosya parametresi.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('Dosya yüklenmedi.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Dosya boyutu çok büyük.');
            default:
                throw new RuntimeException('Bilinmeyen bir hata oluştu.');
        }

        if ($file['size'] > 100 * 1024 * 1024) { // 100MB limit
            throw new RuntimeException('Dosya boyutu 100MB\'dan büyük olamaz.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Dosya tipini kontrol et
        $type = $this->getFileType($extension);
        if (!$type) {
            throw new RuntimeException('İzin verilmeyen dosya tipi.');
        }

        // Güvenli dosya adı oluştur
        $name = bin2hex(random_bytes(16)) . '.' . $extension;
        $path = $this->upload_dir . $name;

        // Dosyayı taşı
        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new RuntimeException('Dosya yüklenirken bir hata oluştu.');
        }

        // Veritabanına kaydet
        $query = "INSERT INTO files (user_id, name, original_name, path, type, size, mime_type, description, is_public) 
                  VALUES (:user_id, :name, :original_name, :path, :type, :size, :mime_type, :description, :is_public)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':original_name', $file['name']);
        $stmt->bindParam(':path', $path);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':size', $file['size']);
        $stmt->bindParam(':mime_type', $mime_type);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':is_public', $is_public);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        // Hata durumunda dosyayı sil
        unlink($path);
        throw new RuntimeException('Dosya veritabanına kaydedilemedi.');
    }

    // Dosya bilgilerini getir
    public function getFile($file_id, $user_id) {
        $query = "SELECT * FROM files WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':id', $file_id);
        $stmt->bindParam(':user_id', $user_id);
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Kullanıcının dosyalarını getir
    public function getUserFiles($user_id, $limit = 10, $offset = 0) {
        $query = "SELECT * FROM files WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Dosya sil
    public function delete($file_id, $user_id) {
        $file = $this->getFile($file_id, $user_id);
        if (!$file) {
            return false;
        }

        // Dosyayı fiziksel olarak sil
        if (file_exists($file['path'])) {
            unlink($file['path']);
        }

        // Veritabanından sil
        $query = "DELETE FROM files WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':id', $file_id);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }

    // Dosya güncelle
    public function update($file_id, $user_id, $description = null, $is_public = null) {
        $query = "UPDATE files SET description = :description, is_public = :is_public 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':id', $file_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':is_public', $is_public);
        
        return $stmt->execute();
    }

    // Dosya tipini belirle
    private function getFileType($extension) {
        foreach ($this->allowed_types as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }
        return false;
    }

    // Dosya boyutunu formatla
    public function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 