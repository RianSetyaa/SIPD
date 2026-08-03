CREATE TABLE IF NOT EXISTS skpd (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(200) NOT NULL,
    alamat VARCHAR(255) DEFAULT NULL,
    kepala VARCHAR(120) DEFAULT NULL,
    bendahara_penerimaan VARCHAR(120) DEFAULT NULL,
    nip_bendahara VARCHAR(40) DEFAULT NULL,
    owner_id INT DEFAULT NULL,            -- pemilik skpd (mahasiswa)
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(120) NOT NULL,
    skpd_id INT DEFAULT NULL,
    role ENUM('admin','bendahara','verifikator','ppk','mahasiswa') DEFAULT 'bendahara',
    nim VARCHAR(40) DEFAULT NULL,
    prodi VARCHAR(120) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (skpd_id) REFERENCES skpd(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS rekening (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(30) NOT NULL,              -- contoh 4.1.1.01.01
    nama VARCHAR(250) NOT NULL,
    level INT DEFAULT 1,                        -- 1=kelompok,2=jenis,... 5=rincian
    induk_kode VARCHAR(30) DEFAULT NULL,
    jenis ENUM('PAD','DANA_PERIMBANGAN','LAIN_PENDAPATAN') DEFAULT 'PAD',
    skpd_id INT DEFAULT NULL,
    UNIQUE KEY uq_rek_kode_skpd (kode, skpd_id),  -- kode boleh sama antar SKPKD berbeda
    FOREIGN KEY (skpd_id) REFERENCES skpd(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS dpa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skpd_id INT NOT NULL,
    rekening_id INT NOT NULL,
    tahun VARCHAR(4) DEFAULT '200B',
    anggaran DECIMAL(18,2) DEFAULT 0,
    FOREIGN KEY (skpd_id) REFERENCES skpd(id) ON DELETE CASCADE,
    FOREIGN KEY (rekening_id) REFERENCES rekening(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS skp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_skp VARCHAR(50) NOT NULL,
    tanggal DATE NOT NULL,
    skpd_id INT NOT NULL,
    rekening_id INT NOT NULL,
    wajib_pajak VARCHAR(200) DEFAULT '-',
    jumlah DECIMAL(18,2) DEFAULT 0,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (skpd_id) REFERENCES skpd(id) ON DELETE CASCADE,
    FOREIGN KEY (rekening_id) REFERENCES rekening(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS tbp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_tbp VARCHAR(50) NOT NULL,
    tanggal DATE NOT NULL,
    skpd_id INT NOT NULL,
    skp_id INT NOT NULL,             -- pelunasan SKP tertentu
    jumlah DECIMAL(18,2) DEFAULT 0,
    setor_tunai ENUM('T','B') DEFAULT 'T',  -- T=bendahara, B=bank
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tbp_skp (skp_id),
    FOREIGN KEY (skpd_id) REFERENCES skpd(id) ON DELETE CASCADE,
    FOREIGN KEY (skp_id) REFERENCES skp(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS sts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_sts VARCHAR(50) NOT NULL,
    tanggal DATE NOT NULL,
    skpd_id INT NOT NULL,
    tbp_id INT NOT NULL,             -- STS melunasi TBP tertentu
    jumlah DECIMAL(18,2) DEFAULT 0,
    bank VARCHAR(120) DEFAULT 'BPD',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_sts_tbp (tbp_id),
    FOREIGN KEY (skpd_id) REFERENCES skpd(id) ON DELETE CASCADE,
    FOREIGN KEY (tbp_id) REFERENCES tbp(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS jurnal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skpd_id INT DEFAULT NULL,
    no_jurnal VARCHAR(50) DEFAULT NULL,
    tanggal DATE NOT NULL,
    jenis ENUM('SKP','TBP','STS') NOT NULL,
    ref_id INT NOT NULL,             -- id pada tabel skp/tbp/sts
    no_dokumen VARCHAR(50) DEFAULT NULL,
    uraian VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS jurnal_rincian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jurnal_id INT NOT NULL,
    rekening_id INT NULL,        -- referensi COA pendapatan (NULL/lain=akun akuntansi internal Kas/Piutang)
    akun VARCHAR(120) NOT NULL,      -- nama akun
    debet DECIMAL(18,2) DEFAULT 0,
    kredit DECIMAL(18,2) DEFAULT 0,
    basis ENUM('LO','LRA') DEFAULT 'LO',
    FOREIGN KEY (jurnal_id) REFERENCES jurnal(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS buku_kas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    kode ENUM('SKP','TBP','STS') NOT NULL,
    ref_id INT NOT NULL,
    no_dokumen VARCHAR(50),
    uraian VARCHAR(255),
    debet DECIMAL(18,2) DEFAULT 0,     -- penerimaan
    kredit DECIMAL(18,2) DEFAULT 0,    -- pengeluaran
    saldo DECIMAL(18,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS laporan_harian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    skpd_id INT NOT NULL,
    saldo_awal DECIMAL(18,2) DEFAULT 0,
    penerimaan DECIMAL(18,2) DEFAULT 0,
    setoran DECIMAL(18,2) DEFAULT 0,
    saldo_akhir DECIMAL(18,2) DEFAULT 0,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_lap_tanggal (tanggal, skpd_id)
) ENGINE=InnoDB;
INSERT INTO skpd (kode, nama, alamat, kepala, bendahara_penerimaan, nip_bendahara) VALUES
('1.20.05', 'Badan Pengelola Keuangan Daerah (SKPKD)', 'Jl. Raya Cimahi No.1', 'Kepala BPKD', 'Bendahara Penerimaan', '197001012000031001')
ON DUPLICATE KEY UPDATE nama=VALUES(nama);
SET @skpkd = (SELECT id FROM skpd WHERE kode='1.20.05');
INSERT INTO rekening (kode, nama, level, induk_kode, jenis, skpd_id) VALUES
('4.1','Pendapatan Asli Daerah',1,NULL,'PAD',@skpkd),
('4.1.1','Hasil Pajak Daerah',2,'4.1','PAD',@skpkd),
('4.1.1.01','Pajak Hotel',3,'4.1.1','PAD',@skpkd),
('4.1.1.01.01','Hotel Bintang Berlian',4,'4.1.1.01','PAD',@skpkd),
('4.1.1.01.02','Hotel Bintang Lima',4,'4.1.1.01','PAD',@skpkd),
('4.1.1.01.03','Hotel Bintang Empat',4,'4.1.1.01','PAD',@skpkd),
('4.1.1.01.04','Hotel Bintang Tiga',4,'4.1.1.01','PAD',@skpkd),
('4.1.1.01.05','Hotel Bintang Dua',4,'4.1.1.01','PAD',@skpkd),
('4.1.1.01.06','Hotel Bintang Satu',4,'4.1.1.01','PAD',@skpkd),
('4.1.1.01.07','Hotel Melati Tiga',4,'4.1.1.01','PAD',@skpkd),
('4.1.1.02','Pajak Restoran',3,'4.1.1','PAD',@skpkd),
('4.1.1.02.01','Restoran',4,'4.1.1.02','PAD',@skpkd),
('4.1.1.02.02','Rumah Makan',4,'4.1.1.02','PAD',@skpkd),
('4.1.1.02.03','Cafe',4,'4.1.1.02','PAD',@skpkd),
('4.1.1.02.04','Kantin',4,'4.1.1.02','PAD',@skpkd),
('4.1.1.03','Pajak Hiburan',3,'4.1.1','PAD',@skpkd),
('4.1.1.03.01','Tontonan Film/Bioskop',4,'4.1.1.03','PAD',@skpkd),
('4.1.1.03.05','Pameran',4,'4.1.1.03','PAD',@skpkd),
('4.1.1.03.06','Diskotik',4,'4.1.1.03','PAD',@skpkd),
('4.1.1.03.13','Pacuan Kuda',4,'4.1.1.03','PAD',@skpkd),
('4.1.1.04','Pajak Reklame',3,'4.1.1','PAD',@skpkd),
('4.1.1.04.01','Reklame Papan/Billboard',4,'4.1.1.04','PAD',@skpkd),
('4.1.1.04.02','Reklame Kain',4,'4.1.1.04','PAD',@skpkd),
('4.1.1.04.09','Reklame Film/Slide',4,'4.1.1.04','PAD',@skpkd),
('4.1.1.05','Pajak Penerangan Jalan',3,'4.1.1','PAD',@skpkd),
('4.1.1.05.01','Pajak Penerangan Jalan PLN',4,'4.1.1.05','PAD',@skpkd),
('4.1.1.07','Pajak Parkir',3,'4.1.1','PAD',@skpkd),
('4.1.1.07.01','Pajak Parkir',4,'4.1.1.07','PAD',@skpkd),
('4.1.1.08','Pajak Air Bawah Tanah',3,'4.1.1','PAD',@skpkd),
('4.1.1.08.01','Pajak Air Bawah Tanah',4,'4.1.1.08','PAD',@skpkd),
('4.1.1.09','Pajak Sarang Burung Walet',3,'4.1.1','PAD',@skpkd),
('4.1.1.09.01','Pajak Sarang Burung Walet',4,'4.1.1.09','PAD',@skpkd),
('4.1.1.10','Pajak Lingkungan',3,'4.1.1','PAD',@skpkd),
('4.1.1.10.01','Pajak Lingkungan',4,'4.1.1.10','PAD',@skpkd),
('4.1.4','Lain-lain Pendapatan Asli Daerah yang Sah',2,'4.1','PAD',@skpkd),
('4.1.4.01','Hasil Penjualan Aset Daerah yang Tidak Dipisahkan',3,'4.1.4','PAD',@skpkd),
('4.1.4.01.01','Pelepasan Hak Atas Tanah',4,'4.1.4.01','PAD',@skpkd),
('4.1.4.03','Pendapatan Bunga Deposito',3,'4.1.4','PAD',@skpkd),
('4.1.4.03.01','Rekening Deposito Pada Bank',4,'4.1.4.03','PAD',@skpkd),
('4.1.4.04','Tuntutan Ganti Kerugian Daerah',3,'4.1.4','PAD',@skpkd),
('4.1.4.04.01','Kerugian Uang',4,'4.1.4.04','PAD',@skpkd)
ON DUPLICATE KEY UPDATE nama=VALUES(nama);
INSERT INTO users (username, password, nama, skpd_id, role) VALUES
('admin','PLACEHOLDER_HASH_123456','Administrator',@skpkd,'admin'),
('bendahara','PLACEHOLDER_HASH_123456','Bendahara Penerimaan',@skpkd,'bendahara'),
('verifikator','PLACEHOLDER_HASH_123456','Verifikator',@skpkd,'verifikator'),
('ppk','PLACEHOLDER_HASH_123456','Pejabat Penatausahaan',@skpkd,'ppk')
ON DUPLICATE KEY UPDATE username=VALUES(username);
