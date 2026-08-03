# 🚀 Deployment SIPD Penatausahaan ke Hosting
## Domain: **sipd.loketiq.com**

Versi zip ini sudah disesuaikan khusus untuk di-upload ke hosting shared
(di-root domain). Berbeda dengan versi lokal, versi hosting ini:

- `BASE_URL` diset ke `/` (karena domain mengarah langsung ke folder root).
- Instalasi database dilakukan lewat **web installer** yang meminta
  kredensial database hosting Anda (tidak lagi hardcode `root`).
- Konfigurasi database otomatis ditulis ke file `config/database_config.php`.
- File `install.php` otomatis **dihapus** setelah berhasil, demi keamanan.
- Disertai `.htaccess` untuk keamanan (blokir akses folder database & config).

---

## 📦 Langkah Upload ke Hosting

1. **Buka zip** ini dan upload SELURUH isi folder ke **public_html** / **htdocs**
   (folder root domain) lewat cPanel → File Manager / FTP.

   Pastikan isi zip diletakkan langsung di root,
   **bukan** di dalam sub-folder `sipd-penatausahaan/`.

2. **Buat database & user MySQL** di cPanel → MySQL Databases.
   Catat:
   - Nama database (contoh: `sipdlokt_db`)
   - Nama user (contoh: `sipdlokt_dbuser`)
   - Password user
   - Host (biasanya `localhost`)

3. **Jalankan installer** di browser:
   ```
   https://sipd.loketiq.com/install.php
   ```
   Isi data database di atas, lalu klik **Install Sekarang**.

4. Setelah sukses, buka halaman utama:
   ```
   https://sipd.loketiq.com/
   ```
   dan login.

---

## 🌐 Akun Demo (Password semua: `123456`)

| Username        | Peran               | Fungsi utama                                |
|-----------------|---------------------|---------------------------------------------|
| `admin`         | Administrator       | Data master, lihat semua modul              |
| `penatausahaan` | Penatausahaan (PPK) | Buat SPP, terbitkan SPM & SP2D              |
| `bendahara`     | Bendahara           | Buku bank, buku pembantu, SPJ               |
| `verifikator`   | Verifikator         | Verifikasi & setujui/tolak SPP              |

---

## ⚙️ Cara Mengubah BASE_URL (jika perlu)

Buka `config/config.php`:

```php
define('BASE_URL', '/');   // sudah benar untuk root domain
```

Jika nanti aplikasi dipindah ke sub-folder (mis. `sipd.loketiq.com/aplikasi/`),
ubah menjadi:

```php
define('BASE_URL', '/aplikasi/');
```

---

## 🔒 Keamanan (sudah diatur)

- Folder `database/` & `config/` diblokir dari akses web.
- Folder `uploads/` tidak bisa mengeksekusi script PHP.
- File `install.php` terhapus setelah instalasi sukses.

> Selalu buat **backup database** (cPanel → Backup) secara berkala.

— **SIPD Penatausahaan** (Aplikasi Pembelajaran Mahasiswa)
