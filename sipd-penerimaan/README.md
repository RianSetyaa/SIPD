# SIPD Penerimaan (SKPKD) — Penatausahaan Penerimaan Pendapatan

Aplikasi web pembelajaran untuk **Praktikum Akuntansi Pemerintahan** (mata kuliah SKPKD),
berbasis Modul 2 — Penatausahaan Penerimaan Pendapatan SKPKD sebagai SKPD.

Aplikasi ini membantu mahasiswa memahami **alur penerimaan kas** dan **pencatatan jurnal
otomatis** pada pemerintah daerah (SKPKD).

---

## Fitur Utama

| Modul | Fungsi |
|-------|--------|
| **SKP** (Surat Ketetapan Pajak) | Bendahara menerbitkan SKP → aplikasi otomatis mencatat **Piutang Pajak** (basis akrual / LO) |
| **TBP** (Tanda Bukti Penerimaan) | Wajib Pajak membayar ke Bendahara → **Kas di Bendahara** bertambah, Piutang berkurang |
| **STS** (Surat Tanda Setoran) | Bendahara menyetor ke Kas Daerah → **Kas di Kas Daerah** bertambah, dan **Pendapatan LRA** terakui |
| **Jurnal Penerimaan** | Menampilkan seluruh jurnal otomatis (LO & LRA) dari SKP/TBP/STS |
| **Buku Kas & Rekap** | Buku kas penerimaan berjalan + rekap per jenis pajak |
| **Laporan / LPJ** | Register penerimaan SKP→TBP→STS yang dapat difilter & dicetak |
| **Master Rekening** | Kelola kode rekening pendapatan daerah |

### Alur & Jurnal yang Diotomatisasi

```
SKP terbit  →  Dr. Piutang Pajak             Cr. Pendapatan LO   (akrual)
TBP dibayar →  Dr. Kas di Bendahara          Cr. Piutang Pajak
STS disetor →  Dr. Kas di Kas Daerah         Cr. Kas di Bendahara (LO)
             + Dr. Kas di Kas Daerah (LRA)   Cr. Pendapatan LRA   (kas)
```

---

## Kebutuhan Sistem

- **PHP** 7.4+ (disarankan 8.x) dengan ekstensi `mysqli`
- **MySQL / MariaDB** 5.7+
- Browser modern (Chrome/Firefox/Edge)

---

## Cara Instalasi

### Opsi A — Terminal (macOS / Linux)
1. Pastikan MySQL berjalan dan PHP terinstall.
2. Dari folder proyek, jalankan:
   ```bash
   chmod +x setup.sh
   ./setup.sh
   ```
3. Buka `http://localhost:8082` lalu login.

### Opsi B — XAMPP / Laragon (Windows)
1. Salin folder `sipd-penerimaan` ke `C:\xampp\htdocs\` (atau `www` Laragon).
2. Pastikan MySQL Apache aktif.
3. Buka `http://localhost/sipd-penerimaan/install.php`
4. Isi kredensial database (biasanya `root`, password kosong).
5. Klik **Install** → login.

> **Catatan:** jika proyek diletakkan bukan di `sipd-penerimaan`, sesuaikan
> `BASE_URL` di file `config/config.php`.

### Opsi C — Hosting Online (cPanel / Plesk / shared hosting)

1. **Buat dulu database kosong** di panel hosting
   (cPanel → MySQL Databases → Create New Database, lalu *Add User to Database* dengan ALL PRIVILEGES).
   Catat Nama Database (mis. `user_db_sipd`), Username, dan Password.
   > Hosting shared **tidak mengizinkan installer membuat database** — wajib dibuat manual.
2. Upload seluruh folder aplikasi ke `public_html` (atau subfolder, mis. `public_html/penerimaan`).
3. Buka `https://domain-anda/folderaplikasi/install.php`, isi data DB dari langkah 1, klik **Install**.
   - `BASE_URL` akan dideteksi otomatis sesuai letak folder.

> **Jalur cadangan (jika installer bermasalah): impor manual via phpMyAdmin**
> 1. Masuk phpMyAdmin → pilih database (buat kosong dulu) → *Import* → pilih file
>    **`database/schema_import.sql`** → Go.
> 2. Buka **`hash_password.php`** sekali di browser untuk men-set password demo `123456`, lalu hapus file itu.
> 3. Pastikan **`config/database_config.php`** ada (lihat `config/database_config_sample.php`, sesuaikan kredensial).

---

## Sistem Multi-Mahasiswa & Multi-Daerah

Aplikasi mendukung **banyak mahasiswa** yang mendaftar dan mengerjakan **daerah (SKPKD)
masing-masing yang terpisah**. Data setiap mahasiswa **tidak tercampur**.

### Alur Penggunaan
1. **Daftar** lewat halaman `register.php` — isi nama, NIM, prodi, dan pilih **nama daerah
   (Kab/Kota)**. Sistem otomatis membuat SKPKD milik mahasiswa tersebut beserta rekening
   pendapatan standar.
2. **Login** sebagai mahasiswa → otomatis berperan **Bendahara** pertama.
3. Gunakan **pemilih peran (role switcher)** di pojok kanan atas untuk berlatih semua posisi:
   **Bendahara → Verifikator → PPK** — semuanya memakai data SKPKD miliknya sendiri.
4. Admin/dosen dapat melihat **semua mahasiswa & ringkasan data** lewat menu *Daftar Mahasiswa*.

### Bagaimana data dipisahkan
- Setiap mahasiswa memiliki **1 SKPKD (daerah) sendiri** yang ditandai pemilik (`owner_id`).
- Semua dokumen (SKP/TBP/STS) dan jurnal melekat pada `skpd_id` pemilik.
- Saat login, aplikasi hanya menampilkan transaksi `skpd_id` milik user tersebut (kecuali admin).

### Akun
| Username | Password | Peran |
|----------|----------|-------|
| `bendahara` | `123456` | Bendahara Penerimaan (SKPKD contoh Cimahi) |
| `verifikator` | `123456` | Verifikator |
| `ppk` | `123456` | Pejabat Penatausahaan |
| `admin` | `123456` | Administrator / Dosen (melihat semua) |
| *(mahasiswa daftar sendiri)* | ditentukan saat daftar | Mahasiswa — bisa semua peran |

---

## Akun Demo (password semua: `123456`)

| Username | Role |
|----------|------|
| `bendahara` | Bendahara Penerimaan (mengelola SKP/TBP/STS) |
| `verifikator` | Verifikator (melihat laporan) |
| `ppk` | Pejabat Penatausahaan Keuangan (master data) |
| `admin` | Administrator |

---

## Struktur Folder

```
sipd-penerimaan/
├── config/
│   ├── config.php          # BASE_URL, konstanta
│   ├── database.php        # bootstrap koneksi + session
│   ├── database_config.php # (dibuat installer/setup)
│   └── functions.php       # helper + logika jurnal otomatis
├── database/
│   └── schema.sql          # skema + data awal (rekening PAD)
├── includes/
│   ├── header.php          # sidebar & navigasi role-aware
│   └── footer.php          # script & DataTables
├── login.php / logout.php / index.php / dashboard.php
├── skp_list.php / tbp_list.php / sts_list.php
├── jurnal_list.php / buku_kas.php / laporan.php
├── master_rekening.php
├── install.php
└── setup.sh
```

---

## Catatan Pembelajaran (Sesuai Modul)

Transaksi contoh (Januari 200B) yang dapat diinput mahasiswa:

| Tanggal | No | Uraian | Nilai |
|--------|----|--------|-------|
| 3 Jan | SKP | Diterbitkan SKP Pajak Hotel Bintang Lima | 100.000.000 |
| 5 Jan | TBP | Diterima pelunasan piutang hotel bintang lima | 100.000.000 |
| 6 Jan | STS | Disetorkan ke Kas Daerah | 100.000.000 |

Mahasiswa cukup memasukkan **nomor & tanggal**, memilih jenis pajak, dan nilainya;
jurnal serta buku kas **terisi otomatis** sehingga fokus belajar ada pada *alur*
dan *konsep akrual vs. kas*.
