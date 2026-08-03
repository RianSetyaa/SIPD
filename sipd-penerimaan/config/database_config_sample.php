<?php
// ============================================================
// CONTOH KONFIGURASI DATABASE
// Salin file ini menjadi database_config.php lalu sesuaikan nilai
// dengan kredensial hosting Anda.
// ============================================================

define('DB_HOST', 'localhost');            // biasanya 'localhost' di hosting
define('DB_USER', 'USER_DB_ANDA');         // contoh: cpaneluser_dbsipd
define('DB_PASS', 'PASSWORD_DB_ANDA');
define('DB_NAME', 'NAMA_DB_ANDA');         // contoh: cpaneluser_dbsipd

$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($koneksi, 'utf8mb4');
