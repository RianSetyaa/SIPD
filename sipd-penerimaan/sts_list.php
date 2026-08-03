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
    $tbp_id  = (int)$_POST['tbp_id'];
    $bank    = sanitize($_POST['bank']);
    $no_sts  = buat_nomor('STS', 'sts');

    $c = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM tbp WHERE id=$tbp_id"));
    if (!$c) flash_error('TBP tidak ditemukan.');
    elseif (mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id FROM sts WHERE tbp_id=$tbp_id")))
        flash_error('TBP ini sudah disetorkan (STS sudah ada).');
    else {
        if (mysqli_query($koneksi, "INSERT INTO sts (no_sts,tanggal,skpd_id,tbp_id,jumlah,bank) VALUES ('$no_sts','$tanggal',{$u['skpd_id']},$tbp_id,{$c['jumlah']},'$bank')")) {
            $sts_id = mysqli_insert_id($koneksi);
            catat_jurnal_sts($sts_id);
            flash_success("STS $no_sts tersimpan. Kas Daerah bertambah, Pendapatan LRA terakui.");
        } else flash_error('Gagal: '.mysqli_error($koneksi));
    }
    header('Location: '.BASE_URL.'sts_list.php'); exit;
}

if (isset($_GET['hapus'])) {
    $id=(int)$_GET['hapus'];
    hapus_jurnal_ref('STS',$id);
    mysqli_query($koneksi,"DELETE FROM sts WHERE id=$id");
    flash_success('STS dihapus.');
    header('Location: '.BASE_URL.'sts_list.php'); exit;
}

$tbp_belum = mysqli_query($koneksi, "SELECT t.*, s.no_skp, s.rekening_id, r.kode, r.nama nama_rek FROM tbp t
    JOIN skp s ON s.id=t.skp_id JOIN rekening r ON r.id=s.rekening_id
    LEFT JOIN sts st ON st.tbp_id=t.id
    WHERE st.id IS NULL AND $scope ORDER BY t.tanggal");
$rows = mysqli_query($koneksi, "SELECT st.*, t.no_tbp, s.no_skp, r.kode, r.nama nama_rek FROM sts st
    JOIN tbp t ON t.id=st.tbp_id JOIN skp s ON s.id=t.skp_id JOIN rekening r ON r.id=s.rekening_id
    WHERE $scope ORDER BY st.tanggal DESC");
include __DIR__ . '/includes/header.php';
?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card card-modul">
      <div class="card-header bg-white fw-bold"><i class="bi bi-bank"></i> Setor ke Kas Daerah (STS)</div>
      <div class="card-body">
        <form method="POST">
          <div class="mb-2"><label class="form-label small">Tanggal Setor</label>
            <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required></div>
          <div class="mb-2"><label class="form-label small">Pilih TBP (diterima)</label>
            <select name="tbp_id" class="form-select form-select-sm" required>
              <option value="">-- pilih TBP --</option>
              <?php while($t=mysqli_fetch_assoc($tbp_belum)): ?>
              <option value="<?= $t['id'] ?>"><?= $t['no_tbp'] ?> — <?= htmlspecialchars($t['nama_rek']) ?> (<?= angka($t['jumlah']) ?>)</option>
              <?php endwhile; ?>
            </select></div>
          <div class="mb-3"><label class="form-label small">Bank</label>
            <input type="text" name="bank" class="form-control form-control-sm" value="BPD Jabar"></div>
          <button class="btn btn-warning btn-sm w-100" name="simpan"><i class="bi bi-check2"></i> Setor &amp; Simpan STS</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card card-modul">
      <div class="card-header bg-white fw-bold"><i class="bi bi-bank"></i> Daftar STS (Kas Daerah)</div>
      <div class="card-body table-responsive">
        <table class="table table-sm table-striped data-table">
          <thead><tr><th>No STS</th><th>Tgl</th><th>TBP</th><th>Rekening</th><th>Bank</th><th class="text-end">Jumlah</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php while($r=mysqli_fetch_assoc($rows)): ?>
            <tr>
              <td><?= $r['no_sts'] ?></td><td><?= tanggal($r['tanggal']) ?></td><td><?= $r['no_tbp'] ?></td>
              <td><span class="small"><?= $r['kode'] ?></span><br><span class="text-muted small"><?= htmlspecialchars($r['nama_rek']) ?></span></td>
              <td><?= htmlspecialchars($r['bank']) ?></td><td class="text-end"><?= rupiah($r['jumlah']) ?></td>
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
