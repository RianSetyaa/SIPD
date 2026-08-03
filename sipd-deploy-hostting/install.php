<?php
// ============================================================
// INSTALLER - SIPD Penatausahaan (untuk Hosting)
// Jalankan sekali: https://sipd.loketiq.com/install.php
// ============================================================

$message = '';
$error = '';

// File konfigurasi yang akan ditulis (dibaca oleh config/database.php)
$config_file = __DIR__ . '/config/database_config.php';

// ========== Menjalankan instalasi ==========
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['action'] ?? '') === 'install') {
    $db_host     = trim($_POST['db_host'] ?? 'localhost');
    $db_user     = trim($_POST['db_user'] ?? '');
    $db_pass     = $_POST['db_pass'] ?? '';
    $db_name     = trim($_POST['db_name'] ?? '');

    if ($db_user === '' || $db_name === '') {
        $error = 'Nama pengguna database dan nama database wajib diisi.';
    } else {
        // Coba koneksi tanpa memilih database
        $temporary_link = @mysqli_connect($db_host, $db_user, $db_pass);
        if (!$temporary_link) {
            $error = 'Koneksi database gagal: ' . mysqli_connect_error();
        } else {
            // Buat database jika belum ada
            @mysqli_query($temporary_link, "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            mysqli_select_db($temporary_link, $db_name);

            // Baca file schema dan hilangkan baris CREATE DATABASE / USE
            $schema = file_get_contents(__DIR__ . '/database/schema.sql');
            $schema_lines = array_filter(explode("\n", $schema), function($line) {
                $l = strtoupper(trim($line));
                return !(strpos($l, 'CREATE DATABASE') === 0 || strpos($l, 'USE ') === 0);
            });
            $schema = implode("\n", $schema_lines);

            if (mysqli_multi_query($temporary_link, $schema)) {
                do {
                    if ($result = mysqli_store_result($temporary_link)) {
                        mysqli_free_result($result);
                    }
                } while (mysqli_more_results($temporary_link) && mysqli_next_result($temporary_link));

                // Reset password hash yang benar (password: 123456)
                $password_hash = password_hash('123456', PASSWORD_DEFAULT);
                $pass_esc = mysqli_real_escape_string($temporary_link, $password_hash);
                mysqli_query($temporary_link, "UPDATE users SET password = '$pass_esc' WHERE password = 'PLACEHOLDER_HASH_123456'");

                // Tulis file config/database_config.php
                $config_content = "<?php\n\n// ============================================================\n// KONFIGURASI DATABASE - SIPD Penatausahaan (hasil instalasi)\n// ============================================================\nsession_start();\n\ndefine('DB_HOST', " . var_export($db_host, true) . ");\ndefine('DB_USER', " . var_export($db_user, true) . ");\ndefine('DB_PASS', " . var_export($db_pass, true) . ");\ndefine('DB_NAME', " . var_export($db_name, true) . ");\n\n\$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);\n\nif (!\$koneksi) {\n    die('Koneksi database gagal: ' . mysqli_connect_error());\n}\n\nmysqli_set_charset(\$koneksi, 'utf8mb4');\n?>";
                if (file_put_contents($config_file, $config_content)) {
                    @unlink(__FILE__); // hapus installer demi keamanan
                    $message = 'Instalasi berhasil! Database <strong>' . htmlspecialchars($db_name) . '</strong> telah dibuat dan <code>config/database_config.php</code> telah dikonfigurasi.';
                } else {
                    $error = 'Database berhasil dibuat, tetapi gagal menulis file config/database_config.php. Pastikan folder <code>config/</code> bisa ditulis (chmod 755), lalu coba lagi.';
                }
            } else {
                $error = 'Query gagal: ' . mysqli_error($temporary_link);
            }
            mysqli_close($temporary_link);
        }
    }
}

// Cek apakah sudah pernah diinstall
$already_installed = file_exists($config_file);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Installer SIPD Penatausahaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a3c6e 0%, #2c5282 100%); min-height: 100vh; }
        .card-install { max-width: 640px; margin: 40px auto; border: none; border-radius: 15px; }
        .form-control { border-radius: 8px; padding: 11px; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="card card-install shadow-lg">
        <div class="card-header bg-primary text-white p-3">
            <h5 class="mb-0"><i class="bi bi-wrench-adjustable"></i> Installer SIPD Penatausahaan</h5>
            <small>Deployment untuk hosting — sipd.loketiq.com</small>
        </div>
        <div class="card-body p-4">

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <strong><i class="bi bi-check-circle"></i> Sukses!</strong><br>
                    <?= $message ?>
                </div>
                <div class="alert alert-info small">
                    Aplikasi siap digunakan. Buka halaman utama <a href="./"><code>sipd.loketiq.com</code></a> dan login.
                    File <code>install.php</code> telah dihapus otomatis demi keamanan.
                </div>
                <a href="login.php" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Menuju Halaman Login</a>

            <?php elseif ($error): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
                <a href="install.php" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> Coba Lagi</a>

            <?php elseif ($already_installed): ?>
                <div class="alert alert-success">
                    <strong><i class="bi bi-check-circle"></i> Aplikasi sudah terinstall!</strong>
                    File <code>config/database_config.php</code> sudah ada.
                </div>
                <a href="login.php" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Menuju Halaman Login</a>

            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="action" value="install">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-database"></i> Konfigurasi Database Hosting</h6>
                    <div class="alert alert-warning small">
                        <i class="bi bi-info-circle"></i> Masukkan data database yang tersedia di panel hosting (cPanel/Plesk). Umumnya host = <code>localhost</code>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Host Database</label>
                        <input type="text" name="db_host" class="form-control" value="localhost" required>
                        <div class="form-text">Biasanya <code>localhost</code> di hosting shared.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama User Database</label>
                        <input type="text" name="db_user" class="form-control" placeholder="contoh: sipdlokt_dbuser" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Database</label>
                        <input type="password" name="db_pass" class="form-control" placeholder="Password user database">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Database</label>
                        <input type="text" name="db_name" class="form-control" placeholder="contoh: sipdlokt_db" required>
                        <div class="form-text">Nama database yang sudah/bisa dibuat di panel hosting.</div>
                    </div>
                    <div class="alert alert-info small">
                        <strong>Akun demo setelah install — password semua: <span class="badge bg-secondary">123456</span></strong>
                        <ul class="mb-0 mt-1 ps-3">
                            <li><code>admin</code> (Administrator)</li>
                            <li><code>penatausahaan</code> (PPK)</li>
                            <li><code>bendahara</code> (Bendahara)</li>
                            <li><code>verifikator</code> (Verifikator)</li>
                        </ul>
                    </div>
                    <button type="submit" class="btn btn-success w-100 py-2"><i class="bi bi-rocket-takeoff"></i> Install Sekarang</button>
                </form>
            <?php endif; ?>

        </div>
        <div class="card-footer text-muted small bg-light p-3">
            <i class="bi bi-shield-check"></i> Aplikasi pembelajaran. File installer otomatis dihapus setelah berhasil agar tidak bisa dijalankan ulang.
        </div>
    </div>
</div>
</body>
</html>
