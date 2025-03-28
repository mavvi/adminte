<?php
class Language {
    private $db;
    private $translations = [];
    private $current_language = 'tr';

    public function __construct($db) {
        $this->db = $db;
        $this->loadTranslations();
    }

    // Çevirileri yükle
    private function loadTranslations() {
        $query = "SELECT t.key_name, t.value 
                  FROM translations t 
                  JOIN languages l ON t.language_id = l.id 
                  WHERE l.code = :code AND l.is_active = TRUE";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':code', $this->current_language);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->translations[$row['key_name']] = $row['value'];
        }
    }

    // Aktif dilleri getir
    public function getActiveLanguages() {
        $query = "SELECT * FROM languages WHERE is_active = TRUE ORDER BY is_default DESC, name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Varsayılan dili getir
    public function getDefaultLanguage() {
        $query = "SELECT * FROM languages WHERE is_default = TRUE LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Dili değiştir
    public function setLanguage($code) {
        $query = "SELECT * FROM languages WHERE code = :code AND is_active = TRUE LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $this->current_language = $code;
            $this->translations = [];
            $this->loadTranslations();
            return true;
        }
        return false;
    }

    // Çeviri getir
    public function get($key, $params = []) {
        $value = isset($this->translations[$key]) ? $this->translations[$key] : $key;
        
        if (!empty($params)) {
            foreach ($params as $param => $replacement) {
                $value = str_replace(':' . $param, $replacement, $value);
            }
        }
        
        return $value;
    }

    // Çeviri ekle/güncelle
    public function setTranslation($language_id, $key, $value) {
        $query = "INSERT INTO translations (language_id, key_name, value) 
                  VALUES (:language_id, :key, :value) 
                  ON DUPLICATE KEY UPDATE value = :value";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':language_id', $language_id);
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':value', $value);
        
        return $stmt->execute();
    }

    // Dil ekle
    public function addLanguage($code, $name, $is_default = false) {
        $query = "INSERT INTO languages (code, name, is_default) VALUES (:code, :name, :is_default)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':is_default', $is_default);
        
        return $stmt->execute();
    }

    // Dil güncelle
    public function updateLanguage($id, $name, $is_active, $is_default = false) {
        $query = "UPDATE languages SET name = :name, is_active = :is_active, is_default = :is_default 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->bindParam(':is_default', $is_default);
        
        return $stmt->execute();
    }

    // Dil sil
    public function deleteLanguage($id) {
        $query = "DELETE FROM languages WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Çeviri anahtarlarını getir
    public function getTranslationKeys() {
        $query = "SELECT DISTINCT key_name FROM translations ORDER BY key_name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Dil çevirilerini getir
    public function getLanguageTranslations($language_id) {
        $query = "SELECT key_name, value FROM translations WHERE language_id = :language_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':language_id', $language_id);
        $stmt->execute();
        
        $translations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $translations[$row['key_name']] = $row['value'];
        }
        
        return $translations;
    }
} 