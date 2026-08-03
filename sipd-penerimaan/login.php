<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($koneksi) {
        $u = sanitize($username);
        $q = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$u' AND is_active=1");
        $user = mysqli_fetch_assoc($q);
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['skpd_id']  = $user['skpd_id'];
            $_SESSION['nim']      = $user['nim'] ?? null;
            $_SESSION['prodi']    = $user['prodi'] ?? null;
            // Mahasiswa: ubah role operasional aktif dimulai bendahara + bisa semua role
            if ($user['role'] === 'mahasiswa') {
                $_SESSION['is_mahasiswa'] = true;
                $_SESSION['active_role']  = 'bendahara';
            } else {
                $_SESSION['is_mahasiswa'] = false;
                $_SESSION['active_role']  = $user['role'];
            }
            header('Location: ' . BASE_URL . 'dashboard.php');
            exit();
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Database belum dikonfigurasi. Jalankan install.php dulu.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - <?= APP_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:linear-gradient(135deg,#0f2a52,#1a3c6e);min-height:100vh;display:flex;align-items:center;}
.card{max-width:420px;margin:auto;border:none;border-radius:16px;}
</style>
</head>
<body>
<div class="container">
  <div class="card shadow p-4">
    <div class="text-center mb-3">
      <div class="fs-1"><i class="bi bi-cash-stack text-primary"></i></div>
      <h4 class="mb-0"><?= APP_NAME ?></h4>
      <small class="text-muted"><?= APP_FULL ?></small>
    </div>
    <?php if ($error): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <div class="mb-3"><label class="form-label">Username</label>
        <input class="form-control" name="username" required autofocus></div>
      <div class="mb-3"><label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required></div>
      <button class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
    </form>
    <div class="text-center mt-3 small">Belum punya akun SKPKD? <a href="register.php"><strong>Daftar Mahasiswa</strong></a></div>
  </div>
</div>
</body>
</html>

