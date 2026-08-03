<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_role(array('penatausahaan','bendahara','admin'));

// Terbitkan SP2D dari SPM
if (isset($_GET['terbit']) && isset($_GET['spm_id'])) {
    $spm_id = (int)$_GET['spm_id'];
    $spm = mysqli_query($koneksi, "SELECT * FROM spm WHERE id=$spm_id")->fetch_assoc();
    if ($spm && $spm['status'] == 'disetujui') {
        // cek apakah sudah punya sp2d
        $cek = mysqli_query($koneksi, "SELECT id FROM sp2d WHERE spm_id=$spm_id");
        if (mysqli_num_rows($cek) == 0) {
            $no_sp2d = buat_nomor('SP2D-', 'sp2d', 'no_sp2d');
            mysqli_query($koneksi, "INSERT INTO sp2d (no_sp2d, tgl_sp2d, spm_id, jumlah, status) VALUES ('$no_sp2d', NOW(), $spm_id, {$spm['jumlah']}, 'proses')");
            $_SESSION['success'] = 'SP2D berhasil diterbitkan: ' . $no_sp2d;
        } else {
            $_SESSION['error'] = 'SPM ini sudah memiliki SP2D.';
        }
    } else {
        $_SESSION['error'] = 'SPM belum disetujui.';
    }
    header('Location: sp2d_list.php');
    exit();
}

// Proses pencairan SP2D (cairkan)
if (isset($_GET['cair']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sp2d = mysqli_query($koneksi, "SELECT * FROM sp2d WHERE id=$id")->fetch_assoc();
    if ($sp2d && $sp2d['status'] == 'proses') {
        mysqli_query($koneksi, "UPDATE sp2d SET status='cair' WHERE id=$id");

        // Masukkan ke buku bank (uang keluar)
        $ur = "Pembayaran SP2D " . $sp2d['no_sp2d'];
        $sql = "INSERT INTO buku_bank (tanggal, no_bukti, uraian, keluar, saldo, sp2d_id) 
                SELECT NOW(), '{$sp2d['no_sp2d']}', '$ur', {$sp2d['jumlah']}, 
                       (SELECT IFNULL(MAX(saldo),0) - {$sp2d['jumlah']} FROM buku_bank), $id";
        mysqli_query($koneksi, $sql);
        $_SESSION['success'] = 'SP2D berhasil dicairkan dan dicatat di buku bank.';
    }
    header('Location: sp2d_list.php');
    exit();
}

$sp2ds = mysqli_query($koneksi, "SELECT sp2d.*, spm.no_spm, s.no_spp 
    FROM sp2d 
    JOIN spm ON sp2d.spm_id = spm.id 
    JOIN spp s ON spm.spp_id = s.id 
    ORDER BY sp2d.created_at DESC");
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h5 class="text-muted">Surat Perintah Pencairan Dana (SP2D)</h5>
    </div>

    <?php flash(); ?>

    <!-- SPM yang siap terbit SP2D -->
    <div class="card mb-3">
        <div class="card-header bg-white"><strong><i class="bi bi-cash-stack text-success"></i> SPM Disetujui (Siap Terbitkan SP2D)</strong></div>
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>No. SPM</th><th>No. SPP</th><th>Tanggal</th><th>Jumlah</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php
                    $ready = mysqli_query($koneksi, "SELECT spm.* , s.no_spp FROM spm 
                        LEFT JOIN sp2d ON sp2d.spm_id=spm.id 
                        JOIN spp s ON spm.spp_id=s.id 
                        WHERE spm.status='disetujui' AND sp2d.id IS NULL 
                        ORDER BY spm.created_at DESC");
                    if (mysqli_num_rows($ready) == 0): ?>
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada SPM yang menunggu penerbitan SP2D.</td></tr>
                    <?php endif; ?>
                    <?php while ($r = mysqli_fetch_assoc($ready)): ?>
                    <tr>
                        <td><strong><?= $r['no_spm'] ?></strong></td>
                        <td><?= $r['no_spp'] ?></td>
                        <td><?= tanggal($r['tgl_spm']) ?></td>
                        <td><?= rupiah($r['jumlah']) ?></td>
                        <td><a href="sp2d_list.php?terbit=1&spm_id=<?= $r['id'] ?>" class="btn btn-sm btn-primary">Terbitkan SP2D</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Daftar SP2D -->
    <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-cash-coin text-primary"></i> Daftar SP2D</strong></div>
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead>
                    <tr><th>No. SP2D</th><th>Tanggal</th><th>No. SPM</th><th>No. SPP</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php while ($d = mysqli_fetch_assoc($sp2ds)): ?>
                    <tr>
                        <td><strong><?= $d['no_sp2d'] ?></strong></td>
                        <td><?= tanggal($d['tgl_sp2d']) ?></td>
                        <td><?= $d['no_spm'] ?></td>
                        <td><?= $d['no_spp'] ?></td>
                        <td><?= rupiah($d['jumlah']) ?></td>
                        <td>
                            <?php
                            $cls = array('proses'=>'warning','cair'=>'success','gagal'=>'danger');
                            $c = (isset($cls[$d['status']])) ? $cls[$d['status']] : 'secondary';
                            ?>
                            <span class="badge bg-<?= $c ?>"><?= ucfirst($d['status']) ?></span>
                        </td>
                        <td>
                            <?php if ($d['status'] == 'proses'): ?>
                                <a href="sp2d_list.php?cair=1&id=<?= $d['id'] ?>" class="btn btn-sm btn-success"><i class="bi bi-cash"></i> Cairkan</a>
                            <?php else: ?>
                                <a href="cetak_sp2d.php?id=<?= $d['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer"></i> Cetak</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
