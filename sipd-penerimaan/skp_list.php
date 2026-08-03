<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();
require_role(['bendahara','admin','ppk']);
$u = current_user();

// TAMBAH SKP
if (isset($_POST['simpan'])) {
    $tanggal  = $_POST['tanggal'];
    $rekening = (int)$_POST['rekening_id'];
    $wp       = sanitize($_POST['wajib_pajak']);
    $jumlah   = str_replace(array('.', ' '), '', $_POST['jumlah']);
    $no_skp   = buat_nomor('SKP', 'skp');

    $sql = "INSERT INTO skp (no_skp, tanggal, skpd_id, rekening_id, wajib_pajak, jumlah)
            VALUES ('$no_skp','$tanggal',{$u['skpd_id']},$rekening,'$wp',$jumlah)";
    if (mysqli_query($koneksi, $sql)) {
        $skp_id = mysqli_insert_id($koneksi);
        catat_jurnal_skp($skp_id); // jurnal otomatis
        flash_success("SKP $no_skp tersimpan. Piutang terakui otomatis.");
    } else {
        flash_error('Gagal simpan: '.mysqli_error($koneksi));
    }
    header('Location: '.BASE_URL.'skp_list.php'); exit;
}

// HAPUS SKP
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    hapus_jurnal_ref('SKP', $id);
    mysqli_query($koneksi, "DELETE FROM skp WHERE id=$id");
    flash_success('SKP dihapus.');
    header('Location: '.BASE_URL.'skp_list.php'); exit;
}

$scope = scope_skpd(); // filter data per pengguna (mahasiswa) / semua (admin)

$rows = mysqli_query($koneksi, "SELECT s.*, r.kode, r.nama AS nama_rek
    FROM skp s JOIN rekening r ON r.id=s.rekening_id
    WHERE $scope ORDER BY s.tanggal DESC");
include __DIR__ . '/includes/header.php';
?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card card-modul">
      <div class="card-header bg-white fw-bold"><i class="bi bi-plus-circle"></i> Terbitkan SKP Baru</div>
      <div class="card-body">
        <form method="POST">
          <div class="mb-2"><label class="form-label small">Tanggal</label>
            <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required></div>
          <div class="mb-2"><label class="form-label small">Rekening Pendapatan</label>
            <select name="rekening_id" class="form-select form-select-sm" required>
              <option value="">-- pilih --</option>
              <?php
              $rek = mysqli_query($koneksi, "SELECT * FROM rekening WHERE level=4 AND (skpd_id={$u['skpd_id']} OR skpd_id=0 OR $scope) ORDER BY kode");
              while($r=mysqli_fetch_assoc($rek)):
              ?><option value="<?= $r['id'] ?>"><?= $r['kode'] ?> — <?= htmlspecialchars($r['nama']) ?></option><?php endwhile; ?>
            </select></div>
          <div class="mb-2"><label class="form-label small">Wajib Pajak</label>
            <input type="text" name="wajib_pajak" class="form-control form-control-sm" value="Wajib Pajak"></div>
          <div class="mb-3"><label class="form-label small">Nilai (Rp)</label>
            <input type="text" name="jumlah" class="form-control form-control-sm money" required></div>
          <button class="btn btn-primary btn-sm w-100" name="simpan"><i class="bi bi-check2"></i> Simpan SKP</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card card-modul">
      <div class="card-header bg-white fw-bold"><i class="bi bi-file-earmark-text"></i> Daftar SKP (Piutang)</div>
      <div class="card-body table-responsive">
        <table class="table table-sm table-striped data-table">
          <thead><tr><th>No</th><th>No SKP</th><th>Tgl</th><th>Rekening</th><th>Wajib Pajak</th><th class="text-end">Jumlah</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php $i=1; while($r=mysqli_fetch_assoc($rows)): ?>
            <tr>
              <td><?= $i++ ?></td><td><?= $r['no_skp'] ?></td><td><?= tanggal($r['tanggal']) ?></td>
              <td><span class="small"><?= $r['kode'] ?></span><br><span class="text-muted small"><?= htmlspecialchars($r['nama_rek']) ?></span></td>
              <td><?= htmlspecialchars($r['wajib_pajak']) ?></td>
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
