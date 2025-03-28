<?php
session_start();
require_once '../config/database.php';
require_once '../models/User.php';

// Kullanıcı zaten giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Token kontrolü
if (!isset($_GET['token'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$success = false;
$error = '';

// Token'ı kontrol et
$query = "SELECT pr.*, u.email 
          FROM password_resets pr 
          JOIN users u ON pr.user_id = u.id 
          WHERE pr.token = ? AND pr.expires_at > NOW() 
          ORDER BY pr.created_at DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute([$_GET['token']]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    $error = 'Geçersiz veya süresi dolmuş şifre sıfırlama bağlantısı.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($password) || empty($confirm_password)) {
            $error = 'Lütfen tüm alanları doldurun.';
        } elseif ($password !== $confirm_password) {
            $error = 'Şifreler eşleşmiyor.';
        } elseif (strlen($password) < 6) {
            $error = 'Şifre en az 6 karakter olmalıdır.';
        } else {
            $user = new User($db);
            $user->id = $reset['user_id'];
            $user->password = $password;
            
            if ($user->update()) {
                // Token'ı kullanıldı olarak işaretle
                $query = "UPDATE password_resets SET used = 1 WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$reset['id']]);
                
                $success = true;
                $message = 'Şifreniz başarıyla güncellendi. Şimdi giriş yapabilirsiniz.';
            } else {
                $error = 'Şifre güncellenirken bir hata oluştu.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırla - AdminTE</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h3 class="mb-0">AdminTE</h3>
                            <p class="text-muted">Yeni Şifre Belirle</p>
                        </div>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <div class="text-center mt-4">
                                <a href="login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Giriş Yap
                                </a>
                            </div>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Yeni Şifre</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Yeni Şifre (Tekrar)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Şifreyi Güncelle
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 