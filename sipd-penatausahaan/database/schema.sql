-- ============================================================
-- DATABASE SIPD PENATAUSAHAAN
-- Aplikasi Pembelajaran Mahasiswa
-- ============================================================

CREATE DATABASE IF NOT EXISTS sipd_penatausahaan;
USE sipd_penatausahaan;

-- ========== Tabel Users (pengguna / multi-peran) ==========
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    nip VARCHAR(30) DEFAULT NULL,
    role ENUM('admin','penatausahaan','bendahara','verifikator') NOT NULL DEFAULT 'penatausahaan',
    skpd_id INT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========== Tabel SKPD/OPD ==========
CREATE TABLE IF NOT EXISTS skpd (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_skpd VARCHAR(20) NOT NULL UNIQUE,
    nama_skpd VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

-- ========== Tabel Rekening/Akun ==========
CREATE TABLE IF NOT EXISTS rekening_akun (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_akun VARCHAR(30) NOT NULL UNIQUE,
    nama_akun VARCHAR(255) NOT NULL,
    jenis ENUM('BELANJA','PENDAPATAN','PEMBIAYAAN') DEFAULT 'BELANJA'
) ENGINE=InnoDB;

-- ========== Tabel Program ==========
CREATE TABLE IF NOT EXISTS program (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_program VARCHAR(30) NOT NULL UNIQUE,
    nama_program VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- ========== Tabel Kegiatan ==========
CREATE TABLE IF NOT EXISTS kegiatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    kode_kegiatan VARCHAR(30) NOT NULL UNIQUE,
    nama_kegiatan VARCHAR(255) NOT NULL,
    anggaran DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (program_id) REFERENCES program(id)
) ENGINE=InnoDB;

-- ========== Tabel RKA (perencanaan per akun per kegiatan) ==========
CREATE TABLE IF NOT EXISTS rka (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kegiatan_id INT NOT NULL,
    akun_id INT NOT NULL,
    pagu DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (kegiatan_id) REFERENCES kegiatan(id),
    FOREIGN KEY (akun_id) REFERENCES rekening_akun(id)
) ENGINE=InnoDB;

-- ========== Tabel SPP (Surat Permintaan Pembayaran) ==========
CREATE TABLE IF NOT EXISTS spp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_spp VARCHAR(50) NOT NULL UNIQUE,
    tgl_spp DATE NOT NULL,
    jenis CHAR(2) NOT NULL COMMENT 'LS=langsung, GU=ganti uang, TU=tambah uang, UP=uang persediaan',
    uraian TEXT,
    kegiatan_id INT,
    akun_id INT,
    jumlah DECIMAL(15,2) DEFAULT 0,
    status ENUM('draft','diajukan','diverifikasi','disetujui','ditolak') DEFAULT 'draft',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kegiatan_id) REFERENCES kegiatan(id),
    FOREIGN KEY (akun_id) REFERENCES rekening_akun(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ========== Tabel Rincian SPP ==========
CREATE TABLE IF NOT EXISTS spp_rincian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    spp_id INT NOT NULL,
    uraian VARCHAR(255),
    akun_id INT,
    volume INT DEFAULT 1,
    satuan VARCHAR(20),
    harga DECIMAL(15,2) DEFAULT 0,
    jumlah DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (spp_id) REFERENCES spp(id) ON DELETE CASCADE,
    FOREIGN KEY (akun_id) REFERENCES rekening_akun(id)
) ENGINE=InnoDB;

-- ========== Tabel Dokumen SPP (upload) ==========
CREATE TABLE IF NOT EXISTS spp_dokumen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    spp_id INT NOT NULL,
    nama_dokumen VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (spp_id) REFERENCES spp(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========== Tabel SPM (Surat Perintah Membayar) ==========
CREATE TABLE IF NOT EXISTS spm (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_spm VARCHAR(50) NOT NULL UNIQUE,
    tgl_spm DATE NOT NULL,
    spp_id INT NOT NULL,
    jumlah DECIMAL(15,2) DEFAULT 0,
    tanggal_bayar DATE,
    status ENUM('draft','disetujui') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (spp_id) REFERENCES spp(id)
) ENGINE=InnoDB;

-- ========== Tabel SP2D (Surat Perintah Pencairan Dana) ==========
CREATE TABLE IF NOT EXISTS sp2d (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_sp2d VARCHAR(50) NOT NULL UNIQUE,
    tgl_sp2d DATE NOT NULL,
    spm_id INT NOT NULL,
    jumlah DECIMAL(15,2) DEFAULT 0,
    bank VARCHAR(50),
    no_rekening VARCHAR(50),
    status ENUM('proses','cair','gagal') DEFAULT 'proses',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (spm_id) REFERENCES spm(id)
) ENGINE=InnoDB;

-- ========== Tabel Buku Bank ==========
CREATE TABLE IF NOT EXISTS buku_bank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    no_bukti VARCHAR(50),
    uraian TEXT,
    masuk DECIMAL(15,2) DEFAULT 0,
    keluar DECIMAL(15,2) DEFAULT 0,
    saldo DECIMAL(15,2) DEFAULT 0,
    sp2d_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sp2d_id) REFERENCES sp2d(id)
) ENGINE=InnoDB;

-- ========== Tabel Buku Pembantu ==========
CREATE TABLE IF NOT EXISTS buku_pembantu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buku_tipe VARCHAR(30) COMMENT 'pajak, kegiatan, bank, panjar',
    tanggal DATE,
    kode_bukti VARCHAR(50),
    uraian TEXT,
    jumlah DECIMAL(15,2) DEFAULT 0,
    keterangan TEXT
) ENGINE=InnoDB;

-- ========== Tabel SPJ (Surat Pertanggungjawaban) ==========
CREATE TABLE IF NOT EXISTS spj (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_spj VARCHAR(50) NOT NULL UNIQUE,
    tgl_spj DATE,
    spp_id INT,
    jumlah DECIMAL(15,2) DEFAULT 0,
    status ENUM('draft','lengkap','ditolak') DEFAULT 'draft',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (spp_id) REFERENCES spp(id)
) ENGINE=InnoDB;

-- ========== Data Awal (Seed) ==========

-- SKPD
INSERT INTO skpd (kode_skpd, nama_skpd) VALUES
('1.01.01', 'Dinas Pendidikan'),
('1.02.01', 'Dinas Kesehatan'),
('1.03.01', 'Dinas Pekerjaan Umum'),
('1.04.01', 'Sekretariat Daerah');

-- Rekening Akun (Belanja)
INSERT INTO rekening_akun (kode_akun, nama_akun, jenis) VALUES
('5.2.2.05.01', 'Belanja Alat Tulis Kantor', 'BELANJA'),
('5.2.2.05.02', 'Belanja Bahan Material', 'BELANJA'),
('5.2.2.05.03', 'Belanja Jasa Kantor', 'BELANJA'),
('5.2.2.06.01', 'Belanja Cetak dan Penggandaan', 'BELANJA'),
('5.2.3.21.01', 'Belanja Makanan dan Minuman', 'BELANJA'),
('5.2.2.09.01', 'Belanja Langganan Listrik', 'BELANJA'),
('5.2.2.05.05', 'Belanja Alat Listrik', 'BELANJA'),
('5.2.2.06.02', 'Belanja Pengadaan Komputer', 'BELANJA');

-- Program
INSERT INTO program (kode_program, nama_program) VALUES
('1.01.01', 'Program Penunjang Urusan Pemerintahan Daerah'),
('1.01.02', 'Program Pendidikan Dasar');

-- Kegiatan
INSERT INTO kegiatan (program_id, kode_kegiatan, nama_kegiatan, anggaran) VALUES
(1, '1.01.01.001', 'Pengelolaan Administrasi Perkantoran', 500000000),
(1, '1.01.01.002', 'Penyediaan Sarana dan Prasarana Kantor', 300000000),
(2, '1.01.02.001', 'Operasional Sekolah Dasar', 750000000);

-- RKA (pagu per akun per kegiatan)
INSERT INTO rka (kegiatan_id, akun_id, pagu) VALUES
(1, 1, 50000000),
(1, 2, 100000000),
(1, 3, 75000000),
(2, 7, 60000000),
(2, 8, 150000000),
(3, 5, 200000000);

-- Users (password default: 123456)
-- import.php akan mengganti PLACEHOLDER dengan password_hash('123456')
INSERT INTO users (username, password, nama, nip, role) VALUES
('admin', 'PLACEHOLDER_HASH_123456', 'Administrator', '198001012010011001', 'admin'),
('penatausahaan', 'PLACEHOLDER_HASH_123456', 'Budi Santoso', '198505152011012001', 'penatausahaan'),
('bendahara', 'PLACEHOLDER_HASH_123456', 'Siti Aminah', '199003202014022002', 'bendahara'),
('verifikator', 'PLACEHOLDER_HASH_123456', 'Ahmad Fauzi', '198712032015031003', 'verifikator');
