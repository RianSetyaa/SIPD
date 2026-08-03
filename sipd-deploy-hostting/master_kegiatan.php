<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_role('admin');

// Tambah program
if (isset($_POST['simpan_program'])) {
    $kode = sanitize($_POST['kode_program']);
    $nama = sanitize($_POST['nama_program']);
    mysqli_query($koneksi, "INSERT INTO program (kode_program, nama_program) VALUES ('$kode','$nama')");
    $_SESSION['success'] = 'Program ditambahkan.';
    header('Location: master_kegiatan.php');
    exit();
}

// Tambah kegiatan
if (isset($_POST['simpan_kegiatan'])) {
    $program_id = (int)$_POST['program_id'];
    $kode = sanitize($_POST['kode_kegiatan']);
    $nama = sanitize($_POST['nama_kegiatan']);
    $anggaran = str_replace('.', '', $_POST['anggaran']);
    mysqli_query($koneksi, "INSERT INTO kegiatan (program_id, kode_kegiatan, nama_kegiatan, anggaran) VALUES ($program_id,'$kode','$nama',$anggaran)");
    $_SESSION['success'] = 'Kegiatan ditambahkan.';
    header('Location: master_kegiatan.php');
    exit();
}

$programs = mysqli_query($koneksi, "SELECT * FROM program ORDER BY kode_program");
$kegiatans = mysqli_query($koneksi, "SELECT k.*, p.kode_program, p.nama_program FROM kegiatan k JOIN program p ON k.program_id=p.id ORDER BY k.kode_kegiatan");
?>
<div class="container-fluid">
    <?php flash(); ?>
    <div class="d-flex justify-content-between mb-3">
        <h5 class="text-muted">Program dan Kegiatan serta pagu anggaran</h5>
        <div>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalProgram">
                <i class="bi bi-plus-circle"></i> Program
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalKegiatan">
                <i class="bi bi-plus-circle"></i> Kegiatan
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-diagram-3 text-primary"></i> Daftar Kegiatan per Program</strong></div>
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead>
                    <tr><th>No</th><th>Kode Kegiatan</th><th>Nama Kegiatan</th><th>Program</th><th>Anggaran</th><th>Pagu per Kegiatan</th></tr>
                </thead>
                <tbody>
                    <?php $no=1; while ($k = mysqli_fetch_assoc($kegiatans)):
                        // Pagu total dari rka
                        $rka = mysqli_query($koneksi, "SELECT SUM(pagu) total FROM rka WHERE kegiatan_id={$k['id']}")->fetch_assoc()['total'];
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><span class="badge bg-dark"><?= $k['kode_kegiatan'] ?></span></td>
                        <td><?= $k['nama_kegiatan'] ?></td>
                        <td><small><?= $k['nama_program'] ?></small></td>
                        <td><?= rupiah($k['anggaran']) ?></td>
                        <td><?= $rka ? rupiah($rka) : '-' ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Program -->
<div class="modal fade" id="modalProgram" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
            <div class="modal-header"><h5 class="modal-title">Tambah Program</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Kode Program</label><input name="kode_program" class="form-control" placeholder="cth: 1.01.01" required></div>
                <div class="mb-3"><label class="form-label">Nama Program</label><input name="nama_program" class="form-control" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" name="simpan_program" class="btn btn-primary">Simpan</button></div>
        </form>
    </div></div>
</div>

<!-- Modal Kegiatan -->
<div class="modal fade" id="modalKegiatan" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
            <div class="modal-header"><h5 class="modal-title">Tambah Kegiatan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Program</label>
                    <select name="program_id" class="form-select" required>
                        <?php mysqli_data_seek($programs, 0); while ($p = mysqli_fetch_assoc($programs)): ?>
                            <option value="<?= $p['id'] ?>"><?= $p['kode_program'] ?> - <?= $p['nama_program'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Kode Kegiatan</label><input name="kode_kegiatan" class="form-control" placeholder="cth: 1.01.01.001" required></div>
                <div class="mb-3"><label class="form-label">Nama Kegiatan</label><input name="nama_kegiatan" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Anggaran (Rp)</label><input name="anggaran" class="form-control text-end" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" name="simpan_kegiatan" class="btn btn-primary">Simpan</button></div>
        </form>
    </div></div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
