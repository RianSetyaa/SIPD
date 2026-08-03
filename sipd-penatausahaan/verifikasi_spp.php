<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_role(array('verifikator','bendahara','admin'));

// Verifikasi SPP
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $aksi = $_GET['aksi'];
    if ($aksi == 'verifikasi') {
        mysqli_query($koneksi, "UPDATE spp SET status='diverifikasi' WHERE id=$id");
        $_SESSION['success'] = 'SPP diterima dan diverifikasi.';
    } elseif ($aksi == 'setujui') {
        mysqli_query($koneksi, "UPDATE spp SET status='disetujui' WHERE id=$id");
        $_SESSION['success'] = 'SPP disetujui. Siap diterbitkan SPM.';
    } elseif ($aksi == 'tolak') {
        mysqli_query($koneksi, "UPDATE spp SET status='ditolak' WHERE id=$id");
        $_SESSION['success'] = 'SPP ditolak.';
    }
    header('Location: verifikasi_spp.php');
    exit();
}

$spps = mysqli_query($koneksi, "SELECT s.*, a.nama_akun, u.nama as pembuat 
    FROM spp s 
    LEFT JOIN rekening_akun a ON s.akun_id=a.id 
    LEFT JOIN users u ON s.created_by=u.id 
    WHERE s.status IN ('diajukan','diverifikasi') 
    ORDER BY s.created_at DESC");
?>
<div class="container-fluid">
    <h5 class="text-muted mb-3">Verifikasi SPP oleh Verifikator</h5>

    <?php flash(); ?>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>No. SPP</th><th>Tanggal</th><th>Jenis</th><th>Uraian</th><th>Akun</th><th>Jumlah</th><th>Pembuat</th><th>Status</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($s = mysqli_fetch_assoc($spps)): ?>
                    <tr>
                        <td><strong><?= $s['no_spp'] ?></strong></td>
                        <td><?= tanggal($s['tgl_spp']) ?></td>
                        <td><?= $s['jenis'] ?></td>
                        <td><?= substr($s['uraian'] ?? '-', 0, 35) ?>...</td>
                        <td><small><?= $s['nama_akun'] ?: '-' ?></small></td>
                        <td><?= rupiah($s['jumlah']) ?></td>
                        <td><?= $s['pembuat'] ?></td>
                        <td><span class="badge bg-warning text-dark"><?= ucfirst($s['status']) ?></span></td>
                        <td>
                            <a href="spp_detail.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="bi bi-eye"></i></a>
                            <a href="verifikasi_spp.php?aksi=setujui&id=<?= $s['id'] ?>" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></a>
                            <a href="verifikasi_spp.php?aksi=tolak&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tolak SPP ini?')"><i class="bi bi-x-lg"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
