# SIPD Penatausahaan - Aplikasi Pembelajaran Mahasiswa

Aplikasi web **PHP Native + MySQL** yang meniru modul **SIPD Penatausahaan** (Sistem Informasi Pemerintahan Daerah - Modul Penatausahaan Keuangan). Ditujukan sebagai media pembelajaran/demonstrasi bagi mahasiswa untuk memahami alur penatausahaan keuangan daerah.

## Fitur Utama

1. **Multi-Pengguna (Login & Peran)**
   - `admin` - mengelola data master, semua akses
   - `penatausahaan` (PPK) - kelola SPP, SPM, SP2D
   - `bendahara` - buku bank, buku pembantu, SPJ
   - `verifikator` - verifikasi dan persetujuan SPP

2. **Data Master**
   - Manajemen Pengguna
   - Rekening Akun (kode rekening belanja)
   - Program & Kegiatan (dengan pagu anggaran)
   - SKPD/OPD

3. **Alur Penatausahaan**
   - **SPP** (Surat Permintaan Pembayaran) - draft → diajukan → diverifikasi → disetujui/ditolak
   - **SPM** (Surat Perintah Membayar) - diterbitkan dari SPP yang disetujui
   - **SP2D** (Surat Perintah Pencairan Dana) - diterbitkan dari SPM, bisa dicairkan
   - **Buku Bank** - pencatatan transaksi kas (debet/kredit/saldo)
   - **Buku Pembantu** - pajak, kegiatan, panjar, bank
   - **SPJ** (Surat Pertanggungjawaban)
   - **Upload dokumen** pendukung (kwitansi, nota, kontrak, dll)
   - **Cetak SP2D** (dengan terbilang Rupiah)

## Kebutuhan

- PHP 7.4+ (lokal: XAMPP / Laragon / WAMP)
- MySQL / MariaDB
- Web Browser

## Cara Install

1. Letakkan folder `sipd-penatausahaan` di dalam `htdocs` (XAMPP) atau `www` (Laragon).
   Contoh: `C:\xampp\htdocs\sipd-penatausahaan`
2. Jalankan **MySQL** (Apache & MySQL di panel XAMPP).
3. Buka browser ke alamat:
   ```
   http://localhost/sipd-penatausahaan/install.php
   ```
4. Klik tombol **"Buat Database SIPD Penatausahaan"** untuk membuat database, tabel, dan data awal.
5. Buka halaman utama:
   ```
   http://localhost/sipd-penatausahaan/
   ```

## Akun Demo

Semua akun password: **123456**

| Username       | Peran              | Fungsi utama                               |
|----------------|--------------------|--------------------------------------------|
| `admin`        | Administrator      | Data master, lihat semua modul             |
| `penatausahaan`| Penatausahaan (PPK)| Buat SPP, terbitkan SPM & SP2D             |
| `bendahara`    | Bendahara          | Buku bank, buku pembantu, SPJ              |
| `verifikator`  | Verifikator        | Verifikasi & setujui/tolak SPP             |

## Alur Penggunaan (Skenario Demo)

1. Login sebagai **penatausahaan** → buat **SPP** → ajukan verifikasi.
2. Login sebagai **verifikator** → verifikasi dan **setujui** SPP.
3. Login sebagai **penatausahaan** → **terbitkan SPM** → setujui SPM → **terbitkan SP2D**.
4. **Cairkan** SP2D → otomatis tercatat di **buku bank** (via bendahara).
5. Login sebagai **bendahara** → buat **SPJ** dari SPP yang disetujui.

## Konfigurasi

Buka `config/config.php`:
```php
define('BASE_URL', '/sipd-penatausahaan/');
```
Buka `config/database.php` untuk mengubah host/user/pass database sesuai kebutuhan Anda.

## Struktur Folder

```
sipd-penatausahaan/
├── config/
│   ├── config.php        # Konfigurasi utama (BASE_URL, upload)
│   ├── database.php      # Koneksi database
│   └── functions.php     # Fungsi bantu (login, rupiah, upload, dsb)
├── database/
│   └── schema.sql        # Skema database + data awal
├── includes/
│   ├── header.php        # Sidebar & header
│   └── footer.php        # Footer & script
├── uploads/
│   └── dokumen/          # Tempat file upload
├── install.php           # Installer database
├── index.php             # Redirect
├── login.php / logout.php
├── dashboard.php
├── master_user.php       # Kelola pengguna
├── master_akun.php       # Rekening akun
├── master_kegiatan.php   # Program & kegiatan
├── master_skpd.php       # SKPD
├── spp_list.php          # Daftar SPP
├── spp_detail.php        # Detail + upload dokumen
├── spm_list.php          # Daftar SPM
├── sp2d_list.php         # Daftar SP2D
├── cetak_sp2d.php        # Cetak SP2D
├── verifikasi_spp.php    # Verifikasi verifikator
├── buku_bank.php         # Buku bank
├── buku_pembantu.php     # Buku pembantu
└── spj_list.php          # SPJ
```

## Disclaimer

Aplikasi ini dibuat **hanya untuk tujuan pembelajaran/demonstrasi** dan bukan aplikasi resmi SIPD dari pemerintah. Data di dalamnya bersifat contoh/simulasi.
