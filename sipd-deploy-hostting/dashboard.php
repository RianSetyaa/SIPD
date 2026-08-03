<?php
require_once __DIR__ . '/includes/header.php';
require_login();
?>
<div class="container-fluid">
    <!-- Statistik Utama -->
    <div class="row g-3 mb-4">
        <?php
        // Hitung jumlah data sesuai peran
        $total_spp = mysqli_query($koneksi, "SELECT COUNT(*) c FROM spp")->fetch_assoc()['c'];
        $total_spm = mysqli_query($koneksi, "SELECT COUNT(*) c FROM spm")->fetch_assoc()['c'];
        $total_sp2d = mysqli_query($koneksi, "SELECT COUNT(*) c FROM sp2d")->fetch_assoc()['c'];
        $total_spp_draft = mysqli_query($koneksi, "SELECT COUNT(*) c FROM spp WHERE status='draft'")->fetch_assoc()['c'];
        $total_spp_diajukan = mysqli_query($koneksi, "SELECT COUNT(*) c FROM spp WHERE status='diajukan'")->fetch_assoc()['c'];
        $total_kegiatan = mysqli_query($koneksi, "SELECT COUNT(*) c FROM kegiatan")->fetch_assoc()['c'];
        ?>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="background: linear-gradient(135deg,#1a3c6e,#2c5282);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase small opacity-75">Total SPP</div>
                        <h3 class="mb-0"><?= $total_spp ?></h3>
                    </div>
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="background: linear-gradient(135deg,#0f766e,#14b8a6);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase small opacity-75">Total SPM</div>
                        <h3 class="mb-0"><?= $total_spm ?></h3>
                    </div>
                    <i class="bi bi-file-earmark-check"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="background: linear-gradient(135deg,#b45309,#f59e0b);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase small opacity-75">Total SP2D</div>
                        <h3 class="mb-0"><?= $total_sp2d ?></h3>
                    </div>
                    <i class="bi bi-cash-coin"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="background: linear-gradient(135deg,#7e22ce,#a855f7);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase small opacity-75">Total Kegiatan</div>
                        <h3 class="mb-0"><?= $total_kegiatan ?></h3>
                    </div>
                    <i class="bi bi-activity"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Peran & Alur -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <i class="bi bi-diagram-3 text-primary"></i> <strong>Alur Penatausahaan Keuangan</strong>
                </div>
                <div class="card-body">
                    <div class="text-center py-3">
                        <span class="badge bg-primary p-3 mx-1">SPP</span>
                        <i class="bi bi-arrow-right mx-1"></i>
                        <span class="badge bg-warning text-dark p-3 mx-1">SPM</span>
                        <i class="bi bi-arrow-right mx-1"></i>
                        <span class="badge bg-success p-3 mx-1">SP2D</span>
                        <i class="bi bi-arrow-right mx-1"></i>
                        <span class="badge bg-info text-dark p-3 mx-1">Pembayaran / Buku Bank</span>
                        <i class="bi bi-arrow-right mx-1"></i>
                        <span class="badge bg-secondary p-3 mx-1">SPJ</span>
                    </div>
                    <p class="text-muted mt-3 mb-0">
                        <strong>SPP</strong> = Surat Permintaan Pembayaran (diajukan oleh Bendahara/PA),
                        <strong>SPM</strong> = Surat Perintah Membayar (diterbitkan PPK),
                        <strong>SP2D</strong> = Surat Perintah Pencairan Dana (terbit dari Bank/BPKAD),
                        <strong>SPJ</strong> = Surat Pertanggungjawaban.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel berdasarkan peran -->
    <div class="row g-3">
        <?php if (in_array($user['role'], array('verifikator'))): ?>
        <!-- Verifikator: menampilkan SPP yang menunggu verifikasi -->
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between">
                    <strong><i class="bi bi-clipboard-check text-primary"></i> SPP Menunggu Verifikasi</strong>
                    <a href="verifikasi_spp.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No. SPP</th><th>Tanggal</th><th>Jenis</th><th>Uraian</th><th>Jumlah</th><th>Status</th><th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = mysqli_query($koneksi, "SELECT * FROM spp WHERE status IN ('diajukan','diverifikasi') ORDER BY created_at DESC LIMIT 10");
                            while ($r = mysqli_fetch_assoc($q)): ?>
                            <tr>
                                <td><?= $r['no_spp'] ?></td>
                                <td><?= tanggal($r['tgl_spp']) ?></td>
                                <td><?= $r['jenis'] ?></td>
                                <td><?= substr($r['uraian'], 0, 40) ?>...</td>
                                <td><?= rupiah($r['jumlah']) ?></td>
                                <td><span class="badge bg-warning text-dark"><?= ucfirst($r['status']) ?></span></td>
                                <td><a href="verifikasi_spp.php?aksi=verifikasi&id=<?= $r['id'] ?>" class="btn btn-sm btn-primary">Verifikasi</a></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php elseif (in_array($user['role'], array('penatausahaan'))): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between">
                    <strong><i class="bi bi-file-earmark-text text-primary"></i> SPP Terbaru</strong>
                    <a href="spp_list.php" class="btn btn-sm btn-primary">Kelola SPP</a>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr><th>No. SPP</th><th>Tanggal</th><th>Jenis</th><th>Uraian</th><th>Jumlah</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = mysqli_query($koneksi, "SELECT * FROM spp ORDER BY created_at DESC LIMIT 10");
                            while ($r = mysqli_fetch_assoc($q)): ?>
                            <tr>
                                <td><?= $r['no_spp'] ?></td>
                                <td><?= tanggal($r['tgl_spp']) ?></td>
                                <td><?= $r['jenis'] ?></td>
                                <td><?= substr($r['uraian'], 0, 40) ?>...</td>
                                <td><?= rupiah($r['jumlah']) ?></td>
                                <td>
                                    <?php
                                    $cls = array('draft'=>'secondary','diajukan'=>'warning','diverifikasi'=>'info','disetujui'=>'success','ditolak'=>'danger');
                                    $c = (in_array($r['status'], array_keys($cls))) ? $cls[$r['status']] : 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $c ?>"><?= ucfirst($r['status']) ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php elseif (in_array($user['role'], array('bendahara'))): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between">
                    <strong><i class="bi bi-bank text-primary"></i> Buku Bank Terbaru</strong>
                    <a href="buku_bank.php" class="btn btn-sm btn-primary">Kelola Buku Bank</a>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead><tr><th>Tanggal</th><th>No. Bukti</th><th>Uraian</th><th>Masuk</th><th>Keluar</th><th>Saldo</th></tr></thead>
                        <tbody>
                            <?php
                            $q = mysqli_query($koneksi, "SELECT * FROM buku_bank ORDER BY tanggal DESC LIMIT 10");
                            while ($r = mysqli_fetch_assoc($q)): ?>
                            <tr>
                                <td><?= tanggal($r['tanggal']) ?></td>
                                <td><?= $r['no_bukti'] ?></td>
                                <td><?= substr($r['uraian'], 0, 40) ?>...</td>
                                <td class="text-success"><?= $r['masuk']>0 ? rupiah($r['masuk']) : '-' ?></td>
                                <td class="text-danger"><?= $r['keluar']>0 ? rupiah($r['keluar']) : '-' ?></td>
                                <td><strong><?= rupiah($r['saldo']) ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: // admin ?>
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between">
                    <strong><i class="bi bi-briefcase text-primary"></i> Modul Aplikasi</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php
                        $modul = array(
                            array('Master Pengguna','bi-people','master_user.php','Kelola user dan peran'),
                            array('Rekening Akun','bi-123','master_akun.php','Data kode rekening belanja'),
                            array('Program & Kegiatan','bi-diagram-3','master_kegiatan.php','Perencanaan kegiatan + pagu'),
                            array('SKPD/OPD','bi-building','master_skpd.php','Daftar SKPD/OPD'),
                            array('Kelola SPP','bi-file-earmark-text','spp_list.php','Surat Permintaan Pembayaran'),
                            array('Kelola SPM','bi-file-earmark-check','spm_list.php','Surat Perintah Membayar'),
                            array('Kelola SP2D','bi-cash-coin','sp2d_list.php','Surat Perintah Pencairan Dana'),
                            array('Buku Bank','bi-bank','buku_bank.php','Catatan transaksi kas di bank'),
                            array('Buku Pembantu','bi-journal-text','buku_pembantu.php','Buku pembantu pajak/kegiatan'),
                            array('SPJ','bi-folder-check','spj_list.php','Surat Pertanggungjawaban'),
                            array('Verifikasi SPP','bi-clipboard-check','verifikasi_spp.php','Validasi verifikator'),
                        );
                        foreach ($modul as $m): ?>
                        <div class="col-lg-4 col-md-6">
                            <a href="<?= $m[2] ?>" class="text-decoration-none">
                                <div class="card h-100 border shadow-sm">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <i class="bi <?= $m[1] ?> fs-2 text-primary"></i>
                                        <div>
                                            <strong class="text-dark"><?= $m[0] ?></strong>
                                            <div class="text-muted small"><?= $m[3] ?></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
