<?php
session_start();
require_once '../config/database.php';
require_once '../models/User.php';
require_once '../helpers/Mailer.php';

// Kullanıcı zaten giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $error = 'Lütfen e-posta adresinizi girin.';
    } else {
        $user = new User($db);
        $user->email = $email;
        
        if ($user->readByEmail()) {
            // Benzersiz token oluştur
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Token'ı veritabanına kaydet
            $query = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$user->id, $token, $expires]);
            
            // E-posta gönder
            $mailer = new Mailer($db);
            $user->reset_token = $token;
            
            if ($mailer->sendPasswordReset($user)) {
                $success = true;
                $message = 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.';
            } else {
                $error = 'E-posta gönderilirken bir hata oluştu.';
            }
        } else {
            $error = 'Bu e-posta adresi ile kayıtlı bir kullanıcı bulunamadı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifremi Unuttum - AdminTE</title>
    
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
                            <p class="text-muted">Şifre Sıfırlama</p>
                        </div>

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

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta Adresi</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Şifre Sıfırlama Bağlantısı Gönder
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <a href="login.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left"></i> Giriş Sayfasına Dön
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 