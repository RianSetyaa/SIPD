<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_role(array('bendahara','admin'));

// Buat SPJ dari SPP
if (isset($_GET['buat']) && isset($_GET['spp_id'])) {
    $spp_id = (int)$_GET['spp_id'];
    $spp = mysqli_query($koneksi, "SELECT * FROM spp WHERE id=$spp_id")->fetch_assoc();
    if ($spp && $spp['status'] == 'disetujui') {
        $no_spj = buat_nomor('SPJ-', 'spj', 'no_spj');
        mysqli_query($koneksi, "INSERT INTO spj (no_spj, tgl_spj, spp_id, jumlah, status) VALUES ('$no_spj', NOW(), $spp_id, {$spp['jumlah']}, 'draft')");
        $_SESSION['success'] = 'SPJ dibuat: ' . $no_spj;
    }
    header('Location: spj_list.php');
    exit();
}

// Selesai / lengkap SPJ
if (isset($_GET['lengkap'])) {
    $id = (int)$_GET['lengkap'];
    mysqli_query($koneksi, "UPDATE spj SET status='lengkap' WHERE id=$id");
    $_SESSION['success'] = 'SPJ ditandai lengkap.';
    header('Location: spj_list.php');
    exit();
}

$spjs = mysqli_query($koneksi, "SELECT spj.*, s.no_spp FROM spj LEFT JOIN spp s ON spj.spp_id=s.id ORDER BY spj.created_at DESC");
?>
<div class="container-fluid">
    <h5 class="text-muted mb-3">Surat Pertanggungjawaban (SPJ)</h5>

    <?php flash(); ?>

    <!-- SPP disetujui yang bisa dibuat SPJ -->
    <div class="card mb-3">
        <div class="card-header bg-white"><strong><i class="bi bi-folder-check text-success"></i> SPP Disetujui (Siap SPJ)</strong></div>
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>No. SPP</th><th>Tanggal</th><th>Jumlah</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php
                    $ready = mysqli_query($koneksi, "SELECT s.* FROM spp s 
                        LEFT JOIN spj ON spj.spp_id = s.id 
                        WHERE s.status='disetujui' AND spj.id IS NULL 
                        ORDER BY s.created_at DESC");
                    if (mysqli_num_rows($ready) == 0): ?>
                        <tr><td colspan="4" class="text-center text-muted">Tidak ada SPP untuk dibuat SPJ.</td></tr>
                    <?php endif; ?>
                    <?php while ($r = mysqli_fetch_assoc($ready)): ?>
                    <tr>
                        <td><strong><?= $r['no_spp'] ?></strong></td>
                        <td><?= tanggal($r['tgl_spp']) ?></td>
                        <td><?= rupiah($r['jumlah']) ?></td>
                        <td><a href="spj_list.php?buat=1&spp_id=<?= $r['id'] ?>" class="btn btn-sm btn-primary">Buat SPJ</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-folder-check text-primary"></i> Daftar SPJ</strong></div>
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead><tr><th>No. SPJ</th><th>Tanggal</th><th>No. SPP</th><th>Jumlah</th><th>Status</th><th>Keterangan</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php while ($j = mysqli_fetch_assoc($spjs)): ?>
                    <tr>
                        <td><strong><?= $j['no_spj'] ?></strong></td>
                        <td><?= tanggal($j['tgl_spj']) ?></td>
                        <td><?= $j['no_spp'] ?></td>
                        <td><?= rupiah($j['jumlah']) ?></td>
                        <td>
                            <?php
                            $cls = array('draft'=>'warning','lengkap'=>'success','ditolak'=>'danger');
                            $c = (isset($cls[$j['status']])) ? $cls[$j['status']] : 'secondary';
                            ?>
                            <span class="badge bg-<?= $c ?>"><?= ucfirst($j['status']) ?></span>
                        </td>
                        <td><?= $j['keterangan'] ?: '-' ?></td>
                        <td>
                            <?php if ($j['status'] != 'lengkap'): ?>
                                <a href="spj_list.php?lengkap=<?= $j['id'] ?>" class="btn btn-sm btn-success">Tandai Lengkap</a>
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
