<?php
session_start();
require_once '../config/database.php';
require_once '../models/User.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Sadece admin kullanıcılar erişebilir
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        // Her bir ayarı güncelle
        foreach ($_POST['settings'] as $key => $value) {
            $query = "UPDATE settings SET value = ? WHERE `key` = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$value, $key]);
        }

        $db->commit();
        $success = 'Ayarlar başarıyla güncellendi!';

        // Aktivite logunu kaydet
        $query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                 VALUES (?, 'settings_update', 'Sistem ayarları güncellendi', ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR']]);
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Ayarlar güncellenirken bir hata oluştu: ' . $e->getMessage();
    }
}

// Mevcut ayarları getir
$query = "SELECT * FROM settings ORDER BY `key`";
$stmt = $db->prepare($query);
$stmt->execute();
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ayarları key-value çifti olarak düzenle
$settings_array = [];
foreach ($settings as $setting) {
    $settings_array[$setting['key']] = $setting;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar - AdminTE</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <h3>AdminTE</h3>
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i> Ana Sayfa
                    </a>
                </li>
                <li>
                    <a href="users.php">
                        <i class="fas fa-users"></i> Kullanıcılar
                    </a>
                </li>
                <li>
                    <a href="profile.php">
                        <i class="fas fa-user"></i> Profil
                    </a>
                </li>
                <li>
                    <a href="activity-logs.php">
                        <i class="fas fa-history"></i> Aktivite Logları
                    </a>
                </li>
                <li class="active">
                    <a href="settings.php">
                        <i class="fas fa-cog"></i> Ayarlar
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Çıkış
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="ml-auto">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                                <li><a class="dropdown-item" href="logout.php">Çıkış</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <h1 class="mb-4">Sistem Ayarları</h1>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="">
                                    <!-- Genel Ayarlar -->
                                    <h5 class="mb-3">Genel Ayarlar</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="site_title" class="form-label">Site Başlığı</label>
                                                <input type="text" class="form-control" id="site_title" name="settings[site_title]" 
                                                       value="<?php echo htmlspecialchars($settings_array['site_title']['value']); ?>" required>
                                                <small class="text-muted"><?php echo htmlspecialchars($settings_array['site_title']['description']); ?></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="site_description" class="form-label">Site Açıklaması</label>
                                                <input type="text" class="form-control" id="site_description" name="settings[site_description]" 
                                                       value="<?php echo htmlspecialchars($settings_array['site_description']['value']); ?>" required>
                                                <small class="text-muted"><?php echo htmlspecialchars($settings_array['site_description']['description']); ?></small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- E-posta Ayarları -->
                                    <h5 class="mb-3">E-posta Ayarları</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="site_email" class="form-label">Site E-posta Adresi</label>
                                                <input type="email" class="form-control" id="site_email" name="settings[site_email]" 
                                                       value="<?php echo htmlspecialchars($settings_array['site_email']['value']); ?>" required>
                                                <small class="text-muted"><?php echo htmlspecialchars($settings_array['site_email']['description']); ?></small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sistem Ayarları -->
                                    <h5 class="mb-3">Sistem Ayarları</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="items_per_page" class="form-label">Sayfa Başına Öğe Sayısı</label>
                                                <input type="number" class="form-control" id="items_per_page" name="settings[items_per_page]" 
                                                       value="<?php echo htmlspecialchars($settings_array['items_per_page']['value']); ?>" required>
                                                <small class="text-muted"><?php echo htmlspecialchars($settings_array['items_per_page']['description']); ?></small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="session_lifetime" class="form-label">Oturum Süresi (Saniye)</label>
                                                <input type="number" class="form-control" id="session_lifetime" name="settings[session_lifetime]" 
                                                       value="<?php echo htmlspecialchars($settings_array['session_lifetime']['value']); ?>" required>
                                                <small class="text-muted"><?php echo htmlspecialchars($settings_array['session_lifetime']['description']); ?></small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="maintenance_mode" class="form-label">Bakım Modu</label>
                                                <select class="form-select" id="maintenance_mode" name="settings[maintenance_mode]">
                                                    <option value="0" <?php echo $settings_array['maintenance_mode']['value'] == '0' ? 'selected' : ''; ?>>Kapalı</option>
                                                    <option value="1" <?php echo $settings_array['maintenance_mode']['value'] == '1' ? 'selected' : ''; ?>>Açık</option>
                                                </select>
                                                <small class="text-muted"><?php echo htmlspecialchars($settings_array['maintenance_mode']['description']); ?></small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Güvenlik Ayarları -->
                                    <h5 class="mb-3">Güvenlik Ayarları</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="registration_enabled" class="form-label">Kayıt Olma Özelliği</label>
                                                <select class="form-select" id="registration_enabled" name="settings[registration_enabled]">
                                                    <option value="1" <?php echo $settings_array['registration_enabled']['value'] == '1' ? 'selected' : ''; ?>>Açık</option>
                                                    <option value="0" <?php echo $settings_array['registration_enabled']['value'] == '0' ? 'selected' : ''; ?>>Kapalı</option>
                                                </select>
                                                <small class="text-muted"><?php echo htmlspecialchars($settings_array['registration_enabled']['description']); ?></small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="password_reset_enabled" class="form-label">Şifre Sıfırlama Özelliği</label>
                                                <select class="form-select" id="password_reset_enabled" name="settings[password_reset_enabled]">
                                                    <option value="1" <?php echo $settings_array['password_reset_enabled']['value'] == '1' ? 'selected' : ''; ?>>Açık</option>
                                                    <option value="0" <?php echo $settings_array['password_reset_enabled']['value'] == '0' ? 'selected' : ''; ?>>Kapalı</option>
                                                </select>
                                                <small class="text-muted"><?php echo htmlspecialchars($settings_array['password_reset_enabled']['description']); ?></small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="max_login_attempts" class="form-label">Maksimum Giriş Deneme Sayısı</label>
                                                <input type="number" class="form-control" id="max_login_attempts" name="settings[max_login_attempts]" 
                                                       value="<?php echo htmlspecialchars($settings_array['max_login_attempts']['value']); ?>" required>
                                                <small class="text-muted"><?php echo htmlspecialchars($settings_array['max_login_attempts']['description']); ?></small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="lockout_time" class="form-label">Hesap Kilitleme Süresi (Saniye)</label>
                                                <input type="number" class="form-control" id="lockout_time" name="settings[lockout_time]" 
                                                       value="<?php echo htmlspecialchars($settings_array['lockout_time']['value']); ?>" required>
                                                <small class="text-muted"><?php echo htmlspecialchars($settings_array['lockout_time']['description']); ?></small>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Ayarları Kaydet
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html> 