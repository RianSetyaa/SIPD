<?php
// ============================================================
// UTILITAS: Set password demo (123456) untuk semua user
// DIPAKAI SETELAH impor database secara manual (phpMyAdmin),
// karena placeholder password pada schema belum di-hash.
//
// Cara pakai:
//   1. Pastikan config/database_config.php sudah benar
//   2. Buka file ini sekali:  yoursite/penerimaan/hash_password.php
//   3. Hapus file ini setelah selesai.
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$msg = ''; $ok = false;
if (file_exists(__DIR__.'/config/database_config.php') && isset($koneksi)) {
    $hash = password_hash('123456', PASSWORD_BCRYPT);
    $hc = mysqli_real_escape_string($koneksi, $hash);
    mysqli_query($koneksi, "UPDATE users SET password='$hc' WHERE password='PLACEHOLDER_HASH_123456'");
    $affected = mysqli_affected_rows($koneksi);
    // Juga pastikan semua user (mencakup yang telah terganti)
    mysqli_query($koneksi, "UPDATE users SET password='$hc'" );
    $ok = true;
    $msg = "Password semua user (demo) di-set ke <code>123456</code>.<br>Catatan: baris yang diperbaiki dari placeholder: $affected.";
} else {
    $msg = 'Koneksi database belum ditemukan. Jalankan install.php dulu ATAU pastikan config/database_config.php ada.';
}
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Set Password Demo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container" style="max-width:520px;margin-top:60px">
<div class="card shadow"><div class="card-body">
<h5><i class="bi bi-key"></i> Password Demo</h5>
<?php if ($ok): ?>
  <div class="alert alert-success"><?= $msg ?></div>
  <a href="login.php" class="btn btn-primary">Login</a>
<?php else: ?>
  <div class="alert alert-warning"><?= $msg ?></div>
<?php endif; ?>
<div class="small text-muted mt-2">Akun demo (password <code>123456</code>): bendahara, verifikator, ppk, admin.</div>
</div></div></div>
</body></html>
