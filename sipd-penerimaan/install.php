<?php
// ============================================================
// INSTALLER - SIPD Penerimaan (versi ramah hosting)
// ------------------------------------------------------------
// TIDAK membuat database (hosting shared tidak mengizinkan CREATE
// DATABASE). User wajib membuat database kosong lebih dulu di
// cPanel/Plesk, lalu mengisi form berikut dengan data DB tersebut.
//
// Alur:
//   1. Konek ke database (yang sudah dibuat user di panel)
//   2. Jalankan semua CREATE TABLE + data seed (tanpa CREATE DB / USE)
//   3. Hash password demo (123456)
//   4. Tulis config/database_config.php
//   5. Deteksi BASE_URL otomatis untuk config.php
// ============================================================
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '0'); // jangan menampilkan langsung, dikumpulkan

if (!defined('APP_NAME')) define('APP_NAME', 'SIPD Penerimaan');

$msg      = '';
$ok       = false;
$details  = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && (($_POST['action'] ?? '') === 'install')) {
    $db_host = trim($_POST['db_host'] ?: 'localhost');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = trim($_POST['db_name'] ?? '');

    if ($db_user === '' || $db_name === '') {
        $msg = 'Nama user dan nama database wajib diisi.';
    } else {
        // 1) Koneksi tanpa memilih database dulu
        $tmp = @mysqli_connect($db_host, $db_user, $db_pass);
        if (!$tmp) {
            $msg = 'Koneksi database gagal: ' . mysqli_connect_error();
        } else {
            // select database (DB sudah dibuat user di panel)
            if (!mysqli_select_db($tmp, $db_name)) {
                $msg = "Database '$db_name' tidak ada atau user tidak punya akses: " . mysqli_error($tmp);
            } else {
                // 2) Baca schema & buang baris CREATE DATABASE / USE
                $schema = file_get_contents(__DIR__ . '/database/schema.sql');
                $lines = array_filter(explode("\n", $schema), function($line) {
                    $l = strtoupper(trim($line));
                    return !(strpos($l, 'CREATE DATABASE') === 0 || strpos($l, 'USE ') === 0 || $l === '');
                });
                $schema_clean = implode("\n", $lines);

                // 3) Jalankan multi query
                $schema_ok = true;
                if (mysqli_multi_query($tmp, $schema_clean)) {
                    do {
                        if (mysqli_error($tmp)) { $schema_ok = false; $details .= mysqli_error($tmp) . '; '; }
                        if ($res = mysqli_store_result($tmp)) mysqli_free_result($res);
                    } while (mysqli_more_results($tmp) && mysqli_next_result($tmp));
                } else {
                    $schema_ok = false;
                    $details = mysqli_error($tmp);
                }

                if (!$schema_ok) {
                    $msg = 'Query gagal saat membuat tabel. ' . $details;
                } else {
                    // 4) Hash password demo
                    $hash = password_hash('123456', PASSWORD_BCRYPT);
                    $hash_esc = mysqli_real_escape_string($tmp, $hash);
                    mysqli_query($tmp, "UPDATE `users` SET password='$hash_esc' WHERE password='PLACEHOLDER_HASH_123456'");

                    // 5) Deteksi BASE_URL otomatis
                    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
                    $dir = rtrim(dirname($scriptName), '/\\');
                    $base_url = $dir . '/';
                    if ($base_url === '//') $base_url = '/';

                    $cfg_path = __DIR__ . '/config/config.php';
                    $config = file_get_contents($cfg_path);
                    $config = preg_replace("/define\('BASE_URL',\s*'[^']*'\);/", "define('BASE_URL', '$base_url');", $config);
                    file_put_contents($cfg_path, $config);

                    // 6) Tulis config/database_config.php
                    $db_cfg = "<?php\n"
                            . "// KONFIGURASI DATABASE - SIPD Penerimaan (auto-generated)\n"
                            . "define('DB_HOST', " . var_export($db_host, true) . ");\n"
                            . "define('DB_USER', " . var_export($db_user, true) . ");\n"
                            . "define('DB_PASS', " . var_export($db_pass, true) . ");\n"
                            . "define('DB_NAME', " . var_export($db_name, true) . ");\n"
                            . "\$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);\n"
                            . "if (!\$koneksi) { die('Koneksi database gagal: ' . mysqli_connect_error()); }\n"
                            . "mysqli_set_charset(\$koneksi, 'utf8mb4');\n"
                            . "?>\n";
                    $db_cfg_path = __DIR__ . '/config/database_config.php';
                    if (file_put_contents($db_cfg_path, $db_cfg)) {
                        $ok = true;
                        $msg = 'Instalasi berhasil! Database <strong>' . htmlspecialchars($db_name) . '</strong> sudah siap.';
                    } else {
                        $msg = 'Database menjalankan CREATE TABLE berhasil, tetapi gagal menulis <code>config/database_config.php</code>. Pastikan folder <code>config/</code> dapat ditulis (chmod 755/775).';
                    }
                }
            }
            mysqli_close($tmp);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Installer - <?= APP_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body{background:linear-gradient(135deg,#1a3c6e,#2c5282);display:flex;align-items:center;min-height:100vh}
  .card-install{max-width:600px;margin:30px auto;border:none;border-radius:15px}
</style>
</head>
<body>
<div class="container">
  <div class="card card-install shadow-lg">
    <div class="card-header bg-primary text-white p-3">
      <h5 class="mb-0"><i class="bi bi-wrench-adjustable"></i> Installer <?= APP_NAME ?></h5>
      <small>Penatausahaan Penerimaan Pendapatan (SKPKD) — Modul 1</small>
    </div>
    <div class="card-body p-4">

      <?php if ($ok): ?>
        <div class="alert alert-success"><strong><i class="bi bi-check-circle"></i> <?= $msg ?></strong></div>
        <div class="alert alert-info small">
          Akun demo — password <code>123456</code>: <code>bendahara</code>, <code>verifikator</code>, <code>ppk</code>, <code>admin</code>.
        </div>
        <a href="login.php" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Menuju Halaman Login</a>

      <?php else: ?>
        <?php if ($msg): ?>
          <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= $msg ?></div>
        <?php endif; ?>

        <div class="alert alert-warning small">
          <i class="bi bi-info-circle"></i> <strong>Penting:</strong> Buat <u>database kosong</u> terlebih dahulu di panel hosting
          (cPanel → MySQL Databases / phpMyAdmin). Hosting shared <strong>tidak mengizinkan installer membuat database</strong>.
          Isi form dengan data database tersebut.
        </div>

        <form method="POST">
          <input type="hidden" name="action" value="install">
          <div class="mb-3"><label class="form-label">Host Database</label>
            <input name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Nama User Database</label>
            <input name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" class="form-control" placeholder="contoh: cpanel_username_dbuser" required></div>
          <div class="mb-3"><label class="form-label">Password User Database</label>
            <input type="password" name="db_pass" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Nama Database (sudah dibuat di panel)</label>
            <input name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>" class="form-control" placeholder="contoh: cpanel_username_dbname" required></div>
          <button class="btn btn-success w-100 py-2"><i class="bi bi-rocket-takeoff"></i> Install Sekarang</button>
        </form>

        <div class="small text-muted mt-3">
          Installer menjalankan CREATE TABLE + data dari <code>database/schema.sql</code> pada database yang sudah Anda buat,
          meng-hash password demo, dan menulis <code>config/database_config.php</code> + menyesuaikan <code>BASE_URL</code>.
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>
</body>
</html>
