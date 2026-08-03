<?php
// ============================================================
// FUNGSI BANTU - SIPD Penatausahaan
// ============================================================

// Cek apakah user sudah login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Cek peran user, jika tidak cocok arahkan ke halaman gagal
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

function require_role($roles) {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
    $current_role = $_SESSION['role'];
    if (!in_array($current_role, (array)$roles)) {
        $_SESSION['error'] = 'Anda tidak memiliki hak akses untuk halaman ini.';
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }
}

// Sanitasi input
function sanitize($data) {
    global $koneksi;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($koneksi, $data);
    return $data;
}

// Format Rupiah
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Format tanggal
function tanggal($tanggal) {
    if (empty($tanggal)) return '-';
    return date('d/m/Y', strtotime($tanggal));
}

function tanggal_panjang($tanggal) {
    if (empty($tanggal)) return '-';
    $bulan = array(
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
        4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    );
    $t = date('Y-m-d', strtotime($tanggal));
    $tgl = (int)date('d', strtotime($t));
    $bln = (int)date('m', strtotime($t));
    $thn = date('Y', strtotime($t));
    return $tgl . ' ' . $bulan[$bln] . ' ' . $thn;
}

// Buat nomor otomatis (SPP/SPM/SP2D)
function buat_nomor($prefix, $tabel, $kolom, $tahun = null) {
    global $koneksi;
    $tahun = $tahun ?: date('Y');
    $query = "SELECT $kolom FROM $tabel WHERE $kolom LIKE '$prefix%-%/$tahun' ORDER BY $kolom DESC LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $last = (int)substr($row[$kolom], strlen($prefix), -strlen('/' . $tahun));
        $next = $last + 1;
    } else {
        $next = 1;
    }
    return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT) . '/' . $tahun;
}

// Upload file
function upload_file($file, $folder, $allowed_ext = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx')) {
    $nama_asli = $file['name'];
    $tmp = $file['tmp_name'];
    $error = $file['error'];
    $size = $file['size'];

    if ($error !== UPLOAD_ERR_OK) {
        return array('status' => false, 'pesan' => 'Gagal upload file.');
    }

    $ekstensi = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));
    if (!in_array($ekstensi, $allowed_ext)) {
        return array('status' => false, 'pesan' => 'Ekstensi file tidak diizinkan.');
    }

    if ($size > 5000000) { // maks 5MB
        return array('status' => false, 'pesan' => 'Ukuran file maksimal 5MB.');
    }

    $nama_baru = time() . '_' . uniqid() . '.' . $ekstensi;
    $path_tujuan = $folder . $nama_baru;

    if (move_uploaded_file($tmp, $path_tujuan)) {
        return array('status' => true, 'nama' => $nama_baru);
    } else {
        return array('status' => false, 'pesan' => 'Gagal menyimpan file.');
    }
}

// Tampilkan pesan flash
function flash() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> ' . $_SESSION['success'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> ' . $_SESSION['error'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
        unset($_SESSION['error']);
    }
}
?>
