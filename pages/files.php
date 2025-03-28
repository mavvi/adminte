<?php
session_start();
require_once '../config/database.php';
require_once '../models/File.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$file = new File($db);

// Sayfalama parametreleri
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Dosyaları getir
$files = $file->getUserFiles($_SESSION['user_id'], $limit, $offset);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Yönetimi - AdminTE</title>
    
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
                <li>
                    <a href="notifications.php">
                        <i class="fas fa-bell"></i> Bildirimler
                    </a>
                </li>
                <li class="active">
                    <a href="files.php">
                        <i class="fas fa-file"></i> Dosyalar
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
                            <h1>Dosya Yönetimi</h1>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-upload"></i> Dosya Yükle
                            </button>
                        </div>

                        <!-- Dosya Listesi -->
                        <div class="card">
                            <div class="card-body">
                                <?php if (empty($files)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Henüz dosya yüklenmemiş.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Dosya Adı</th>
                                                    <th>Tip</th>
                                                    <th>Boyut</th>
                                                    <th>Yüklenme Tarihi</th>
                                                    <th>Durum</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($files as $f): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($f['original_name']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $f['type'] === 'image' ? 'success' : ($f['type'] === 'document' ? 'primary' : ($f['type'] === 'archive' ? 'warning' : ($f['type'] === 'video' ? 'danger' : 'info'))); ?>">
                                                                <?php echo ucfirst($f['type']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $file->formatSize($f['size']); ?></td>
                                                        <td><?php echo date('d.m.Y H:i', strtotime($f['created_at'])); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $f['is_public'] ? 'success' : 'secondary'; ?>">
                                                                <?php echo $f['is_public'] ? 'Herkese Açık' : 'Özel'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="api/files/download.php?id=<?php echo $f['id']; ?>" 
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-outline-info"
                                                                        onclick="editFile(<?php echo $f['id']; ?>, '<?php echo htmlspecialchars($f['description']); ?>', <?php echo $f['is_public'] ? 'true' : 'false'; ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-outline-danger"
                                                                        onclick="deleteFile(<?php echo $f['id']; ?>)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
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
                                            <?php if (count($files) == $limit): ?>
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

    <!-- Dosya Yükleme Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dosya Yükle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm">
                        <div class="mb-3">
                            <label for="file" class="form-label">Dosya</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_public" name="is_public">
                            <label class="form-check-label" for="is_public">Herkese Açık</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="uploadFile()">Yükle</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosya Düzenleme Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dosya Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="edit_file_id">
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_public" name="is_public">
                            <label class="form-check-label" for="edit_is_public">Herkese Açık</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="updateFile()">Güncelle</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Dosya yükle
        function uploadFile() {
            const formData = new FormData(document.getElementById('uploadForm'));
            
            fetch('api/files/upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    location.reload();
                } else {
                    alert('Dosya yüklenirken bir hata oluştu');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Dosya yüklenirken bir hata oluştu');
            });
        }

        // Dosya düzenleme modalını aç
        function editFile(fileId, description, isPublic) {
            document.getElementById('edit_file_id').value = fileId;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_is_public').checked = isPublic;
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        // Dosya güncelle
        function updateFile() {
            const fileId = document.getElementById('edit_file_id').value;
            const description = document.getElementById('edit_description').value;
            const isPublic = document.getElementById('edit_is_public').checked;
            
            fetch('api/files/update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    file_id: fileId,
                    description: description,
                    is_public: isPublic
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    location.reload();
                } else {
                    alert('Dosya güncellenirken bir hata oluştu');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Dosya güncellenirken bir hata oluştu');
            });
        }

        // Dosya sil
        function deleteFile(fileId) {
            if (confirm('Bu dosyayı silmek istediğinize emin misiniz?')) {
                fetch('api/files/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        file_id: fileId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        location.reload();
                    } else {
                        alert('Dosya silinirken bir hata oluştu');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Dosya silinirken bir hata oluştu');
                });
            }
        }
    </script>
</body>
</html> 