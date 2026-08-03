<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_role(array('bendahara','admin'));

// Tambah catatan buku pembantu
if (isset($_POST['simpan'])) {
    $buku_tipe = sanitize($_POST['buku_tipe']);
    $tanggal = sanitize($_POST['tanggal']);
    $kode_bukti = sanitize($_POST['kode_bukti']);
    $uraian = sanitize($_POST['uraian']);
    $jumlah = str_replace('.', '', $_POST['jumlah']);
    $ket = sanitize($_POST['keterangan']);
    mysqli_query($koneksi, "INSERT INTO buku_pembantu (buku_tipe, tanggal, kode_bukti, uraian, jumlah, keterangan) VALUES ('$buku_tipe','$tanggal','$kode_bukti','$uraian',$jumlah,'$ket')");
    $_SESSION['success'] = 'Catatan buku pembantu ditambahkan.';
    header('Location: buku_pembantu.php');
    exit();
}

$rows = mysqli_query($koneksi, "SELECT * FROM buku_pembantu ORDER BY id DESC");
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h5 class="text-muted">Buku Pembantu (Pajak, Kegiatan, Panjar, Bank)</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBP"><i class="bi bi-plus-circle"></i> Tambah Catatan</button>
    </div>

    <?php flash(); ?>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead>
                    <tr><th>Tipe Buku</th><th>Tanggal</th><th>No. Bukti</th><th>Uraian</th><th>Jumlah</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                    <?php while ($r = mysqli_fetch_assoc($rows)): ?>
                    <tr>
                        <td><span class="badge bg-info text-dark"><?= ucfirst($r['buku_tipe']) ?></span></td>
                        <td><?= tanggal($r['tanggal']) ?></td>
                        <td><?= $r['kode_bukti'] ?: '-' ?></td>
                        <td><?= $r['uraian'] ?></td>
                        <td><?= rupiah($r['jumlah']) ?></td>
                        <td><?= $r['keterangan'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBP" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
            <div class="modal-header"><h5 class="modal-title">Tambah Buku Pembantu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Tipe Buku</label>
                    <select name="buku_tipe" class="form-select">
                        <option value="pajak">Buku Pembantu Pajak</option>
                        <option value="kegiatan">Buku Pembantu Kegiatan</option>
                        <option value="panjar">Buku Pembantu Panjar</option>
                        <option value="bank">Buku Pembantu Bank</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                <div class="mb-3"><label class="form-label">No. Bukti</label><input name="kode_bukti" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Uraian</label><textarea name="uraian" class="form-control" rows="2" required></textarea></div>
                <div class="mb-3"><label class="form-label">Jumlah (Rp)</label><input name="jumlah" class="form-control text-end" required></div>
                <div class="mb-3"><label class="form-label">Keterangan</label><input name="keterangan" class="form-control"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" name="simpan" class="btn btn-primary">Simpan</button></div>
        </form>
    </div></div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
