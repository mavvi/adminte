<?php
session_start();
require_once '../config/database.php';
require_once '../models/Notification.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$notification = new Notification($db);

// Sayfalama parametreleri
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Bildirimleri getir
$notifications = $notification->getUserNotifications($_SESSION['user_id'], $limit, $offset);
$unread_count = $notification->getUnreadCount($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bildirimler - AdminTE</title>
    
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
                <li>
                    <a href="backup.php">
                        <i class="fas fa-database"></i> Yedekleme
                    </a>
                </li>
                <li>
                    <a href="statistics.php">
                        <i class="fas fa-chart-bar"></i> İstatistikler
                    </a>
                </li>
                <li class="active">
                    <a href="notifications.php">
                        <i class="fas fa-bell"></i> Bildirimler
                        <?php if ($unread_count > 0): ?>
                            <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
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
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1>Bildirimler</h1>
                            <div>
                                <button class="btn btn-success me-2" onclick="markAllAsRead()">
                                    <i class="fas fa-check-double"></i> Tümünü Okundu İşaretle
                                </button>
                                <button class="btn btn-danger" onclick="deleteAllNotifications()">
                                    <i class="fas fa-trash"></i> Tümünü Sil
                                </button>
                            </div>
                        </div>

                        <!-- Bildirimler -->
                        <div class="card">
                            <div class="card-body">
                                <?php if (empty($notifications)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Henüz bildiriminiz bulunmuyor.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group">
                                        <?php foreach ($notifications as $notification): ?>
                                            <div class="list-group-item list-group-item-action <?php echo $notification['is_read'] ? '' : 'list-group-item-light'; ?>"
                                                 id="notification-<?php echo $notification['id']; ?>">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1">
                                                        <?php echo htmlspecialchars($notification['title']); ?>
                                                        <?php if (!$notification['is_read']): ?>
                                                            <span class="badge bg-primary">Yeni</span>
                                                        <?php endif; ?>
                                                    </h5>
                                                    <small class="text-muted">
                                                        <?php echo date('d.m.Y H:i', strtotime($notification['created_at'])); ?>
                                                    </small>
                                                </div>
                                                <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                <div class="d-flex justify-content-end">
                                                    <?php if (!$notification['is_read']): ?>
                                                        <button class="btn btn-sm btn-outline-primary me-2" 
                                                                onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                                            <i class="fas fa-check"></i> Okundu
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteNotification(<?php echo $notification['id']; ?>)">
                                                        <i class="fas fa-trash"></i> Sil
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Sayfalama -->
                                    <nav class="mt-4">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            <li class="page-item active">
                                                <span class="page-link"><?php echo $page; ?></span>
                                            </li>
                                            <?php if (count($notifications) == $limit): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
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
    <script>
        // Bildirimi okundu olarak işaretle
        function markAsRead(notificationId) {
            fetch('api/notifications/mark-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    notification_id: notificationId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    const notification = document.getElementById(`notification-${notificationId}`);
                    notification.classList.remove('list-group-item-light');
                    notification.querySelector('.badge').remove();
                    notification.querySelector('.btn-outline-primary').remove();
                    updateUnreadCount();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Tüm bildirimleri okundu olarak işaretle
        function markAllAsRead() {
            if (confirm('Tüm bildirimleri okundu olarak işaretlemek istediğinize emin misiniz?')) {
                fetch('api/notifications/mark-all-read.php')
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        // Bildirimi sil
        function deleteNotification(notificationId) {
            if (confirm('Bu bildirimi silmek istediğinize emin misiniz?')) {
                fetch('api/notifications/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        notification_id: notificationId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        document.getElementById(`notification-${notificationId}`).remove();
                        updateUnreadCount();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        // Tüm bildirimleri sil
        function deleteAllNotifications() {
            if (confirm('Tüm bildirimleri silmek istediğinize emin misiniz?')) {
                fetch('api/notifications/delete-all.php')
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        // Okunmamış bildirim sayısını güncelle
        function updateUnreadCount() {
            fetch('api/notifications/read.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.badge.bg-danger');
                if (data.unread_count > 0) {
                    if (!badge) {
                        const link = document.querySelector('a[href="notifications.php"]');
                        const newBadge = document.createElement('span');
                        newBadge.className = 'badge bg-danger';
                        newBadge.textContent = data.unread_count;
                        link.appendChild(newBadge);
                    } else {
                        badge.textContent = data.unread_count;
                    }
                } else if (badge) {
                    badge.remove();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html> 