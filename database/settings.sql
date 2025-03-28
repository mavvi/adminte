CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Varsayılan ayarları ekle
INSERT INTO `settings` (`key`, `value`, `description`) VALUES
('site_title', 'AdminTE', 'Site başlığı'),
('site_description', 'Modern Admin Paneli', 'Site açıklaması'),
('site_email', 'admin@example.com', 'Site e-posta adresi'),
('items_per_page', '10', 'Sayfa başına gösterilecek öğe sayısı'),
('maintenance_mode', '0', 'Bakım modu (0: Kapalı, 1: Açık)'),
('registration_enabled', '1', 'Kayıt olma özelliği (0: Kapalı, 1: Açık)'),
('password_reset_enabled', '1', 'Şifre sıfırlama özelliği (0: Kapalı, 1: Açık)'),
('session_lifetime', '7200', 'Oturum süresi (saniye)'),
('max_login_attempts', '5', 'Maksimum giriş deneme sayısı'),
('lockout_time', '900', 'Hesap kilitleme süresi (saniye)'); 