<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_role('admin');

// Simpan akun
if (isset($_POST['simpan'])) {
    $kode_akun = sanitize($_POST['kode_akun']);
    $nama_akun = sanitize($_POST['nama_akun']);
    $jenis = sanitize($_POST['jenis']);

    $cek = mysqli_query($koneksi, "SELECT id FROM rekening_akun WHERE kode_akun='$kode_akun'");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['error'] = 'Kode akun sudah ada!';
    } else {
        mysqli_query($koneksi, "INSERT INTO rekening_akun (kode_akun, nama_akun, jenis) VALUES ('$kode_akun','$nama_akun','$jenis')");
        $_SESSION['success'] = 'Rekening akun berhasil ditambahkan.';
    }
    header('Location: master_akun.php');
    exit();
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM rekening_akun WHERE id=$id");
    $_SESSION['success'] = 'Akun dihapus.';
    header('Location: master_akun.php');
    exit();
}

$akun = mysqli_query($koneksi, "SELECT * FROM rekening_akun ORDER BY kode_akun");
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h5 class="text-muted">Data kode rekening akun belanja</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-circle"></i> Tambah Akun
        </button>
    </div>

    <?php flash(); ?>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead>
                    <tr><th>No</th><th>Kode Akun</th><th>Nama Akun</th><th>Jenis</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php $no=1; while ($a = mysqli_fetch_assoc($akun)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><span class="badge bg-dark"><?= $a['kode_akun'] ?></span></td>
                        <td><?= $a['nama_akun'] ?></td>
                        <td><span class="badge bg-info text-dark"><?= $a['jenis'] ?></span></td>
                        <td>
                            <a href="master_akun.php?hapus=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus akun ini?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Rekening Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Akun</label>
                        <input type="text" name="kode_akun" class="form-control" placeholder="cth: 5.2.2.05.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Akun</label>
                        <input type="text" name="nama_akun" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis</label>
                        <select name="jenis" class="form-select">
                            <option value="BELANJA">Belanja</option>
                            <option value="PENDAPATAN">Pendapatan</option>
                            <option value="PEMBIAYAAN">Pembiayaan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
