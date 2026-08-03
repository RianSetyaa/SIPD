<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();
$u = current_user();
$scope = scope_skpd_alias('st');

// Ambil STS sebagai penerimaan kas (per dagang), dan TBP (kas bendahara)
$sts = mysqli_query($koneksi, "SELECT st.id, st.tanggal, st.no_sts, st.jumlah, r.nama nama_rek, r.kode FROM sts st
    JOIN tbp t ON t.id=st.tbp_id JOIN skp s ON s.id=t.skp_id JOIN rekening r ON r.id=s.rekening_id
    WHERE $scope ORDER BY st.tanggal, st.id");

$kumpul = array();
while($r=mysqli_fetch_assoc($sts)) $kumpul[] = $r;
usort($kumpul, function($a,$b){ return strtotime($a['tanggal'])-strtotime($b['tanggal']); });

include __DIR__ . '/includes/header.php';
?>
<div class="row g-3">
  <div class="col-lg-6">
    <div class="card card-modul">
      <div class="card-header bg-white fw-bold"><i class="bi bi-book"></i> Buku Kas Penerimaan (Kas Daerah)</div>
      <div class="card-body table-responsive">
        <table class="table table-sm table-striped">
          <thead><tr><th>Tgl</th><th>No STS</th><th>Uraian</th><th class="text-end">Debet</th><th class="text-end">Saldo</th></tr></thead>
          <tbody>
          <?php $saldo=0; foreach($kumpul as $r): $saldo+=$r['jumlah']; ?>
            <tr>
              <td><?= tanggal($r['tanggal']) ?></td><td><?= $r['no_sts'] ?></td>
              <td><span class="small"><?= $r['kode'] ?></span> — <?= htmlspecialchars($r['nama_rek']) ?></td>
              <td class="text-end"><?= rupiah($r['jumlah']) ?></td><td class="text-end"><?= rupiah($saldo) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-modul">
      <div class="card-header bg-white fw-bold"><i class="bi bi-book"></i> Rekapitulasi Penerimaan per Jenis</div>
      <div class="card-body">
        <?php
        $rekap = mysqli_query($koneksi, "SELECT r.kode, r.nama, SUM(st.jumlah) total FROM sts st
            JOIN tbp t ON t.id=st.tbp_id JOIN skp s ON s.id=t.skp_id JOIN rekening r ON r.id=s.rekening_id
            WHERE $scope GROUP BY r.id, r.kode, r.nama ORDER BY r.kode");
        $grand=0;
        ?>
        <table class="table table-sm">
          <thead><tr><th>Rek</th><th>Jenis Penerimaan</th><th class="text-end">Total</th></tr></thead>
          <tbody>
          <?php while($r=mysqli_fetch_assoc($rekap)): $grand+=$r['total']; ?>
            <tr><td><?= $r['kode'] ?></td><td><?= htmlspecialchars($r['nama']) ?></td><td class="text-end"><?= rupiah($r['total']) ?></td></tr>
          <?php endwhile; ?>
          </tbody>
          <tfoot><tr class="fw-bold"><td colspan="2">Total Realisasi</td><td class="text-end"><?= rupiah($grand) ?></td></tr></tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
