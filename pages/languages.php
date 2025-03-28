<?php
session_start();
require_once '../config/Database.php';
require_once '../models/Language.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Rol kontrolü
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();
$language = new Language($db);

// Dil ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            if (isset($_POST['code']) && isset($_POST['name'])) {
                $is_default = isset($_POST['is_default']) ? 1 : 0;
                if ($language->addLanguage($_POST['code'], $_POST['name'], $is_default)) {
                    $_SESSION['success'] = 'Dil başarıyla eklendi.';
                } else {
                    $_SESSION['error'] = 'Dil eklenirken bir hata oluştu.';
                }
            }
            break;

        case 'update':
            if (isset($_POST['id']) && isset($_POST['name'])) {
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $is_default = isset($_POST['is_default']) ? 1 : 0;
                if ($language->updateLanguage($_POST['id'], $_POST['name'], $is_active, $is_default)) {
                    $_SESSION['success'] = 'Dil başarıyla güncellendi.';
                } else {
                    $_SESSION['error'] = 'Dil güncellenirken bir hata oluştu.';
                }
            }
            break;

        case 'delete':
            if (isset($_POST['id'])) {
                if ($language->deleteLanguage($_POST['id'])) {
                    $_SESSION['success'] = 'Dil başarıyla silindi.';
                } else {
                    $_SESSION['error'] = 'Dil silinirken bir hata oluştu.';
                }
            }
            break;

        case 'translate':
            if (isset($_POST['language_id']) && isset($_POST['translations'])) {
                foreach ($_POST['translations'] as $key => $value) {
                    $language->setTranslation($_POST['language_id'], $key, $value);
                }
                $_SESSION['success'] = 'Çeviriler başarıyla güncellendi.';
            }
            break;
    }
    header('Location: languages.php');
    exit;
}

// Aktif dilleri getir
$languages = $language->getActiveLanguages();
$translation_keys = $language->getTranslationKeys();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dil Yönetimi - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="users.php">
                                <i class="bi bi-people"></i> Kullanıcılar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="languages.php">
                                <i class="bi bi-translate"></i> Diller
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="settings.php">
                                <i class="bi bi-gear"></i> Ayarlar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Ana içerik -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dil Yönetimi</h1>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Dil ekleme formu -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Yeni Dil Ekle</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="action" value="add">
                            <div class="col-md-4">
                                <label for="code" class="form-label">Dil Kodu</label>
                                <input type="text" class="form-control" id="code" name="code" required>
                            </div>
                            <div class="col-md-4">
                                <label for="name" class="form-label">Dil Adı</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input type="checkbox" class="form-check-input" id="is_default" name="is_default">
                                    <label class="form-check-label" for="is_default">Varsayılan Dil</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Dil Ekle</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Dil listesi -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Diller</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Dil Kodu</th>
                                        <th>Dil Adı</th>
                                        <th>Durum</th>
                                        <th>Varsayılan</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($languages as $lang): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($lang['code']); ?></td>
                                            <td><?php echo htmlspecialchars($lang['name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $lang['is_active'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $lang['is_active'] ? 'Aktif' : 'Pasif'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($lang['is_default']): ?>
                                                    <i class="bi bi-check-circle-fill text-success"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" 
                                                        onclick="editLanguage(<?php echo htmlspecialchars(json_encode($lang)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        onclick="translateLanguage(<?php echo $lang['id']; ?>)">
                                                    <i class="bi bi-translate"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="deleteLanguage(<?php echo $lang['id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Dil düzenleme modalı -->
    <div class="modal fade" id="editLanguageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dili Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Dil Adı</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                                <label class="form-check-label" for="edit_is_active">Aktif</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="edit_is_default" name="is_default">
                                <label class="form-check-label" for="edit_is_default">Varsayılan Dil</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Çeviri modalı -->
    <div class="modal fade" id="translateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Çevirileri Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="translate">
                        <input type="hidden" name="language_id" id="translate_language_id">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Anahtar</th>
                                        <th>Çeviri</th>
                                    </tr>
                                </thead>
                                <tbody id="translations_table">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Dil silme modalı -->
    <div class="modal fade" id="deleteLanguageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dili Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bu dili silmek istediğinizden emin misiniz?</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-danger">Sil</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dil düzenleme
        function editLanguage(language) {
            document.getElementById('edit_id').value = language.id;
            document.getElementById('edit_name').value = language.name;
            document.getElementById('edit_is_active').checked = language.is_active == 1;
            document.getElementById('edit_is_default').checked = language.is_default == 1;
            new bootstrap.Modal(document.getElementById('editLanguageModal')).show();
        }

        // Dil silme
        function deleteLanguage(id) {
            document.getElementById('delete_id').value = id;
            new bootstrap.Modal(document.getElementById('deleteLanguageModal')).show();
        }

        // Çeviri düzenleme
        function translateLanguage(languageId) {
            document.getElementById('translate_language_id').value = languageId;
            const translationsTable = document.getElementById('translations_table');
            translationsTable.innerHTML = '';

            <?php foreach ($translation_keys as $key): ?>
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><?php echo htmlspecialchars($key); ?></td>
                    <td>
                        <input type="text" class="form-control" name="translations[<?php echo htmlspecialchars($key); ?>]" 
                               value="<?php echo htmlspecialchars($language->get($key)); ?>">
                    </td>
                `;
                translationsTable.appendChild(row);
            <?php endforeach; ?>

            new bootstrap.Modal(document.getElementById('translateModal')).show();
        }
    </script>
</body>
</html> 