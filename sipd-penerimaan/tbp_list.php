<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();
require_role(['bendahara','admin','ppk']);
$u = current_user();
$scope = scope_skpd();

if (isset($_POST['simpan'])) {
    $tanggal = $_POST['tanggal'];
    $skp_id  = (int)$_POST['skp_id'];
    $jumlah  = str_replace(array('.', ' '), '', $_POST['jumlah']);
    $no_tbp  = buat_nomor('TBP', 'tbp');

    // validasi SKP
    $c = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM skp WHERE id=$skp_id"));
    if (!$c) { flash_error('SKP tidak ditemukan.'); }
    elseif (mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id FROM tbp WHERE skp_id=$skp_id"))) {
        flash_error('SKP ini sudah dibayar (TBP sudah ada).');
    } else {
        $sisa = $c['jumlah'] - 0;
        if ($jumlah > $c['jumlah']) { flash_error('Jumlah melebihi nilai SKP.'); }
        else {
            // terima full (pelunasan). Simpan jumlah = nilai SKP
            $jml = $c['jumlah'];
            if (mysqli_query($koneksi, "INSERT INTO tbp (no_tbp,tanggal,skpd_id,skp_id,jumlah) VALUES ('$no_tbp','$tanggal',{$u['skpd_id']},$skp_id,$jml)")) {
                $tbp_id = mysqli_insert_id($koneksi);
                catat_jurnal_tbp($tbp_id);
                flash_success("TBP $no_tbp tersimpan. Kas di Bendahara bertambah, Piutang berkurang.");
            } else flash_error('Gagal: '.mysqli_error($koneksi));
        }
    }
    header('Location: '.BASE_URL.'tbp_list.php'); exit;
}

if (isset($_GET['hapus'])) {
    $id=(int)$_GET['hapus'];
    hapus_jurnal_ref('TBP',$id);
    mysqli_query($koneksi,"DELETE FROM tbp WHERE id=$id");
    flash_success('TBP dihapus.');
    header('Location: '.BASE_URL.'tbp_list.php'); exit;
}

// SKP yang belum dibayar
$skp_belum = mysqli_query($koneksi, "SELECT s.*, r.kode, r.nama nama_rek FROM skp s
    JOIN rekening r ON r.id=s.rekening_id
    LEFT JOIN tbp t ON t.skp_id=s.id
    WHERE t.id IS NULL AND $scope ORDER BY s.tanggal");
$rows = mysqli_query($koneksi, "SELECT t.*, s.no_skp, s.wajib_pajak, r.kode, r.nama nama_rek
    FROM tbp t JOIN skp s ON s.id=t.skp_id JOIN rekening r ON r.id=s.rekening_id
    WHERE $scope ORDER BY t.tanggal DESC");
include __DIR__ . '/includes/header.php';
?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card card-modul">
      <div class="card-header bg-white fw-bold"><i class="bi bi-receipt"></i> Terima Pembayaran (TBP)</div>
      <div class="card-body">
        <form method="POST">
          <div class="mb-2"><label class="form-label small">Tanggal</label>
            <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required></div>
          <div class="mb-2"><label class="form-label small">Pilih SKP (Piutang)</label>
            <select name="skp_id" class="form-select form-select-sm" required>
              <option value="">-- pilih SKP --</option>
              <?php while($s=mysqli_fetch_assoc($skp_belum)): ?>
              <option value="<?= $s['id'] ?>"><?= $s['no_skp'] ?> — <?= htmlspecialchars($s['nama_rek']) ?> (<?= angka($s['jumlah']) ?>)</option>
              <?php endwhile; ?>
            </select></div>
          <button class="btn btn-success btn-sm w-100" name="simpan"><i class="bi bi-check2"></i> Terima &amp; Simpan TBP</button>
        </form>
        <div class="small text-muted mt-2">Nilai TBP otomatis = nilai SKP (pelunasan piutang).</div>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card card-modul">
      <div class="card-header bg-white fw-bold"><i class="bi bi-receipt"></i> Daftar TBP (Kas di Bendahara)</div>
      <div class="card-body table-responsive">
        <table class="table table-sm table-striped data-table">
          <thead><tr><th>No TBP</th><th>Tgl</th><th>SKP</th><th>Rekening</th><th class="text-end">Jumlah</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php while($r=mysqli_fetch_assoc($rows)): ?>
            <tr>
              <td><?= $r['no_tbp'] ?></td><td><?= tanggal($r['tanggal']) ?></td><td><?= $r['no_skp'] ?></td>
              <td><span class="small"><?= $r['kode'] ?></span><br><span class="text-muted small"><?= htmlspecialchars($r['nama_rek']) ?></span></td>
              <td class="text-end"><?= rupiah($r['jumlah']) ?></td>
              <td><a href="?hapus=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?');"><i class="bi bi-trash"></i></a></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
