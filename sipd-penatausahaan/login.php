<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

// Jika sudah login, arahkan ke dashboard
if (is_logged_in()) {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username' AND is_active = 1 LIMIT 1";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            // Sukses login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nip'] = $user['nip'];
            $_SESSION['skpd_id'] = $user['skpd_id'];

            header('Location: ' . BASE_URL . 'dashboard.php');
            exit();
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIPD Penatausahaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a3c6e 0%, #2c5282 100%);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .card-login { max-width: 420px; width: 90%; border: none; border-radius: 15px; }
        .logo-circle {
            width: 80px; height: 80px; border-radius: 50%;
            background: var(--bs-primary); color: #fff; margin: 0 auto;
            display: flex; align-items: center; justify-content: center; font-size: 2rem;
        }
        .form-control { border-radius: 8px; padding: 12px; }
        .btn-login { border-radius: 8px; padding: 12px; font-weight: 600; }
        .info-akun { font-size: 0.8rem; background: #f8f9fa; border-radius: 8px; padding: 10px; }
        .info-akun span { font-family: monospace; }
    </style>
</head>
<body>
    <div class="card card-login p-4 shadow-lg">
        <div class="text-center mb-3">
            <div class="logo-circle">
                <i class="bi bi-building"></i>
            </div>
            <h4 class="mt-3 fw-bold">SIPD Penatausahaan</h4>
            <p class="text-muted">Sistem Informasi Keuangan Daerah - Modul Penatausahaan</p>
            <span class="badge bg-info text-dark">Aplikasi Pembelajaran Mahasiswa</span>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><i class="bi bi-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-login w-100">
                <i class="bi bi-box-arrow-in-right"></i> Masuk
            </button>
        </form>

        <div class="info-akun mt-3">
            <strong><i class="bi bi-info-circle"></i> Akun Demo untuk Mahasiswa (password: <span>123456</span>):</strong>
            <ul class="mb-0 mt-1 ps-3">
                <li>admin (Administrator)</li>
                <li>penatausahaan (Bendahara Pengeluaran/PA)</li>
                <li>bendahara (Bendahara)</li>
                <li>verifikator (Verifikator)</li>
            </ul>
        </div>
    </div>
</body>
</html>
