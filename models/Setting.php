<?php
class Setting {
    private $conn;
    private $table_name = "settings";

    public $id;
    public $key;
    public $value;
    public $description;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Tüm ayarları getir
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY `key`";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Tek bir ayarı getir
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE `key` = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->key);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->value = $row['value'];
            $this->description = $row['description'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Ayar güncelle
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET value = :value 
                 WHERE `key` = :key";

        $stmt = $this->conn->prepare($query);

        // Değerleri temizle
        $this->key = htmlspecialchars(strip_tags($this->key));
        $this->value = htmlspecialchars(strip_tags($this->value));

        // Parametreleri bağla
        $stmt->bindParam(":key", $this->key);
        $stmt->bindParam(":value", $this->value);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Birden fazla ayarı güncelle
    public function updateMultiple($settings) {
        try {
            $this->conn->beginTransaction();

            foreach ($settings as $key => $value) {
                $this->key = $key;
                $this->value = $value;
                $this->update();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Ayar ekle
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (`key`, value, description) 
                 VALUES (:key, :value, :description)";

        $stmt = $this->conn->prepare($query);

        // Değerleri temizle
        $this->key = htmlspecialchars(strip_tags($this->key));
        $this->value = htmlspecialchars(strip_tags($this->value));
        $this->description = htmlspecialchars(strip_tags($this->description));

        // Parametreleri bağla
        $stmt->bindParam(":key", $this->key);
        $stmt->bindParam(":value", $this->value);
        $stmt->bindParam(":description", $this->description);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Ayar sil
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE `key` = ?";
        $stmt = $this->conn->prepare($query);
        $this->key = htmlspecialchars(strip_tags($this->key));
        $stmt->bindParam(1, $this->key);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
} 