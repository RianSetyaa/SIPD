<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $base = isset($_SERVER['BASE_URL']) ? $_SERVER['BASE_URL'] : '';
    // gunakan konfigurasi jika ada
    if (file_exists(__DIR__.'/config/config.php')) {
        require_once __DIR__.'/config/config.php';
        header('Location: '.BASE_URL.'dashboard.php'); exit;
    }
    header('Location: dashboard.php'); exit;
}
header('Location: login.php');
exit;
