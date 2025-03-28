<?php
class Mailer {
    private $db;
    private $from_email;
    private $from_name;

    public function __construct($db) {
        $this->db = $db;
        
        // Varsayılan e-posta ayarlarını al
        $query = "SELECT value FROM settings WHERE `key` = 'mail_from_email'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $this->from_email = $stmt->fetchColumn() ?: 'noreply@admintemplate.com';
        
        $query = "SELECT value FROM settings WHERE `key` = 'mail_from_name'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $this->from_name = $stmt->fetchColumn() ?: 'AdminTE';
    }

    public function send($to, $subject, $template, $data = []) {
        // E-posta şablonunu yükle
        ob_start();
        extract($data);
        include "../templates/email/{$template}.php";
        $message = ob_get_clean();

        // E-posta başlıkları
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->from_name} <{$this->from_email}>\r\n";
        $headers .= "Reply-To: {$this->from_email}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // E-postayı gönder
        return mail($to, $subject, $message, $headers);
    }

    public function sendPasswordReset($user) {
        $data = [
            'user_name' => $user->full_name,
            'reset_link' => "http://" . $_SERVER['HTTP_HOST'] . "/pages/reset-password.php?token=" . $user->reset_token
        ];

        return $this->send(
            $user->email,
            "Şifre Sıfırlama İsteği",
            "reset-password",
            $data
        );
    }
} 