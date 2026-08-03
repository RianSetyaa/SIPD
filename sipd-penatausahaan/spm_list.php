<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_role(array('penatausahaan','bendahara','admin'));

// Terbitkan SPM dari SPP yang sudah disetujui
if (isset($_GET['terbit']) && isset($_GET['spp_id'])) {
    $spp_id = (int)$_GET['spp_id'];
    $spp = mysqli_query($koneksi, "SELECT * FROM spp WHERE id=$spp_id")->fetch_assoc();
    if ($spp && $spp['status'] == 'disetujui') {
        $no_spm = buat_nomor('SPM-', 'spm', 'no_spm');
        mysqli_query($koneksi, "INSERT INTO spm (no_spm, tgl_spm, spp_id, jumlah) VALUES ('$no_spm', NOW(), $spp_id, {$spp['jumlah']})");
        $_SESSION['success'] = 'SPM berhasil diterbitkan: ' . $no_spm;
    } else {
        $_SESSION['error'] = 'SPP belum disetujui atau tidak ditemukan.';
    }
    header('Location: spm_list.php');
    exit();
}

// Selesai SPM (status disetujui)
if (isset($_GET['setujui'])) {
    $id = (int)$_GET['setujui'];
    mysqli_query($koneksi, "UPDATE spm SET status='disetujui', tanggal_bayar=NOW() WHERE id=$id");
    $_SESSION['success'] = 'SPM disetujui.';
    header('Location: spm_list.php');
    exit();
}

$spms = mysqli_query($koneksi, "SELECT spm.*, s.no_spp, s.jenis, s.uraian 
    FROM spm JOIN spp s ON spm.spp_id = s.id 
    ORDER BY spm.created_at DESC");
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h5 class="text-muted">Surat Perintah Membayar (SPM)</h5>
    </div>

    <?php flash(); ?>

    <!-- Daftar SPP yang siap dibuat SPM -->
    <div class="card mb-3">
        <div class="card-header bg-white"><strong><i class="bi bi-check2-circle text-success"></i> SPP yang Sudah Disetujui (Siap Terbitkan SPM)</strong></div>
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>No. SPP</th><th>Tanggal</th><th>Jenis</th><th>Uraian</th><th>Jumlah</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php
                    $ready = mysqli_query($koneksi, "SELECT s.* FROM spp s 
                        LEFT JOIN spm ON spm.spp_id=s.id 
                        WHERE s.status='disetujui' AND spm.id IS NULL 
                        ORDER BY s.created_at DESC");
                    if (mysqli_num_rows($ready) == 0): ?>
                        <tr><td colspan="6" class="text-center text-muted">Tidak ada SPP yang menunggu penerbitan SPM.</td></tr>
                    <?php endif; ?>
                    <?php while ($r = mysqli_fetch_assoc($ready)): ?>
                    <tr>
                        <td><strong><?= $r['no_spp'] ?></strong></td>
                        <td><?= tanggal($r['tgl_spp']) ?></td>
                        <td><?= $r['jenis'] ?></td>
                        <td><?= substr($r['uraian'] ?? '-', 0, 40) ?>...</td>
                        <td><?= rupiah($r['jumlah']) ?></td>
                        <td><a href="spm_list.php?terbit=1&spp_id=<?= $r['id'] ?>" class="btn btn-sm btn-primary">Terbitkan SPM</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Daftar SPM -->
    <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-file-earmark-check text-primary"></i> Daftar SPM</strong></div>
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead>
                    <tr><th>No. SPM</th><th>Tanggal</th><th>No. SPP</th><th>Jenis</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php while ($m = mysqli_fetch_assoc($spms)): ?>
                    <tr>
                        <td><strong><?= $m['no_spm'] ?></strong></td>
                        <td><?= tanggal($m['tgl_spm']) ?></td>
                        <td><?= $m['no_spp'] ?></td>
                        <td><?= $m['jenis'] ?></td>
                        <td><?= rupiah($m['jumlah']) ?></td>
                        <td>
                            <?php if ($m['status'] == 'disetujui'): ?>
                                <span class="badge bg-success">Disetujui</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($m['status'] != 'disetujui'): ?>
                                <a href="spm_list.php?setujui=<?= $m['id'] ?>" class="btn btn-sm btn-outline-success">Setujui</a>
                            <?php endif; ?>
                            <a href="sp2d_list.php?terbit=1&spm_id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary">Terbit SP2D</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
