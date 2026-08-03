<?php
// ============================================================
// KONFIGURASI UTAMA APLIKASI
// ============================================================

// URL dasar aplikasi (ubah sesuai kebutuhan / folder)
// Untuk hosting di root domain (sipd.loketiq.com diset ke folder ini), gunakan '/'
define('BASE_URL', '/');
define('ROOT_PATH', dirname(__DIR__));

// Folder upload
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Ambil data user dari session
function current_user() {
    if (isset($_SESSION['user_id'])) {
        return array(
            'id' => $_SESSION['user_id'],
            'nama' => $_SESSION['nama'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'nip' => $_SESSION['nip']
        );
    }
    return null;
}
?>

