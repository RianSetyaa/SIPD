<?php
if (!is_logged_in()) { header('Location: '.BASE_URL.'login.php'); exit; }
$u = current_user();
$page = basename($_SERVER['SCRIPT_NAME']);
$title_map = array(
    'dashboard.php'=>'Dashboard',
    'skp_list.php'=>'SKP (Surat Ketetapan Pajak)',
    'skp_tambah.php'=>'Input SKP',
    'tbp_list.php'=>'TBP (Tanda Bukti Penerimaan)',
    'tbp_tambah.php'=>'Input TBP',
    'sts_list.php'=>'STS (Surat Tanda Setoran)',
    'sts_tambah.php'=>'Input STS',
    'jurnal_list.php'=>'Jurnal Penerimaan',
    'buku_kas.php'=>'Buku Kas',
    'laporan.php'=>'Laporan & Pertanggungjawaban',
    'master_rekening.php'=>'Data Rekening Pendapatan',
    'master_akun.php'=>'Data Akun',
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($title_map[$page]) ? $title_map[$page].' - ' : '' ?><?= APP_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
body { background:#f4f6f9; }
.sidebar { position:fixed; top:0; left:0; height:100vh; width:250px; background:#1a3c6e; color:#fff; padding-top:15px; overflow-y:auto; z-index:100; }
.sidebar .brand { padding:10px 20px; font-weight:700; font-size:1.05rem; border-bottom:1px solid rgba(255,255,255,.12); }
.sidebar a.menu { display:block; color:#c9d6e8; padding:10px 22px; text-decoration:none; font-size:.92rem; border-left:3px solid transparent; }
.sidebar a.menu:hover, .sidebar a.menu.active { background:rgba(255,255,255,.08); color:#fff; border-left-color:#ffc107; }
.sidebar .group { font-size:.7rem; text-transform:uppercase; letter-spacing:1px; color:#8fa8c8; padding:14px 22px 4px; }
.sidebar a.logout { color:#f2a3a3; }
.main-content { margin-left:250px; padding:20px 25px; }
.topbar { background:#fff; border-radius:10px; padding:12px 20px; box-shadow:0 1px 4px rgba(0,0,0,.07); display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.card-modul { border:none; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.06); }
.table thead th { background:#f0f3f8; }
</style>
</head>
<body>

<aside class="sidebar">
  <div class="brand"><i class="bi bi-cash-stack"></i> SIPD Penerimaan</div>
  <div class="group">Menu Utama</div>
  <a class="menu <?= $page=='dashboard.php'?'active':'' ?>" href="<?= BASE_URL ?>dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>

  <?php if (in_array($u['role'], ['bendahara','admin','ppk']) || is_mahasiswa()): ?>
  <div class="group">Penerimaan (Dokumen)</div>
  <a class="menu <?= in_array($page,['skp_list.php','skp_tambah.php'])?'active':'' ?>" href="<?= BASE_URL ?>skp_list.php"><i class="bi bi-file-earmark-text"></i> SKP (Penerbitan)</a>
  <a class="menu <?= in_array($page,['tbp_list.php','tbp_tambah.php'])?'active':'' ?>" href="<?= BASE_URL ?>tbp_list.php"><i class="bi bi-receipt"></i> TBP (Penerimaan)</a>
  <a class="menu <?= in_array($page,['sts_list.php','sts_tambah.php'])?'active':'' ?>" href="<?= BASE_URL ?>sts_list.php"><i class=" bi bi-bank"></i> STS (Setoran)</a>
  <?php endif; ?>

  <div class="group">Akuntansi</div>
  <a class="menu <?= $page=='jurnal_list.php'?'active':'' ?>" href="<?= BASE_URL ?>jurnal_list.php"><i class="bi bi-journal-text"></i> Jurnal Penerimaan</a>
  <a class="menu <?= $page=='buku_kas.php'?'active':'' ?>" href="<?= BASE_URL ?>buku_kas.php"><i class="bi bi-book"></i> Buku Kas / Buku Pembantu</a>
  <a class="menu <?= $page=='laporan.php'?'active':'' ?>" href="<?= BASE_URL ?>laporan.php"><i class="bi bi-clipboard-data"></i> Laporan &amp; LPJ</a>

  <?php if (in_array($u['role'], ['admin','ppk']) || is_mahasiswa()): ?>
  <div class="group">Master Data</div>
  <a class="menu <?= $page=='master_rekening.php'?'active':'' ?>" href="<?= BASE_URL ?>master_rekening.php"><i class="bi bi-diagram-3"></i> Rekening Pendapatan</a>
  <?php endif; ?>

  <?php if ($u['role']=='admin' && !is_mahasiswa()): ?>
  <div class="group">Admin / Dosen</div>
  <a class="menu <?= $page=='daftar_mahasiswa.php'?'active':'' ?>" href="<?= BASE_URL ?>daftar_mahasiswa.php"><i class="bi bi-people"></i> Daftar Mahasiswa</a>
  <?php endif; ?>

  <hr style="border-color:rgba(255,255,255,.15)">
  <a class="menu logout" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right"></i> Logout (<?= htmlspecialchars($u['username']) ?>)</a>
</aside>

<div class="main-content">
  <div class="topbar">
    <div>
      <h5 class="mb-0"><?= isset($title_map[$page]) ? $title_map[$page] : 'SIPD Penerimaan' ?></h5>
      <small class="text-muted"><?= APP_FULL ?> — <?= $u['nama'] ?></small>
    </div>
    <div class="d-flex align-items-center gap-2">
      <?php if (is_mahasiswa()): ?>
      <div class="dropdown">
        <button class="btn btn-sm btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-person-workspace"></i> Peran: <strong><?= ucfirst(active_role()) ?></strong>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><span class="dropdown-header">Ganti peran aktif (role)</span></li>
          <li><a class="dropdown-item <?= active_role()=='bendahara'?'active':'' ?>" href="<?= BASE_URL ?>ganti_role.php?role=bendahara"><i class="bi bi-wallet2"></i> Bendahara Penerimaan</a></li>
          <li><a class="dropdown-item <?= active_role()=='verifikator'?'active':'' ?>" href="<?= BASE_URL ?>ganti_role.php?role=verifikator"><i class="bi bi-patch-check"></i> Verifikator</a></li>
          <li><a class="dropdown-item <?= active_role()=='ppk'?'active':'' ?>" href="<?= BASE_URL ?>ganti_role.php?role=ppk"><i class="bi bi-briefcase"></i> Pejabat Penatausahaan</a></li>
        </ul>
      </div>
      <?php endif; ?>
      <span class="badge bg-primary"><?= ucfirst($u['role']) ?></span>
      <?php if (is_mahasiswa() && $u['nim']): ?>
        <span class="badge bg-secondary"><?= htmlspecialchars($u['nim']) ?></span>
      <?php endif; ?>
    </div>
  </div>
  <?php flash(); ?>
