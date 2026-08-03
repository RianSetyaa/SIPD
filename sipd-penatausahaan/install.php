<?php
// ============================================================
// INSTALLER - SIPD Penatausahaan
// Jalankan sekali: buka http://localhost/sipd-penatausahaan/install.php
// ============================================================

// Konfigurasi database (sama seperti config)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sipd_penatausahaan');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Baca file schema
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');

    // Koneksi tanpa database untuk membuat database
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    if (!$conn) {
        $error = 'Koneksi gagal: ' . mysqli_connect_error();
    } else {
        // Jalankan seluruh query dalam schema.sql
        if (mysqli_multi_query($conn, $schema)) {
            do {
                // konsumsi semua hasil
            } while (mysqli_more_results($conn) && mysqli_next_result($conn));

            // Perbarui password dengan hash yang benar (password: 123456)
            $conn2 = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $password_hash = password_hash('123456', PASSWORD_DEFAULT);
            $pass_esc = mysqli_real_escape_string($conn2, $password_hash);
            mysqli_query($conn2, "UPDATE users SET password = '$pass_esc' WHERE password = 'PLACEHOLDER_HASH_123456'");
            mysqli_close($conn2);

            $message = 'Database berhasil dibuat! Password semua akun demo adalah <strong>123456</strong>.';
        } else {
            $error = 'Query gagal: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Installer SIPD Penatausahaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 700px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Installer Aplikasi SIPD Penatausahaan</h5>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <strong><i class="bi bi-check-circle"></i> Sukses!</strong><br>
                    <?= $message ?>
                </div>
                <a href="login.php" class="btn btn-primary">Menuju Halaman Login</a>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!$message): ?>
                <p>Klik tombol di bawah untuk membuat database dan tabel beserta data awal (seed).</p>
                <div class="alert alert-warning small">
                    <strong>Peringatan:</strong> Jalankan installer ini <u>sekali saja</u>. Pastikan MySQL/XAMPP/Laragon sudah berjalan.
                </div>
                <form method="POST">
                    <button type="submit" class="btn btn-success">Buat Database SIPD Penatausahaan</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
