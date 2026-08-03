<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();

$id = (int)$_GET['id'];
$d = mysqli_query($koneksi, "SELECT sp2d.*, spm.no_spm, spm.tgl_spm, s.no_spp, s.tgl_spp, s.jenis, s.uraian, s.created_by, u.nama as pembuat, a.nama_akun, a.kode_akun, k.nama_kegiatan
    FROM sp2d 
    JOIN spm ON sp2d.spm_id=spm.id 
    JOIN spp s ON spm.spp_id=s.id 
    LEFT JOIN users u ON s.created_by=u.id 
    LEFT JOIN rekening_akun a ON s.akun_id=a.id 
    LEFT JOIN kegiatan k ON s.kegiatan_id=k.id 
    WHERE sp2d.id=$id")->fetch_assoc();

if (!$d) { header('Location: sp2d_list.php'); exit(); }

function jumlah_kata($angka) {
    $angka = abs($angka);
    $kata = array('', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas');
    if ($angka < 12) return $kata[$angka];
    if ($angka < 20) return $kata[$angka-10] . ' Belas';
    if ($angka < 100) return $kata[intval($angka/10)] . ' Puluh ' . $kata[$angka%10];
    if ($angka < 1000) return $kata[intval($angka/100)] . ' Ratus ' . jumlah_kata($angka%100);
    if ($angka < 1000000) return jumlah_kata(intval($angka/1000)) . ' Ribu ' . jumlah_kata($angka%1000);
    if ($angka < 1000000000) return jumlah_kata(intval($angka/1000000)) . ' Juta ' . jumlah_kata($angka%1000000);
    if ($angka < 1000000000000) return jumlah_kata(intval($angka/1000000000)) . ' Miliar ' . jumlah_kata($angka%1000000000);
    return 'Terbilang';
}
$terbilang = trim(str_replace('  ', ' ', jumlah_kata(round($d['jumlah'])))) . ' Rupiah';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak SP2D - <?= $d['no_sp2d'] ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; margin: 30px; color: #000; }
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop h3 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .kop p { margin: 2px 0; font-size: 13px; }
        .judul { text-align: center; margin: 20px 0; font-weight: bold; text-decoration: underline; font-size: 16px; }
        .no-surat { text-align: center; font-style: italic; margin-bottom: 20px; }
        table.detail { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table.detail td { padding: 4px 8px; vertical-align: top; }
        .ttd { display: flex; justify-content: flex-end; margin-top: 40px; }
        .ttd-box { text-align: center; width: 250px; }
        .ttd-box .gap { height: 80px; }
        .garis { border-top: 1px solid #000; width: 200px; margin: 0 auto; }
        .btn-print { position: fixed; top: 10px; right: 10px; }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">🖨️ Cetak</button>
    <div class="kop">
        <h3>Pemerintah Daerah Kota Pelajar</h3>
        <p>Jl. Pendidikan No. 1, Kota Pelajar</p>
        <p>Telp (021) 123456</p>
    </div>

    <div class="judul">SURAT PERINTAH PENCARIAN DANA (SP2D)</div>
    <div class="no-surat">Nomor: <?= $d['no_sp2d'] ?></div>

    <table class="detail">
        <tr><td width="30%">Berdasarkan</td><td>: Surat Perintah Membayar (SPM) Nomor <?= $d['no_spm'] ?></td></tr>
        <tr><td>Tanggal SPM</td><td>: <?= tanggal_panjang($d['tgl_spm']) ?></td></tr>
        <tr><td>Untuk Pembayaran</td><td>: <?= $d['uraian'] ?></td></tr>
        <tr><td>Kegiatan</td><td>: <?= $d['nama_kegiatan'] ?: '-' ?></td></tr>
        <tr><td>Rekening Akun</td><td>: <?= $d['kode_akun'] ?: '-' ?> (<?= $d['nama_akun'] ?: '-' ?>)</td></tr>
        <tr><td>Kepada</td><td>: <?= $d['pembuat'] ?: '-' ?></td></tr>
        <tr><td>Jumlah</td><td>: <?= rupiah($d['jumlah']) ?></td></tr>
        <tr><td>Terbilang</td><td>: <?= $terbilang ?></td></tr>
        <tr><td>Bank</td><td>: Bank Pembangunan Daerah</td></tr>
    </table>

    <p style="margin-top: 20px;">Untuk menjalankan pencairan dana tersebut sejumlah sesuai dengan yang tercantum di atas.</p>

    <div class="ttd">
        <div class="ttd-box">
            <p>Kota Pelajar, <?= tanggal_panjang($d['tgl_sp2d']) ?></p>
            <p>Bendahara Umum Daerah</p>
            <div class="gap"></div>
            <div class="garis"></div>
            <p><strong>DRA. DEWI SUKMA</strong><br>NIP. 197001011990031001</p>
        </div>
    </div>
</body>
</html>
