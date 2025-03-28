<?php
session_start();
require_once '../config/database.php';
require_once '../helpers/Backup.php';

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

$backup = new Backup($db);
$success = false;
$error = '';

// Yedek oluştur
if (isset($_POST['create_backup'])) {
    try {
        $filename = $backup->create();
        $success = true;
        $message = 'Yedek başarıyla oluşturuldu: ' . $filename;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Yedek geri yükle
if (isset($_POST['restore_backup'])) {
    try {
        if ($backup->restore($_POST['filename'])) {
            $success = true;
            $message = 'Yedek başarıyla geri yüklendi.';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Yedek sil
if (isset($_POST['delete_backup'])) {
    if ($backup->delete($_POST['filename'])) {
        $success = true;
        $message = 'Yedek başarıyla silindi.';
    } else {
        $error = 'Yedek silinirken bir hata oluştu.';
    }
}

// Yedekleri listele
$backups = $backup->listBackups();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yedekleme - AdminTE</title>
    
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
                <li>
                    <a href="settings.php">
                        <i class="fas fa-cog"></i> Ayarlar
                    </a>
                </li>
                <li class="active">
                    <a href="backup.php">
                        <i class="fas fa-database"></i> Yedekleme
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
                        <h1 class="mb-4">Veritabanı Yedekleme</h1>

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

                        <!-- Yedek Oluştur -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Yeni Yedek Oluştur</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <button type="submit" name="create_backup" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Yedek Oluştur
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Yedek Listesi -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Yedekler</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Dosya Adı</th>
                                                <th>Boyut</th>
                                                <th>Tarih</th>
                                                <th>İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($backups as $backup): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($backup['filename']); ?></td>
                                                    <td><?php echo $backup['size']; ?></td>
                                                    <td><?php echo $backup['date']; ?></td>
                                                    <td>
                                                        <form method="POST" action="" class="d-inline">
                                                            <input type="hidden" name="filename" value="<?php echo $backup['filename']; ?>">
                                                            <button type="submit" name="restore_backup" class="btn btn-warning btn-sm" onclick="return confirm('Bu yedeği geri yüklemek istediğinizden emin misiniz?')">
                                                                <i class="fas fa-undo"></i> Geri Yükle
                                                            </button>
                                                            <button type="submit" name="delete_backup" class="btn btn-danger btn-sm" onclick="return confirm('Bu yedeği silmek istediğinizden emin misiniz?')">
                                                                <i class="fas fa-trash"></i> Sil
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
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