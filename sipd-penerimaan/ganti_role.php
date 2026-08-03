<?php
// ============================================================
// PENGGANTI PERAN AKTIF (Role Switcher) untuk mahasiswa
// Mahasiswa dapat menjalankan semua peran (bendahara / verifikator
// / ppk) pada data SKPKD miliknya sendiri.
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();

if (is_mahasiswa()) {
    $role = $_GET['role'] ?? '';
    $allowed = ['bendahara', 'verifikator', 'ppk'];
    if (in_array($role, $allowed)) {
        $_SESSION['active_role'] = $role;
        flash_success('Peran aktif diubah menjadi <strong>' . htmlspecialchars(strtoupper($role)) . '</strong>.');
    } else {
        flash_error('Peran tidak valid.');
    }
} else {
    flash_error('Fitur ganti peran hanya untuk akun mahasiswa.');
}
header('Location: ' . BASE_URL . 'dashboard.php');
exit();
