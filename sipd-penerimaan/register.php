<?php
// ============================================================
// PENDAFTARAN MAHASISWA - SIPD Penerimaan
// Mahasiswa mendaftar, sistem membuat SKPKD (daerah) otomatis
// milik mahasiswa tersebut. Semua data calon transaksi terkait
// ke SKPKD miliknya, sehingga terpisah dari mahasiswa lain.
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

$error = '';
$success = '';

function skpd_kode_baru($koneksi) {
    // kode unik per SKPD mahasiswa: 9.<id> contoh 9.1, 9.2 dst (Grup kode 9 untuk SKPKD mahasiswa)
    $q = mysqli_query($koneksi, "SELECT COALESCE(MAX(id),0)+1 AS n FROM skpd");
    $r = mysqli_fetch_assoc($q);
    return '9.' . $r['n'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama        = trim($_POST['nama'] ?? '');
    $nim         = trim($_POST['nim'] ?? '');
    $prodi       = trim($_POST['prodi'] ?? '');
    $username    = trim($_POST['username'] ?? '');
    $password    = $_POST['password'] ?? '';
    $password2   = $_POST['password2'] ?? '';
    $daerah      = trim($_POST['daerah'] ?? '');      // nama kab/kota
    $kepala      = trim($_POST['kepala'] ?? '');

    if ($nama === '' || $username === '' || $password === '' || $daerah === '' || $nim === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif ($password !== $password2) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 5) {
        $error = 'Password minimal 5 karakter.';
    } elseif (mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id FROM users WHERE username='".sanitize($username)."'"))) {
        $error = 'Username sudah dipakai.';
    } else {
        // Buat SKPD milik mahasiswa (daerah virtual)
        $kode = skpd_kode_baru($koneksi);
        $nama_skpd = trim($_POST['nama_skpd'] ?? '') !== '' ? trim($_POST['nama_skpd']) : 'SKPKD ' . $daerah;
        $hash = password_hash($password, PASSWORD_BCRYPT);

        mysqli_begin_transaction($koneksi);
        $ok = true;

        $q = "INSERT INTO skpd (kode, nama, alamat, kepala, bendahara_penerimaan) VALUES
              ('$kode', '".sanitize($nama_skpd)."', '".sanitize($daerah)."', '".sanitize($kepala)."', '".sanitize($nama)."')";
        if (!mysqli_query($koneksi, $q)) { $ok = false; $error = 'Gagal membuat SKPD: '.mysqli_error($koneksi); }

        $skpd_id = $ok ? mysqli_insert_id($koneksi) : 0;

        // Buat user mahasiswa
        if ($ok) {
            $q2 = "INSERT INTO users (username, password, nama, skpd_id, role, nim, prodi)
                   VALUES ('".sanitize($username)."', '$hash', '".sanitize($nama)."', $skpd_id, 'mahasiswa', '".sanitize($nim)."', '".sanitize($prodi)."')";
            if (!mysqli_query($koneksi, $q2)) { $ok = false; $error = 'Gagal membuat akun: '.mysqli_error($koneksi); }
            $user_id = $ok ? mysqli_insert_id($koneksi) : 0;
        }

        // Tandai kepemilikan SKPD oleh mahasiswa
        if ($ok) {
            if (!mysqli_query($koneksi, "UPDATE skpd SET owner_id=$user_id WHERE id=$skpd_id")) {
                $ok = false; $error = 'Gagal menautkan kepemilikan: '.mysqli_error($koneksi);
            }
        }

        if ($ok) {
            // Salin rekening pendapatan dari template (SKPKD grup 9 pertama / daftar PAD standar)
            salin_rekening_template($koneksi, $skpd_id);
            mysqli_commit($koneksi);
            $success = "Pendaftaran berhasil! Silakan login dengan username <strong>".htmlspecialchars($username)."</strong>.";
        } else {
            mysqli_rollback($koneksi);
        }
    }
}

function salin_rekening_template($koneksi, $skpd_id) {
    // Ambil daftar rekening standar dari SKPD template (kode 1.20.05 = BPKD Cimahi)
    $tpl = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id FROM skpd WHERE kode='1.20.05' ORDER BY id LIMIT 1"));
    if ($tpl) {
        $rows = mysqli_query($koneksi, "SELECT kode,nama,level,induk_kode,jenis FROM rekening WHERE skpd_id={$tpl['id']}");
        while ($r = mysqli_fetch_assoc($rows)) {
            mysqli_query($koneksi, "INSERT INTO rekening (kode,nama,level,induk_kode,jenis,skpd_id)
                VALUES ('{$r['kode']}','".mysqli_real_escape_string($koneksi,$r['nama'])."',{$r['level']},".
                ($r['induk_kode']? "'{$r['induk_kode']}'":"NULL").",'{$r['jenis']}',$skpd_id)");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Daftar Mahasiswa - <?= APP_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:linear-gradient(135deg,#14532d,#15803d);min-height:100vh;display:flex;align-items:center;}
.card{max-width:540px;margin:auto;border:none;border-radius:14px}
</style>
</head>
<body>
<div class="container py-4">
  <div class="card shadow p-4">
    <div class="text-center mb-3">
      <div class="fs-1"><i class="bi bi-person-plus-fill text-success"></i></div>
      <h4 class="mb-0">Daftar Mahasiswa</h4>
      <small class="text-muted">Buat akun SKPKD (daerah) Anda sendiri</small>
    </div>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
      <a href="login.php" class="btn btn-success w-100">Menuju Login</a>
    <?php else: ?>
      <?php if ($error): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST">
        <div class="row g-2">
          <div class="col-12"><label class="form-label small mb-1">Nama Lengkap</label>
            <input name="nama" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label small mb-1">NIM</label>
            <input name="nim" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label small mb-1">Program Studi</label>
            <input name="prodi" class="form-control" placeholder="Akuntansi"></div>
          <div class="col-12"><label class="form-label small mb-1">Nama Daerah (Kabupaten/Kota)</label>
            <input name="daerah" class="form-control" placeholder="mis. Kab. Bandung" required></div>
          <div class="col-12"><label class="form-label small mb-1">Nama SKPKD/OPD (opsional)</label>
            <input name="nama_skpd" class="form-control" placeholder="kosongkan = SKPKD <daerah>"></div>
          <div class="col-12"><label class="form-label small mb-1">Nama Kepala/Kepala BPKD (opsional)</label>
            <input name="kepala" class="form-control"></div>
          <div class="col-12"><label class="form-label small mb-1">Username</label>
            <input name="username" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label small mb-1">Password</label>
            <input type="password" name="password" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label small mb-1">Ulangi Password</label>
            <input type="password" name="password2" class="form-control" required></div>
        </div>
        <button class="btn btn-success w-100 mt-3"><i class="bi bi-person-plus"></i> Daftar</button>
      </form>
      <div class="text-center mt-3 small">Sudah punya akun? <a href="login.php">Login</a></div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
