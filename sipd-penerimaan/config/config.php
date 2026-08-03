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
            'role'     => isset($_SESSION['active_role']) ? $_SESSION['active_role'] : $_SESSION['role'],
            'base_role'=> $_SESSION['role'],
            'is_mahasiswa' => isset($_SESSION['is_mahasiswa']) ? $_SESSION['is_mahasiswa'] : false,
            'nim'      => isset($_SESSION['nim']) ? $_SESSION['nim'] : null,
            'skpd_id'  => $_SESSION['skpd_id'],
        );
    }
    return null;
}

// Cakupan data: setiap user melihat data SKPKD miliknya saja
// (admin melihat semua = skpd_id 0 / null)
function scope_skpd() {
    if (!isset($_SESSION['user_id'])) return '1=0';
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && !isset($_SESSION['is_mahasiswa'])) {
        return '1=1'; // admin melihat semua
    }
    $skpd = (int)$_SESSION['skpd_id'];
    return "skpd_id=$skpd";
}

// Sama seperti scope_skpd() tapi dengan prefix alias tabel
// (berguna pada query JOIN yang punya beberapa kolom skpd_id)
function scope_skpd_alias($alias) {
    if (!isset($_SESSION['user_id'])) return '1=0';
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && !isset($_SESSION['is_mahasiswa'])) {
        return '1=1'; // admin melihat semua
    }
    $skpd = (int)$_SESSION['skpd_id'];
    return "$alias.skpd_id=$skpd";
}
