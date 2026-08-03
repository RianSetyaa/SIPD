<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();
require_role(['admin','ppk']);
$u = current_user();
$scope = scope_skpd();

if (isset($_POST['simpan'])) {
    $kode = sanitize($_POST['kode']);
    $nama = sanitize($_POST['nama']);
    $level= (int)$_POST['level'];
    $induk= sanitize($_POST['induk_kode'] ?: '');
    $induk_sql = "NULL"; if($induk!=='') $induk_sql = "'$induk'";
    $skpd = (int)$u['skpd_id'];
    mysqli_query($koneksi, "INSERT INTO rekening (kode,nama,level,induk_kode,skpd_id) VALUES ('$kode','$nama',$level,$induk_sql,$skpd)");
    flash_success('Rekening ditambahkan.');
    header('Location: '.BASE_URL.'master_rekening.php'); exit;
}
if (isset($_GET['hapus'])) {
    mysqli_query($koneksi, "DELETE FROM rekening WHERE id=".(int)$_GET['hapus']." AND $scope");
    flash_success('Dihapus.');
    header('Location: '.BASE_URL.'master_rekening.php'); exit;
}
$rows = mysqli_query($koneksi, "SELECT * FROM rekening WHERE $scope ORDER BY kode");
include __DIR__ . '/includes/header.php';
?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card card-modul">
      <div class="card-header bg-white fw-bold"><i class="bi bi-diagram-3"></i> Tambah Rekening</div>
      <div class="card-body">
        <form method="POST">
          <div class="mb-2"><label class="form-label small">Kode</label><input name="kode" class="form-control form-control-sm" placeholder="4.1.1.02.05"></div>
          <div class="mb-2"><label class="form-label small">Nama</label><input name="nama" class="form-control form-control-sm" required></div>
          <div class="mb-2"><label class="form-label small">Level (4=rinci)</label><select name="level" class="form-select form-select-sm">
            <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4" selected>4</option></select></div>
          <div class="mb-3"><label class="form-label small">Induk Kode</label><input name="induk_kode" class="form-control form-control-sm" placeholder="opsional"></div>
          <button class="btn btn-primary btn-sm w-100" name="simpan">Simpan</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card card-modul">
      <div class="card-header bg-white fw-bold">Data Rekening Pendapatan</div>
      <div class="card-body table-responsive">
        <table class="table table-sm table-striped data-table">
          <thead><tr><th>Kode</th><th>Nama</th><th>Level</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php while($r=mysqli_fetch_assoc($rows)): ?>
            <tr><td><code><?= $r['kode'] ?></code></td><td><?= htmlspecialchars($r['nama']) ?></td><td><?= $r['level'] ?></td>
              <td><a href="?hapus=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?');"><i class="bi bi-trash"></i></a></td></tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
