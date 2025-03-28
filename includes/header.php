<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Language.php';

$database = new Database();
$db = $database->getConnection();
$language = new Language($db);

// Dil değiştirme işlemi
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if ($language->setLanguage($lang)) {
        $_SESSION['lang'] = $lang;
    }
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Aktif dilleri getir
$languages = $language->getActiveLanguages();
?>
<!DOCTYPE html>
<html lang="<?php echo $language->get('lang_code'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $language->get('site_title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><?php echo $language->get('site_name'); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><?php echo $language->get('home'); ?></a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php"><?php echo $language->get('dashboard'); ?></a>
                        </li>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="users.php"><?php echo $language->get('users'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="languages.php"><?php echo $language->get('languages'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="settings.php"><?php echo $language->get('settings'); ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <!-- Dil seçimi -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-translate"></i> <?php echo $language->get('language'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php foreach ($languages as $lang): ?>
                                <li>
                                    <a class="dropdown-item <?php echo $lang['code'] === $language->get('lang_code') ? 'active' : ''; ?>" 
                                       href="?lang=<?php echo $lang['code']; ?>">
                                        <?php echo htmlspecialchars($lang['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="profile.php">
                                        <i class="bi bi-person"></i> <?php echo $language->get('profile'); ?>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="bi bi-box-arrow-right"></i> <?php echo $language->get('logout'); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><?php echo $language->get('login'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><?php echo $language->get('register'); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4"> 