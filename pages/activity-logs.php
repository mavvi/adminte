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

// Sayfalama için parametreler
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Filtreleme parametreleri
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$action = isset($_GET['action']) ? $_GET['action'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Toplam kayıt sayısını al
$count_query = "SELECT COUNT(*) as total FROM activity_logs WHERE 1=1";
$params = [];

if ($user_id) {
    $count_query .= " AND user_id = ?";
    $params[] = $user_id;
}
if ($action) {
    $count_query .= " AND action = ?";
    $params[] = $action;
}
if ($start_date) {
    $count_query .= " AND created_at >= ?";
    $params[] = $start_date;
}
if ($end_date) {
    $count_query .= " AND created_at <= ?";
    $params[] = $end_date;
}

$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $limit);

// Aktivite loglarını getir
$query = "SELECT al.*, u.username 
          FROM activity_logs al 
          LEFT JOIN users u ON al.user_id = u.id 
          WHERE 1=1";

if ($user_id) {
    $query .= " AND al.user_id = ?";
    $params[] = $user_id;
}
if ($action) {
    $query .= " AND al.action = ?";
    $params[] = $action;
}
if ($start_date) {
    $query .= " AND al.created_at >= ?";
    $params[] = $start_date;
}
if ($end_date) {
    $query .= " AND al.created_at <= ?";
    $params[] = $end_date;
}

$query .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $db->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kullanıcıları getir (filtreleme için)
$user_query = "SELECT id, username FROM users ORDER BY username";
$user_stmt = $db->prepare($user_query);
$user_stmt->execute();
$users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

// Benzersiz aksiyonları getir (filtreleme için)
$action_query = "SELECT DISTINCT action FROM activity_logs ORDER BY action";
$action_stmt = $db->prepare($action_query);
$action_stmt->execute();
$actions = $action_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivite Logları - AdminTE</title>
    
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
                <li class="active">
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
                        <h1 class="mb-4">Aktivite Logları</h1>

                        <!-- Filtreler -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-3">
                                        <label for="user_id" class="form-label">Kullanıcı</label>
                                        <select class="form-select" id="user_id" name="user_id">
                                            <option value="">Tümü</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?php echo $user['id']; ?>" <?php echo $user_id == $user['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($user['username']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="action" class="form-label">Aksiyon</label>
                                        <select class="form-select" id="action" name="action">
                                            <option value="">Tümü</option>
                                            <?php foreach ($actions as $action_type): ?>
                                                <option value="<?php echo $action_type; ?>" <?php echo $action == $action_type ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($action_type); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="start_date" class="form-label">Başlangıç Tarihi</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="end_date" class="form-label">Bitiş Tarihi</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filtrele
                                        </button>
                                        <a href="activity-logs.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Filtreleri Temizle
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Log Tablosu -->
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Kullanıcı</th>
                                                <th>Aksiyon</th>
                                                <th>Açıklama</th>
                                                <th>IP Adresi</th>
                                                <th>Tarih</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($logs as $log): ?>
                                                <tr>
                                                    <td><?php echo $log['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($log['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['description']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                                    <td><?php echo date('d.m.Y H:i:s', strtotime($log['created_at'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Sayfalama -->
                                <?php if ($total_pages > 1): ?>
                                    <nav aria-label="Sayfalama" class="mt-4">
                                        <ul class="pagination justify-content-center">
                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>&user_id=<?php echo $user_id; ?>&action=<?php echo $action; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
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