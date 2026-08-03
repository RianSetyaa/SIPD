<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_role('admin');

if (isset($_POST['simpan'])) {
    $kode = sanitize($_POST['kode_skpd']);
    $nama = sanitize($_POST['nama_skpd']);
    mysqli_query($koneksi, "INSERT INTO skpd (kode_skpd, nama_skpd) VALUES ('$kode','$nama')");
    $_SESSION['success'] = 'SKPD ditambahkan.';
    header('Location: master_skpd.php');
    exit();
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM skpd WHERE id=$id");
    $_SESSION['success'] = 'SKPD dihapus.';
    header('Location: master_skpd.php');
    exit();
}

$skpd = mysqli_query($koneksi, "SELECT * FROM skpd ORDER BY kode_skpd");
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h5 class="text-muted">Data SKPD / OPD</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="bi bi-plus-circle"></i> Tambah SKPD</button>
    </div>

    <?php flash(); ?>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead><tr><th>No</th><th>Kode SKPD</th><th>Nama SKPD</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php $no=1; while ($s = mysqli_fetch_assoc($skpd)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><span class="badge bg-dark"><?= $s['kode_skpd'] ?></span></td>
                        <td><?= $s['nama_skpd'] ?></td>
                        <td><a href="master_skpd.php?hapus=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus SKPD ini?')"><i class="bi bi-trash"></i></a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
            <div class="modal-header"><h5 class="modal-title">Tambah SKPD</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Kode SKPD</label><input name="kode_skpd" class="form-control" placeholder="cth: 1.05.01" required></div>
                <div class="mb-3"><label class="form-label">Nama SKPD</label><input name="nama_skpd" class="form-control" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" name="simpan" class="btn btn-primary">Simpan</button></div>
        </form>
    </div></div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
