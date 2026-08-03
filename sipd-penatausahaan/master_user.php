<?php
require_once __DIR__ . '/includes/header.php';
require_login();
require_role('admin');

// Proses simpan pengguna baru
if (isset($_POST['simpan'])) {
    $username = sanitize($_POST['username']);
    $nama = sanitize($_POST['nama']);
    $nip = sanitize($_POST['nip']);
    $role = sanitize($_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek username unik
    $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['error'] = 'Username sudah digunakan!';
    } else {
        mysqli_query($koneksi, "INSERT INTO users (username, password, nama, nip, role) VALUES ('$username','$password','$nama','$nip','$role')");
        $_SESSION['success'] = 'Pengguna berhasil ditambahkan.';
    }
    header('Location: master_user.php');
    exit();
}

// Proses hapus pengguna
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM users WHERE id=$id");
    $_SESSION['success'] = 'Pengguna berhasil dihapus.';
    header('Location: master_user.php');
    exit();
}

// Ambil data pengguna
$query = "SELECT u.*, s.nama_skpd FROM users u LEFT JOIN skpd s ON u.skpd_id = s.id ORDER BY u.id";
$users = mysqli_query($koneksi, $query);
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h5 class="text-muted">Kelola akun pengguna dan hak akses</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-person-plus"></i> Tambah Pengguna
        </button>
    </div>

    <?php flash(); ?>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>No</th><th>Username</th><th>Nama Lengkap</th><th>NIP</th><th>Peran</th><th>Status</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($u = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= $u['username'] ?></strong></td>
                        <td><?= $u['nama'] ?></td>
                        <td><?= $u['nip'] ?: '-' ?></td>
                        <td>
                            <?php
                            $role_cls = array('admin'=>'danger','penatausahaan'=>'primary','bendahara'=>'success','verifikator'=>'warning');
                            $r = (isset($role_cls[$u['role']])) ? $role_cls[$u['role']] : 'secondary';
                            ?>
                            <span class="badge bg-<?= $r ?>"><?= ucfirst($u['role']) ?></span>
                        </td>
                        <td>
                            <?php if ($u['is_active']): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['username'] != 'admin'): ?>
                            <a href="master_user.php?hapus=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus pengguna ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">NIP</label>
                        <input type="text" name="nip" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Peran / Hak Akses</label>
                        <select name="role" class="form-select">
                            <option value="penatausahaan">Penatausahaan (PPK)</option>
                            <option value="bendahara">Bendahara</option>
                            <option value="verifikator">Verifikator</option>
                            <option value="admin">Admin</option>
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
