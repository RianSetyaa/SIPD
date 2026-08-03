<?php
// ============================================================
// ADMIN/DOSEN: Daftar Mahasiswa & SKPKD masing-masing
// Hanya untuk akun admin. Menampilkan semua mahasiswa, daerah
// (SKPKD) yang mereka miliki, dan ringkasan transaksi.
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();
require_role(['admin']);

$q = "SELECT u.id, u.username, u.nama AS nama_mhs, u.nim, u.prodi, u.skpd_id,
             s.nama AS nama_skpd, s.kode AS kode_skpd, s.alamat AS daerah,
             (SELECT COUNT(*) FROM skp WHERE skpd_id=u.skpd_id) AS jml_skp,
             (SELECT IFNULL(SUM(jumlah),0) FROM skp WHERE skpd_id=u.skpd_id) AS tot_skp,
             (SELECT IFNULL(SUM(jumlah),0) FROM sts WHERE skpd_id=u.skpd_id) AS tot_sts
      FROM users u
      LEFT JOIN skpd s ON s.id=u.skpd_id
      WHERE u.role='mahasiswa'
      ORDER BY u.created_at DESC";
$rows = mysqli_query($koneksi, $q);
include __DIR__ . '/includes/header.php';
?>
<div class="card card-modul">
  <div class="card-header bg-white fw-bold"><i class="bi bi-people"></i> Daftar Mahasiswa &amp; Daerah (SKPKD) — Akses Admin/Dosen</div>
  <div class="card-body table-responsive">
    <table class="table table-sm table-striped data-table" style="width:100%">
      <thead><tr>
        <th>Nama</th><th>NIM</th><th>Prodi</th><th>Username</th><th>Daerah / SKPKD</th>
        <th class="text-end">SKP</th><th class="text-end">Total SKP (Rp)</th><th class="text-end">Total Setor STS (Rp)</th>
      </tr></thead>
      <tbody>
      <?php while($r=mysqli_fetch_assoc($rows)): ?>
        <tr>
          <td><?= htmlspecialchars($r['nama_mhs']) ?></td>
          <td><?= htmlspecialchars($r['nim']) ?></td>
          <td><?= htmlspecialchars($r['prodi']) ?></td>
          <td><code><?= htmlspecialchars($r['username']) ?></code></td>
          <td>
            <span class="badge bg-secondary"><?= htmlspecialchars($r['kode_skpd']) ?></span>
            <?= htmlspecialchars($r['nama_skpd']) ?><br>
            <small class="text-muted"><?= htmlspecialchars($r['daerah']) ?></small>
          </td>
          <td class="text-end"><?= $r['jml_skp'] ?></td>
          <td class="text-end"><?= rupiah($r['tot_skp']) ?></td>
          <td class="text-end"><?= rupiah($r['tot_sts']) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
      <?php if (mysqli_num_rows($rows)==0): ?>
        <tfoot><tr><td colspan="8" class="text-center text-muted py-3">Belum ada mahasiswa terdaftar.</td></tr></tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
