<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();

$u = current_user();
$skpd_scope = ($u['role']=='admin' && !is_mahasiswa()) ? '' : "WHERE skpd_id={$u['skpd_id']}";

$tot_skp = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT IFNULL(SUM(jumlah),0) t, COUNT(*) c FROM skp $skpd_scope"));
$tot_tbp = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT IFNULL(SUM(jumlah),0) t, COUNT(*) c FROM tbp $skpd_scope"));
$tot_sts = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT IFNULL(SUM(jumlah),0) t, COUNT(*) c FROM sts $skpd_scope"));

include __DIR__ . '/includes/header.php';
?>
<div class="row g-3 mb-4">
  <div class="col-md-3 col-6">
    <div class="card card-modul text-white bg-primary"><div class="card-body">
      <div class="fs-5"><i class="bi bi-file-earmark-text"></i></div>
      <h3><?= rupiah($tot_skp['t']) ?></h3><div>SKP Diterbitkan (<?= $tot_skp['c'] ?> dok)</div>
    </div></div>
  </div>
  <div class="col-md-3 col-6">
    <div class="card card-modul text-white bg-success"><div class="card-body">
      <div class="fs-5"><i class="bi bi-receipt"></i></div>
      <h3><?= rupiah($tot_tbp['t']) ?></h3><div>TBP Diterima (<?= $tot_tbp['c'] ?> dok)</div>
    </div></div>
  </div>
  <div class="col-md-3 col-6">
    <div class="card card-modul text-white bg-warning"><div class="card-body">
      <div class="fs-5"><i class="bi bi-bank"></i></div>
      <h3><?= rupiah($tot_sts['t']) ?></h3><div>STS Disetor (<?= $tot_sts['c'] ?> dok)</div>
    </div></div>
  </div>
  <div class="col-md-3 col-6">
    <div class="card card-modul text-white bg-info"><div class="card-body">
      <div class="fs-5"><i class="bi bi-journal-text"></i></div>
      <h3><?= rupiah($tot_sts['t']) ?></h3><div>Pendapatan LRA Terealisasi</div>
    </div></div>
  </div>
</div>

<div class="card card-modul mb-4">
  <div class="card-header bg-white fw-bold">Alur Penatausahaan Penerimaan Pendapatan SKPKD</div>
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-center gap-2">
      <span class="badge bg-primary p-2">1. Terbit SKP<br><small>Piutang timbul</small></span>
      <i class="bi bi-arrow-right"></i>
      <span class="badge bg-success p-2">2. Terima TBP<br><small>Kas Bendahara</small></span>
      <i class="bi bi-arrow-right"></i>
      <span class="badge bg-warning p-2">3. Terbit STS<br><small>Kas Kas Daerah</small></span>
      <i class="bi bi-arrow-right"></i>
      <span class="badge bg-info p-2">4. Jurnal &amp; Buku<br><small>Otomatis</small></span>
    </div>
    <hr>
    <div class="alert alert-light border small mb-0">
      <strong>Konsep:</strong> Saat <em>SKP</em> diterbitkan, bendahara mengakui <strong>Piutang</strong> (basis akrual). Saat wajib pajak membayar lewat <em>TBP</em>, kas di bendahara bertambah dan piutang berkurang. Saat disetorkan ke Kas Daerah via <em>STS</em>, aplikasi mencatat penerimaan kas ke Kas Daerah sekaligus mengakui <strong>Pendapatan LRA</strong>.
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
