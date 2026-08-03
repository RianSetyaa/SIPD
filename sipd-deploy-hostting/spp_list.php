<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_role(array('penatausahaan','bendahara','admin'));

// Hapus SPP
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // cek apakah sudah punya SPM
    $cek = mysqli_query($koneksi, "SELECT id FROM spm WHERE spp_id=$id");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['error'] = 'SPP tidak bisa dihapus karena sudah ada SPM.';
    } else {
        mysqli_query($koneksi, "DELETE FROM spp WHERE id=$id");
        $_SESSION['success'] = 'SPP berhasil dihapus.';
    }
    header('Location: spp_list.php');
    exit();
}

// Ubah status SPP (ajukan)
if (isset($_GET['ajukan'])) {
    $id = (int)$_GET['ajukan'];
    mysqli_query($koneksi, "UPDATE spp SET status='diajukan' WHERE id=$id");
    $_SESSION['success'] = 'SPP diajukan untuk verifikasi.';
    header('Location: spp_list.php');
    exit();
}

// Proses buat SPP
if (isset($_POST['simpan'])) {
    $no_spp = buat_nomor('SPP-', 'spp', 'no_spp');
    $tgl = sanitize($_POST['tgl_spp']);
    $jenis = sanitize($_POST['jenis']);
    $uraian = sanitize($_POST['uraian']);
    $akun_id = (int)$_POST['akun_id'];
    $jumlah = str_replace('.', '', $_POST['jumlah']);
    $created_by = $user['id'];

    $q = "INSERT INTO spp (no_spp, tgl_spp, jenis, uraian, akun_id, jumlah, status, created_by)
          VALUES ('$no_spp','$tgl','$jenis','$uraian',$akun_id,$jumlah,'draft',$created_by)";
    if (mysqli_query($koneksi, $q)) {
        $_SESSION['success'] = 'SPP berhasil dibuat dengan nomor ' . $no_spp;
    } else {
        $_SESSION['error'] = 'Gagal menyimpan: ' . mysqli_error($koneksi);
    }
    header('Location: spp_list.php');
    exit();
}

// Cari parameter status filter
$filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$where = $filter ? "WHERE status='$filter'" : '';
$query = "SELECT spp.*, u.nama as pembuat 
          FROM spp 
          LEFT JOIN users u ON spp.created_by = u.id 
          $where 
          ORDER BY spp.created_at DESC";
$spps = mysqli_query($koneksi, $query);

// Data master untuk form
$akuns = mysqli_query($koneksi, "SELECT * FROM rekening_akun WHERE jenis='BELANJA' ORDER BY kode_akun");
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
        <h5 class="text-muted">Surat Permintaan Pembayaran (SPP)</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSPP">
            <i class="bi bi-plus-circle"></i> Buat SPP
        </button>
    </div>

    <?php flash(); ?>

    <!-- Filter status -->
    <div class="mb-3">
        <a href="?status=" class="btn btn-sm btn-outline-secondary <?= !$filter ? 'active' : '' ?>">Semua</a>
        <a href="?status=draft" class="btn btn-sm btn-outline-secondary <?= $filter=='draft' ? 'active' : '' ?>">Draft</a>
        <a href="?status=diajukan" class="btn btn-sm btn-outline-secondary <?= $filter=='diajukan' ? 'active' : '' ?>">Diajukan</a>
        <a href="?status=diverifikasi" class="btn btn-sm btn-outline-secondary <?= $filter=='diverifikasi' ? 'active' : '' ?>">Diverifikasi</a>
        <a href="?status=disetujui" class="btn btn-sm btn-outline-secondary <?= $filter=='disetujui' ? 'active' : '' ?>">Disetujui</a>
        <a href="?status=ditolak" class="btn btn-sm btn-outline-secondary <?= $filter=='ditolak' ? 'active' : '' ?>">Ditolak</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>No. SPP</th><th>Tanggal</th><th>Jenis</th><th>Uraian</th><th>Jumlah</th><th>Dibuat oleh</th><th>Status</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($s = mysqli_fetch_assoc($spps)): ?>
                    <tr>
                        <td><strong><?= $s['no_spp'] ?></strong></td>
                        <td><?= tanggal($s['tgl_spp']) ?></td>
                        <td><span class="badge bg-info text-dark"><?= $s['jenis'] ?></span></td>
                        <td><?= substr($s['uraian'] ?? '-', 0, 40) ?><?= strlen($s['uraian'] ?? '') > 40 ? '...' : '' ?></td>
                        <td><?= rupiah($s['jumlah']) ?></td>
                        <td><?= $s['pembuat'] ?: '-' ?></td>
                        <td>
                            <?php
                            $cls = array('draft'=>'secondary','diajukan'=>'warning','diverifikasi'=>'info','disetujui'=>'success','ditolak'=>'danger');
                            $c = (in_array($s['status'], array_keys($cls))) ? $cls[$s['status']] : 'secondary';
                            ?>
                            <span class="badge bg-<?= $c ?>"><?= ucfirst($s['status']) ?></span>
                        </td>
                        <td>
                            <a href="spp_detail.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            <?php if ($s['status'] == 'draft'): ?>
                                <a href="spp_list.php?ajukan=<?= $s['id'] ?>" class="btn btn-sm btn-outline-warning" title="Ajukan verifikasi"><i class="bi bi-send"></i></a>
                                <a href="spp_list.php?hapus=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus SPP?')"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Buat SPP -->
<div class="modal fade" id="modalSPP" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Buat SPP Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal SPP</label>
                        <input type="date" name="tgl_spp" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jenis SPP</label>
                        <select name="jenis" class="form-select">
                            <option value="LS">LS (Langsung)</option>
                            <option value="GU">GU (Ganti Uang)</option>
                            <option value="TU">TU (Tambah Uang)</option>
                            <option value="UP">UP (Uang Persediaan)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rekening Akun</label>
                        <select name="akun_id" class="form-select" required>
                            <option value="">-- Pilih Akun --</option>
                            <?php while ($a = mysqli_fetch_assoc($akuns)): ?>
                                <option value="<?= $a['id'] ?>"><?= $a['kode_akun'] ?> - <?= $a['nama_akun'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="text" name="jumlah" class="form-control text-end" placeholder="cth: 1000000" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Uraian / Pembelian</label>
                        <textarea name="uraian" class="form-control" rows="2" placeholder="Uraian keperluan bayar"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="simpan" class="btn btn-primary">Simpan SPP</button>
            </div>
        </form>
    </div></div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
