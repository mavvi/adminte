<?php
session_start();
require_once '../config/database.php';
require_once '../helpers/Statistics.php';

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

$stats = new Statistics($db);
$user_stats = $stats->getUserStats();
$activity_stats = $stats->getActivityStats();
$system_stats = $stats->getSystemStats();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İstatistikler - AdminTE</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li>
                    <a href="backup.php">
                        <i class="fas fa-database"></i> Yedekleme
                    </a>
                </li>
                <li class="active">
                    <a href="statistics.php">
                        <i class="fas fa-chart-bar"></i> İstatistikler
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
                        <h1 class="mb-4">İstatistikler</h1>

                        <!-- Kullanıcı İstatistikleri -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Toplam Kullanıcı</h5>
                                        <h2 class="mb-0"><?php echo $user_stats['total_users']; ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Aktif Kullanıcı</h5>
                                        <h2 class="mb-0"><?php echo $user_stats['active_users']; ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Admin Kullanıcı</h5>
                                        <h2 class="mb-0"><?php echo $user_stats['admin_users']; ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Son 7 Gün</h5>
                                        <h2 class="mb-0"><?php echo $user_stats['new_users_7days']; ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Aktivite Grafiği -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Son 7 Günlük Aktivite</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="activityChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">En Çok Yapılan Aktiviteler</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Aktivite</th>
                                                        <th>Sayı</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($activity_stats['top_activities'] as $activity): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                                            <td><?php echo $activity['count']; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sistem Durumu -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Sistem Bilgileri</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table">
                                            <tr>
                                                <th>PHP Sürümü</th>
                                                <td><?php echo $system_stats['php_version']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Sunucu Yazılımı</th>
                                                <td><?php echo $system_stats['server_software']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Veritabanı Boyutu</th>
                                                <td><?php echo $system_stats['database_size']; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Kaynak Kullanımı</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table">
                                            <tr>
                                                <th>Disk Kullanımı</th>
                                                <td><?php echo $system_stats['disk_free']; ?> / <?php echo $system_stats['disk_total']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Bellek Kullanımı</th>
                                                <td><?php echo $system_stats['memory_usage']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>En Yüksek Bellek Kullanımı</th>
                                                <td><?php echo $system_stats['memory_peak']; ?></td>
                                            </tr>
                                        </table>
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
    <script>
        // Aktivite grafiği
        const ctx = document.getElementById('activityChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($activity_stats['activity_7days'], 'date')); ?>,
                datasets: [{
                    label: 'Aktivite Sayısı',
                    data: <?php echo json_encode(array_column($activity_stats['activity_7days'], 'count')); ?>,
                    borderColor: '#4e73df',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html> 