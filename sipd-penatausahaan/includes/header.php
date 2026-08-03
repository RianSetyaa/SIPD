<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Halaman yang sedang diakses (untuk menu aktif)
$current_page = basename($_SERVER['PHP_SELF']);

// Daftar menu berdasarkan peran
function role_menu($role) {
    $menus = array(
        'semua' => array(
            array('label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'link' => 'dashboard.php'),
        ),
        'admin' => array(
            array('label' => 'Data Master', 'icon' => 'bi-database', 'link' => '#' , 'sub' => array(
                array('label' => 'Pengguna', 'link' => 'master_user.php'),
                array('label' => 'Rekening AKUN', 'link' => 'master_akun.php'),
                array('label' => 'Program & Kegiatan', 'link' => 'master_kegiatan.php'),
                array('label' => 'SKPD/OPD', 'link' => 'master_skpd.php'),
            )),
        ),
        'penatausahaan' => array(
            array('label' => 'SPP', 'icon' => 'bi-file-earmark-text', 'link' => 'spp_list.php'),
            array('label' => 'SPM', 'icon' => 'bi-file-earmark-check', 'link' => 'spm_list.php'),
            array('label' => 'SP2D', 'icon' => 'bi-cash-coin', 'link' => 'sp2d_list.php'),
        ),
        'bendahara' => array(
            array('label' => 'Buku Bank', 'icon' => 'bi-bank', 'link' => 'buku_bank.php'),
            array('label' => 'Buku Pembantu', 'icon' => 'bi-journal-text', 'link' => 'buku_pembantu.php'),
            array('label' => 'SPJ', 'icon' => 'bi-folder-check', 'link' => 'spj_list.php'),
        ),
        'verifikator' => array(
            array('label' => 'Verifikasi SPP', 'icon' => 'bi-clipboard-check', 'link' => 'verifikasi_spp.php'),
        ),
    );

    $result = $menus['semua'];
    if (isset($menus[$role])) {
        $result = array_merge($result, $menus[$role]);
    }
    return $result;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPD Penatausahaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root { --primary: #1a3c6e; --primary-light: #2c5282; --sidebar-w: 250px; }
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w); background: var(--primary);
            color: #fff; padding-top: 0; overflow-y: auto; z-index: 100;
        }
        .sidebar .brand {
            padding: 20px; background: rgba(0,0,0,0.15);
            font-size: 1.1rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar .brand small { font-weight: 400; opacity: 0.7; font-size: 0.75rem; }
        .sidebar .nav-link { color: #cfd8e8; padding: 12px 20px; font-size: 0.9rem; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar .nav-link.active { background: var(--primary-light); color: #fff; border-left: 4px solid #fff; }
        .sidebar .nav-sub .nav-link { padding-left: 40px; font-size: 0.85rem; }
        .main-content { margin-left: var(--sidebar-w); padding: 20px; min-height: 100vh; }
        .topbar {
            background: #fff; padding: 10px 20px; margin-bottom: 20px;
            border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.06);
            display: flex; justify-content: space-between; align-items: center;
        }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .card-header { font-weight: 600; }
        .page-title { font-size: 1.4rem; font-weight: 700; color: var(--primary); }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-light); border-color: var(--primary-light); }
        .stat-card { border-radius: 10px; color: #fff; padding: 20px; }
        .stat-card .bi { font-size: 2.5rem; opacity: 0.8; }
        .table thead th { background: var(--primary); color: #fff; font-size: 0.85rem; }
        .table td { font-size: 0.88rem; }
        .footer-text { font-size: 0.8rem; color: #888; text-align: center; margin-top: 30px; }
        .badge-lg { font-size: 0.75rem; padding: 5px 10px; }
        .welcome-user { display: flex; align-items: center; gap: 12px; }
        .welcome-user .avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--primary); color: #fff; display: flex;
            align-items: center; justify-content: center; font-weight: 700;
        }
    </style>
</head>
<body>
<?php
// Jika user sudah login, tampilkan sidebar
if (is_logged_in()) $user = current_user();
?>
<?php if (is_logged_in()): ?>
<div class="sidebar">
    <div class="brand">
        <i class="bi bi-building"></i> SIPD Penatausahaan
        <br><small>Modul Keuangan Daerah</small>
    </div>
    <nav class="nav flex-column mt-2">
        <?php $menus = role_menu($user['role']); ?>
        <?php foreach ($menus as $menu): ?>
            <?php if (isset($menu['sub'])): ?>
                <a class="nav-link" href="#">
                    <i class="bi <?= $menu['icon'] ?>"></i> <?= $menu['label'] ?> <i class="bi bi-chevron-down float-end"></i>
                </a>
                <div class="nav-sub">
                    <?php foreach ($menu['sub'] as $sub): ?>
                        <a class="nav-link <?= ($current_page == $sub['link']) ? 'active' : '' ?>" href="<?= $sub['link'] ?>">
                            <i class="bi bi-dot"></i> <?= $sub['label'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <a class="nav-link <?= ($current_page == $menu['link']) ? 'active' : '' ?>" href="<?= $menu['link'] ?>">
                    <i class="bi <?= $menu['icon'] ?>"></i> <?= $menu['label'] ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</div>
<?php endif; ?>

<div class="main-content">
    <?php if (is_logged_in()): ?>
    <div class="topbar">
        <div class="page-title">
            <?php
                $title_map = array(
                    'dashboard.php' => 'Dashboard',
                    'master_user.php' => 'Manajemen Pengguna',
                    'master_akun.php' => 'Data Rekening (Akun)',
                    'master_kegiatan.php' => 'Program & Kegiatan',
                    'master_skpd.php' => 'Data SKPD/OPD',
                    'spp_list.php' => 'Daftar SPP',
                    'spm_list.php' => 'Daftar SPM',
                    'sp2d_list.php' => 'Daftar SP2D',
                    'buku_bank.php' => 'Buku Bank',
                    'buku_pembantu.php' => 'Buku Pembantu',
                    'spj_list.php' => 'SPJ',
                    'verifikasi_spp.php' => 'Verifikasi SPP',
                );
                echo isset($title_map[$current_page]) ? $title_map[$current_page] : 'SIPD Penatausahaan';
            ?>
        </div>
        <div class="welcome-user">
            <div class="avatar"><?= strtoupper(substr($user['nama'], 0, 1)) ?></div>
            <div>
                <strong><?= $user['nama'] ?></strong>
                <br><small class="text-muted"><?= ucfirst($user['role']) ?></small>
            </div>
            <a href="logout.php" class="btn btn-sm btn-outline-danger ms-2" title="Keluar">
                <i class="bi bi-box-arrow-right"></i> Keluar
            </a>
        </div>
    </div>
    <?php endif; ?>
    <!-- Konten dinamis -->
