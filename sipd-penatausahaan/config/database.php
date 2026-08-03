<?php


// ============================================================
// KONFIGURASI DATABASE - SIPD Penatausahaan
// ============================================================
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sipd_penatausahaan');

$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

// Set karakter set
mysqli_set_charset($koneksi, 'utf8');
?>
