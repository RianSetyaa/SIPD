<?php
// ============================================================
// BOOTSTRAP KONFIGURASI DATABASE
// Membaca file database_config.php (dibuat oleh installer)
// Jika belum terkonfigurasi, arahkan ke install.php
// ============================================================

session_start();

$config_path = __DIR__ . '/database_config.php';
if (!file_exists($config_path)) {
    // Modul selain installer membutuhkan konfigurasi
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'install.php') {
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/') . 'install.php');
        exit();
    }
}

if (file_exists($config_path)) {
    require_once $config_path; // mendefinisikan $koneksi + DB_HOST/DB_USER/DB_PASS/DB_NAME
} else {
    $koneksi = null;
}
