<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_role(array('bendahara','admin'));

// Tambah transaksi manual
if (isset($_POST['simpan'])) {
    $tanggal = sanitize($_POST['tanggal']);
    $no_bukti = sanitize($_POST['no_bukti']);
    $uraian = sanitize($_POST['uraian']);
    $masuk = str_replace('.', '', $_POST['masuk']);
    $keluar = str_replace('.', '', $_POST['keluar']);
    $saldo_akhir = mysqli_query($koneksi, "SELECT saldo FROM buku_bank ORDER BY id DESC LIMIT 1");
    $saldo_sekarang = (mysqli_num_rows($saldo_akhir) > 0) ? mysqli_fetch_assoc($saldo_akhir)['saldo'] : 0;
    $saldo_new = $saldo_sekarang + $masuk - $keluar;
    mysqli_query($koneksi, "INSERT INTO buku_bank (tanggal, no_bukti, uraian, masuk, keluar, saldo) VALUES ('$tanggal','$no_bukti','$uraian',$masuk,$keluar,$saldo_new)");
    $_SESSION['success'] = 'Transaksi buku bank dicatat.';
    header('Location: buku_bank.php');
    exit();
}

$rows = mysqli_query($koneksi, "SELECT * FROM buku_bank ORDER BY id DESC");
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h5 class="text-muted">Buku Bank Kas Daerah</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTrans"><i class="bi bi-plus-circle"></i> Transaksi Manual</button>
    </div>

    <?php flash(); ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr><th>Tanggal</th><th>No. Bukti</th><th>Uraian</th><th>Masuk (Debet)</th><th>Keluar (Kredit)</th><th>Saldo</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($r = mysqli_fetch_assoc($rows)): ?>
                        <tr>
                            <td><?= tanggal($r['tanggal']) ?></td>
                            <td><?= $r['no_bukti'] ?: '-' ?></td>
                            <td><?= $r['uraian'] ?></td>
                            <td class="text-success"><?= $r['masuk'] > 0 ? rupiah($r['masuk']) : '-' ?></td>
                            <td class="text-danger"><?= $r['keluar'] > 0 ? rupiah($r['keluar']) : '-' ?></td>
                            <td><strong><?= rupiah($r['saldo']) ?></strong></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTrans" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
            <div class="modal-header"><h5 class="modal-title">Tambah Transaksi Buku Bank</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                <div class="mb-3"><label class="form-label">No. Bukti</label><input name="no_bukti" class="form-control" placeholder="cth: BKM-001"></div>
                <div class="mb-3"><label class="form-label">Uraian</label><textarea name="uraian" class="form-control" rows="2" required></textarea></div>
                <div class="row g-3">
                    <div class="col-6"><label class="form-label">Masuk (Rp)</label><input name="masuk" class="form-control text-end" value="0"></div>
                    <div class="col-6"><label class="form-label">Keluar (Rp)</label><input name="keluar" class="form-control text-end" value="0"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" name="simpan" class="btn btn-primary">Simpan</button></div>
        </form>
    </div></div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
