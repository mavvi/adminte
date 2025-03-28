<?php
session_start();
require_once '../config/database.php';
require_once '../models/User.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->id = $_SESSION['user_id'];
$user_data = $user->readOne();

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = false;
    $error = '';

    // Profil güncelleme
    if (isset($_POST['update_profile'])) {
        $user->full_name = $_POST['full_name'];
        $user->email = $_POST['email'];
        
        if ($user->update()) {
            $success = true;
            $message = 'Profil başarıyla güncellendi.';
            $user_data = $user->readOne(); // Güncel verileri al
        } else {
            $error = 'Profil güncellenirken bir hata oluştu.';
        }
    }
    
    // Şifre değiştirme
    if (isset($_POST['change_password'])) {
        if (password_verify($_POST['current_password'], $user_data['password'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                $user->password = $_POST['new_password'];
                if ($user->update()) {
                    $success = true;
                    $message = 'Şifreniz başarıyla güncellendi.';
                } else {
                    $error = 'Şifre güncellenirken bir hata oluştu.';
                }
            } else {
                $error = 'Yeni şifreler eşleşmiyor.';
            }
        } else {
            $error = 'Mevcut şifre yanlış.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - AdminTE</title>
    
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
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li>
                    <a href="users.php">
                        <i class="fas fa-users"></i> Kullanıcılar
                    </a>
                </li>
                <?php endif; ?>
                <li class="active">
                    <a href="profile.php">
                        <i class="fas fa-user"></i> Profil
                    </a>
                </li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li>
                    <a href="activity-logs.php">
                        <i class="fas fa-history"></i> Aktivite Logları
                    </a>
                </li>
                <li>
                    <a href="settings.php">
                        <i class="fas fa-cog"></i> Ayarlar
                    </a>
                </li>
                <?php endif; ?>
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
                        <h1 class="mb-4">Profil</h1>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <!-- Profil Bilgileri -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Profil Bilgileri</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <div class="mb-3">
                                                <label for="username" class="form-label">Kullanıcı Adı</label>
                                                <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label for="full_name" class="form-label">Ad Soyad</label>
                                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">E-posta</label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="role" class="form-label">Rol</label>
                                                <input type="text" class="form-control" id="role" value="<?php echo htmlspecialchars($user_data['role']); ?>" readonly>
                                            </div>
                                            <button type="submit" name="update_profile" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Profili Güncelle
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Şifre Değiştirme -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Şifre Değiştir</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label">Mevcut Şifre</label>
                                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">Yeni Şifre</label>
                                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Yeni Şifre (Tekrar)</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            </div>
                                            <button type="submit" name="change_password" class="btn btn-primary">
                                                <i class="fas fa-key"></i> Şifreyi Değiştir
                                            </button>
                                        </form>
                                    </div>
                                </div>
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