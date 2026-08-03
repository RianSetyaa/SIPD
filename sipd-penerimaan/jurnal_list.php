<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();

$rows = mysqli_query($koneksi, "SELECT * FROM jurnal ORDER BY tanggal, id");
include __DIR__ . '/includes/header.php';
?>
<div class="card card-modul">
  <div class="card-header bg-white fw-bold"><i class="bi bi-journal-text"></i> Jurnal Penerimaan (Otomatis)</div>
  <div class="card-body table-responsive">
    <table class="table table-sm table-striped data-table" style="width:100%">
      <thead><tr><th>Tgl</th><th>Dokumen</th><th>Jen</th><th>Uraian / Akun</th><th class="text-end">Debet</th><th class="text-end">Kredit</th></tr></thead>
      <tbody>
      <?php while($j=mysqli_fetch_assoc($rows)):
        // kumpulkan rincian jurnal
        $det = array();
        $qr = mysqli_query($koneksi, "SELECT jr.* FROM jurnal_rincian jr WHERE jr.jurnal_id={$j['id']} ORDER BY jr.id");
        while($d=mysqli_fetch_assoc($qr)) $det[] = $d;
        $n = count($det); if($n==0) $n=1;
        $ci = 0;
        foreach($det as $d): ?>
        <tr>
          <?php if($ci==0): ?>
            <td rowspan="<?= $n ?>" class="align-middle"><?= tanggal($j['tanggal']) ?></td>
            <td rowspan="<?= $n ?>" class="align-middle"><?= $j['no_dokumen'] ?></td>
            <td rowspan="<?= $n ?>" class="align-middle"><span class="badge bg-secondary"><?= $j['jenis'] ?></span></td>
          <?php endif; ?>
          <td><span class="badge bg-light text-dark border"><?= $d['basis'] ?></span> <?= htmlspecialchars($d['akun']) ?></td>
          <td class="text-end"><?= $d['debet']>0?angka($d['debet']):'-' ?></td>
          <td class="text-end"><?= $d['kredit']>0?angka($d['kredit']):'-' ?></td>
        </tr>
        <?php $ci++; endforeach; endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>

