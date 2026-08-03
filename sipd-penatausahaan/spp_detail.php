<?php
require_once __DIR__ . '/includes/header.php';
require_login();

$id = (int)$_GET['id'];
$spp = mysqli_query($koneksi, "SELECT s.*, a.kode_akun, a.nama_akun, k.nama_kegiatan, u.nama as pembuat 
    FROM spp s 
    LEFT JOIN rekening_akun a ON s.akun_id = a.id 
    LEFT JOIN kegiatan k ON s.kegiatan_id = k.id 
    LEFT JOIN users u ON s.created_by = u.id 
    WHERE s.id=$id")->fetch_assoc();

if (!$spp) { header('Location: spp_list.php'); exit(); }

$dokumen = mysqli_query($koneksi, "SELECT * FROM spp_dokumen WHERE spp_id=$id");

// Upload dokumen
if (isset($_POST['upload_dokumen'])) {
    $nama_dok = sanitize($_POST['nama_dokumen']);
    $file = $_FILES['file_dokumen'];
    $result = upload_file($file, ROOT_PATH . '/uploads/dokumen/');
    if ($result['status']) {
        mysqli_query($koneksi, "INSERT INTO spp_dokumen (spp_id, nama_dokumen, file_path) VALUES ($id,'$nama_dok','dokumen/{$result['nama']}')");
        $_SESSION['success'] = 'Dokumen berhasil diupload.';
    } else {
        $_SESSION['error'] = $result['pesan'];
    }
    header('Location: spp_detail.php?id='.$id);
    exit();
}

if (isset($_GET['hapus_dok'])) {
    $dok_id = (int)$_GET['hapus_dok'];
    $dok = mysqli_query($koneksi, "SELECT * FROM spp_dokumen WHERE id=$dok_id")->fetch_assoc();
    @unlink(ROOT_PATH . '/uploads/' . $dok['file_path']);
    mysqli_query($koneksi, "DELETE FROM spp_dokumen WHERE id=$dok_id");
    $_SESSION['success'] = 'Dokumen dihapus.';
    header('Location: spp_detail.php?id='.$id);
    exit();
}
?>
<div class="container-fluid">
    <?php flash(); ?>
    <div class="d-flex justify-content-between mb-3">
        <div>
            <a href="spp_list.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="mt-2 mb-0">Detail SPP: <strong><?= $spp['no_spp'] ?></strong></h4>
        </div>
        <span class="badge fs-6 bg-<?= ($spp['status']=='disetujui'?'success':($spp['status']=='ditolak'?'danger':'warning')) ?>"><?= ucfirst($spp['status']) ?></span>
    </div>

    <div class="row g-3">
        <!-- Informasi SPP -->
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header bg-white"><strong><i class="bi bi-file-earmark-text text-primary"></i> Informasi SPP</strong></div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th width="35%">Nomor SPP</th><td><strong><?= $spp['no_spp'] ?></strong></td></tr>
                        <tr><th>Tanggal</th><td><?= tanggal_panjang($spp['tgl_spp']) ?></td></tr>
                        <tr><th>Jenis</th><td><span class="badge bg-info text-dark"><?= $spp['jenis'] ?></span></td></tr>
                        <tr><th>Kegiatan</th><td><?= $spp['nama_kegiatan'] ?: '-' ?></td></tr>
                        <tr><th>Rekening Akun</th><td><?= $spp['kode_akun'] ?? '-' ?> - <?= $spp['nama_akun'] ?? '-' ?></td></tr>
                        <tr><th>Uraian</th><td><?= $spp['uraian'] ?: '-' ?></td></tr>
                        <tr><th>Jumlah</th><td><strong class="text-success"><?= rupiah($spp['jumlah']) ?></strong></td></tr>
                        <tr><th>Dibuat oleh</th><td><?= $spp['pembuat'] ?> (<?= $spp['created_at'] ?>)</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Dokumen pendukung -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white"><strong><i class="bi bi-paperclip text-primary"></i> Dokumen Pendukung</strong></div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="mb-3">
                        <div class="mb-2">
                            <label class="form-label">Nama Dokumen</label>
                            <input type="text" name="nama_dokumen" class="form-control" placeholder="cth: Kwitansi, Nota, Kontrak, SPP..." required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">File</label>
                            <input type="file" name="file_dokumen" class="form-control" required>
                            <div class="form-text">JPG/PNG/PDF maks 5MB</div>
                        </div>
                        <button type="submit" name="upload_dokumen" class="btn btn-primary w-100"><i class="bi bi-upload"></i> Upload</button>
                    </form>
                    <hr>
                    <ul class="list-group">
                        <?php if (mysqli_num_rows($dokumen) > 0): while ($d = mysqli_fetch_assoc($dokumen)): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-file-earmark-pdf text-danger"></i> <?= $d['nama_dokumen'] ?>
                                <br><small class="text-muted"><?= $d['created_at'] ?></small>
                            </div>
                            <div>
                                <a href="<?= UPLOAD_URL . $d['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                                <a href="spp_detail.php?id=<?= $id ?>&hapus_dok=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus dokumen?')"><i class="bi bi-trash"></i></a>
                            </div>
                        </li>
                        <?php endwhile; else: ?>
                            <li class="list-group-item text-center text-muted">Belum ada dokumen</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
