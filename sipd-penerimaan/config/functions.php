
<?php
// ============================================================
// FUNGSI BANTU - SIPD Penerimaan (SKPKD)
// ============================================================

// ---------- AUTH ----------
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

function active_role() {
    // Role aktif (bisa berpindah-pindah untuk mahasiswa)
    if (isset($_SESSION['active_role'])) return $_SESSION['active_role'];
    if (isset($_SESSION['role'])) return $_SESSION['role'];
    return 'bendahara';
}

function is_mahasiswa() {
    return isset($_SESSION['is_mahasiswa']) && $_SESSION['is_mahasiswa'];
}

function require_role($roles) {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
    $allowed = (array)$roles;

    // Mahasiswa boleh menjalankan semua peran operasional (bendahara/verifikator/ppk)
    // tetapi TIDAK diberi akses ke halaman khusus admin (hanya berisi 'admin').
    if (is_mahasiswa()) {
        $operasional = array_intersect($allowed, ['bendahara', 'verifikator', 'ppk']);
        if (!empty($operasional)) {
            return; // izinkan mahasiswa
        }
        // Halaman ini hanya untuk admin
        flash_error('Anda tidak memiliki hak akses untuk halaman ini.');
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }

    // Non-mahasiswa: izinkan bila role dasar (admin/ppk) atau role aktif cocok
    if (in_array($_SESSION['role'], $allowed) || in_array(active_role(), $allowed)) {
        return;
    }
    flash_error('Anda tidak memiliki hak akses untuk halaman ini.');
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit();
}

// ---------- FORMAT ----------
function sanitize($data) {
    global $koneksi;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    if ($koneksi) $data = mysqli_real_escape_string($koneksi, $data);
    return $data;
}

function rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 2, ',', '.');
}

function angka($angka) {
    return number_format((float)$angka, 0, ',', '.');
}

function tanggal($tanggal) {
    if (empty($tanggal)) return '-';
    return date('d/m/Y', strtotime($tanggal));
}

function tanggal_panjang($tanggal) {
    if (empty($tanggal)) return '-';
    $bulan = array(1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                   7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember');
    $t = strtotime($tanggal);
    return date('j', $t) . ' ' . $bulan[(int)date('n', $t)] . ' ' . date('Y', $t);
}

// ---------- NOMOR DOKUMEN ----------
function buat_nomor($prefix, $tabel) {
    global $koneksi;
    $q = mysqli_query($koneksi, "SELECT MAX(id) AS mx FROM $tabel");
    $r = mysqli_fetch_assoc($q);
    $no = ($r['mx'] ? $r['mx'] : 0) + 1;
    return $prefix . str_pad($no, 3, '0', STR_PAD_LEFT);
}

// ---------- FLASH ----------
function flash() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle"></i> ' . $_SESSION['success'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle"></i> ' . $_SESSION['error'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['error']);
    }
}
function flash_success($m) { $_SESSION['success'] = $m; }
function flash_error($m) { $_SESSION['error'] = $m; }

// ---------- NAMA REKENING ----------
function nama_rekening($id) {
    global $koneksi;
    $q = mysqli_query($koneksi, "SELECT nama FROM rekening WHERE id=" . (int)$id);
    $r = mysqli_fetch_assoc($q);
    return $r ? $r['nama'] : '-';
}
function kode_rekening($id) {
    global $koneksi;
    $q = mysqli_query($koneksi, "SELECT kode FROM rekening WHERE id=" . (int)$id);
    $r = mysqli_fetch_assoc($q);
    return $r ? $r['kode'] : '-';
}

// ============================================================
// LOGIKA JURNAL OTOMATIS PENERIMAAN
// ============================================================

// Akun akuntansi internal (bukan rekening pendapatan)
// 1. SKP terbit  -> Dr. Piutang Pajak | Cr. Pendapatan LO (akrual)
// 2. TBP bayar   -> Dr. Kas di Bendahara | Cr. Piutang Pajak
// 3. STS setor   -> Dr. Kas di Kas Daerah | Cr. Kas di Bendahara  + LRA Pendapatan

// Simpan jurnal otomatis
function catat_jurnal_skp($skp_id) {
    global $koneksi;
    $s = mysqli_query($koneksi, "SELECT * FROM skp WHERE id=$skp_id");
    $skp = mysqli_fetch_assoc($s);
    if (!$skp) return;

    $jl = $skp['jumlah'];

    mysqli_query($koneksi, "INSERT INTO jurnal (skpd_id, tanggal, jenis, ref_id, no_dokumen, uraian)
        VALUES ({$skp['skpd_id']},'{$skp['tanggal']}','SKP',{$skp['id']},'{$skp['no_skp']}','SKP {$skp['no_skp']}')");
    $jid = mysqli_insert_id($koneksi);

    // Dr Piutang Pajak
    mysqli_query($koneksi, "INSERT INTO jurnal_rincian (jurnal_id,rekening_id,akun,debet,kredit,basis)
        VALUES ($jid,0,'Piutang Pajak',$jl,0,'LO')");
    // Cr Pendapatan LO
    mysqli_query($koneksi, "INSERT INTO jurnal_rincian (jurnal_id,rekening_id,akun,debet,kredit,basis)
        VALUES ($jid,{$skp['rekening_id']},'Pendapatan LO - {$skp['no_skp']}',0,$jl,'LO')");
}

function catat_jurnal_tbp($tbp_id) {
    global $koneksi;
    $t = mysqli_query($koneksi, "SELECT t.*, s.rekening_id, s.jumlah AS skp_jumlah, s.no_skp
        FROM tbp t JOIN skp s ON s.id=t.skp_id WHERE t.id=$tbp_id");
    $tbp = mysqli_fetch_assoc($t);
    if (!$tbp) return;

    $jl = $tbp['jumlah'];

    mysqli_query($koneksi, "INSERT INTO jurnal (skpd_id, tanggal, jenis, ref_id, no_dokumen, uraian)
        VALUES ({$tbp['skpd_id']},'{$tbp['tanggal']}','TBP',{$tbp['id']},'{$tbp['no_tbp']}','TBP {$tbp['no_tbp']}')");
    $jid = mysqli_insert_id($koneksi);

    // Dr Kas di Bendahara
    mysqli_query($koneksi, "INSERT INTO jurnal_rincian (jurnal_id,rekening_id,akun,debet,kredit,basis)
        VALUES ($jid,0,'Kas di Bendahara Penerimaan',$jl,0,'LO')");
    // Cr Piutang Pajak
    mysqli_query($koneksi, "INSERT INTO jurnal_rincian (jurnal_id,rekening_id,akun,debet,kredit,basis)
        VALUES ($jid,0,'Piutang Pajak',0,$jl,'LO')");
}

function catat_jurnal_sts($sts_id) {
    global $koneksi;
    $st = mysqli_query($koneksi, "SELECT st.*, t.skp_id, s.rekening_id, s.jumlah AS skp_jumlah FROM sts st
        JOIN tbp t ON t.id=st.tbp_id JOIN skp s ON s.id=t.skp_id WHERE st.id=$sts_id");
    $sts = mysqli_fetch_assoc($st);
    if (!$sts) return;

    $jl = $sts['jumlah'];

    mysqli_query($koneksi, "INSERT INTO jurnal (skpd_id, tanggal, jenis, ref_id, no_dokumen, uraian)
        VALUES ({$sts['skpd_id']},'{$sts['tanggal']}','STS',{$sts['id']},'{$sts['no_sts']}','STS {$sts['no_sts']}')");
    $jid = mysqli_insert_id($koneksi);

    // Dr Kas di Kas Daerah (LO)
    mysqli_query($koneksi, "INSERT INTO jurnal_rincian (jurnal_id,rekening_id,akun,debet,kredit,basis)
        VALUES ($jid,0,'Kas di Kas Daerah',$jl,0,'LO')");
    // Cr Kas di Bendahara (LO)
    mysqli_query($koneksi, "INSERT INTO jurnal_rincian (jurnal_id,rekening_id,akun,debet,kredit,basis)
        VALUES ($jid,0,'Kas di Bendahara Penerimaan',0,$jl,'LO')");

    // Jurnal LRA: Dr Kas di Kas Daerah (LRA) | Cr Pendapatan LRA
    mysqli_query($koneksi, "INSERT INTO jurnal_rincian (jurnal_id,rekening_id,akun,debet,kredit,basis)
        VALUES ($jid,0,'Kas di Kas Daerah (Penerimaan)',$jl,0,'LRA')");
    mysqli_query($koneksi, "INSERT INTO jurnal_rincian (jurnal_id,rekening_id,akun,debet,kredit,basis)
        VALUES ($jid,{$sts['rekening_id']},'Pendapatan LRA - {$sts['no_sts']}',0,$jl,'LRA')");
}

// Hapus jurnal terkait (saat dokumen dihapus)
function hapus_jurnal_ref($jenis, $ref_id) {
    global $koneksi;
    mysqli_query($koneksi, "DELETE jr FROM jurnal_rincian jr
        JOIN jurnal j ON j.id=jr.jurnal_id
        WHERE j.jenis='$jenis' AND j.ref_id=$ref_id");
    mysqli_query($koneksi, "DELETE FROM jurnal WHERE jenis='$jenis' AND ref_id=$ref_id");
}
