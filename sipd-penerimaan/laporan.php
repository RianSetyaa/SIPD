<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();

// Filter bulan
$tahun = $_GET['tahun'] ?? date('Y');
$bulan = $_GET['bulan'] ?? '';

$where = "";
if ($bulan !== '' && $bulan !== '') {
    $where = "AND MONTH(st.tanggal)=".(int)$bulan." AND YEAR(st.tanggal)=".(int)$tahun;
}
$sts = mysqli_query($koneksi, "SELECT st.*, t.no_tbp, s.no_skp, s.wajib_pajak, r.kode, r.nama nama_rek, s.rekening_id
    FROM sts st JOIN tbp t ON t.id=st.tbp_id JOIN skp s ON s.id=t.skp_id JOIN rekening r ON r.id=s.rekening_id
    WHERE 1=1 $where ORDER BY st.tanggal");

include __DIR__ . '/includes/header.php';
?>
<div class="card card-modul mb-3">
  <div class="card-header bg-white fw-bold"><i class="bi bi-clipboard-data"></i> Laporan Penatausahaan &amp; LPJ Bendahara Penerimaan</div>
  <div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-auto"><label class="form-label small mb-0">Tahun</label>
        <select name="tahun" class="form-select form-select-sm">
          <?php for($y=date('Y'); $y>=2000; $y--): ?><option value="<?= $y ?>" <?= $y==$tahun?'selected':'' ?>><?= $y ?></option><?php endfor; ?>
        </select></div>
      <div class="col-auto"><label class="form-label small mb-0">Bulan</label>
        <select name="bulan" class="form-select form-select-sm">
          <option value="">Semua</option>
          <?php foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i=>$b): ?>
            <option value="<?= $i+1 ?>" <?= $bulan==($i+1)?'selected':'' ?>><?= $b ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button></div>
      <div class="col-auto"><a class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Cetak</a></div>
    </form>
  </div>
</div>

<style>@media print{.sidebar,.topbar,.card-header form,.btn{display:none!important}.main-content{margin-left:0!important}}</style>

<div class="card card-modul">
  <div class="card-header bg-white fw-bold">Register Penerimaan SKPKD (SKP → TBP → STS)</div>
  <div class="card-body table-responsive">
    <table class="table table-sm table-striped">
      <thead><tr><th>Tgl STS</th><th>No STS</th><th>No TBP</th><th>No SKP</th><th>Wajib Pajak</th><th>Rekening</th><th class="text-end">Jumlah</th></tr></thead>
      <tbody>
      <?php $total=0; while($r=mysqli_fetch_assoc($sts)): $total+=$r['jumlah']; ?>
        <tr>
          <td><?= tanggal($r['tanggal']) ?></td><td><?= $r['no_sts'] ?></td><td><?= $r['no_tbp'] ?></td>
          <td><?= $r['no_skp'] ?></td><td><?= htmlspecialchars($r['wajib_pajak']) ?></td>
          <td><span class="small"><?= $r['kode'] ?></span> — <?= htmlspecialchars($r['nama_rek']) ?></td>
          <td class="text-end"><?= rupiah($r['jumlah']) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
      <tfoot><tr class="fw-bold"><td colspan="6" class="text-end">Total Penerimaan</td><td class="text-end"><?= rupiah($total) ?></td></tr></tfoot>
    </table>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
