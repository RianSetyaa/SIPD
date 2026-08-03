<?php
// ============================================================
// KONFIGURASI UMUM - SIPD Penerimaan (SKPKD)
// ============================================================

// BASE_URL - sesuaikan jika dipindah ke subfolder
// Untuk XAMPP/Laragon di folder htdocs/sipd-penerimaan:
//   define('BASE_URL', '/');
// Untuk root domain hosting:
//   define('BASE_URL', '/');
define('BASE_URL', '/');

define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Informasi aplikasi
define('APP_NAME', 'SIPD Penerimaan');
define('APP_FULL', 'Penatausahaan Penerimaan Pendapatan (SKPKD)');

// Ambil data user yang sedang login
function current_user() {
    if (isset($_SESSION['user_id'])) {
        return array(
            'id'       => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'nama'     => $_SESSION['nama'],
            'role'     => $_SESSION['role'],
            'skpd_id'  => $_SESSION['skpd_id'],
        );
    }
    return null;
}
