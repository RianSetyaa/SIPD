#!/usr/bin/env bash
# ============================================================
# Setup SIPD Penerimaan (SKPKD) - untuk macOS / Linux / XAMPP
# ============================================================
set -e

echo "=============================================="
echo "  SIPD PENERIMAAN - Penatausahaan SKPKD"
echo "  Strategi Penyiapan Database & Server"
echo "=============================================="

# Cek MySQL tersedia
if ! command -v mysql >/dev/null 2>&1; then
  echo "[ERROR] MySQL/mariadb tidak ditemukan. Pastikan sudah terinstall & berjalan."
  exit 1
fi

echo "[1/4] Membuat database & tabel dari database/schema.sql ..."
mysql -u root < database/schema.sql 2>/dev/null || mysql -u root -p < database/schema.sql
echo "      -> database 'sipd_penerimaan' siap."

echo "[2/4] Membuat config/database_config.php ..."
cat > config/database_config.php <<'PHP'
<?php
// KONFIGURASI DATABASE - SIPD Penerimaan (auto-generated oleh setup.sh)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sipd_penerimaan');
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$koneksi) { die('Koneksi database gagal: ' . mysqli_connect_error()); }
mysqli_set_charset($koneksi, 'utf8mb4');
?>
PHP
echo "      -> config/database_config.php dibuat."

echo "[3/4] Meng-hash password pengguna demo (123456) ..."
HASH=$(php -r "echo password_hash('123456', PASSWORD_BCRYPT);")
mysql -u root -e "UPDATE \`sipd_penerimaan\`.\`users\` SET password='$HASH' WHERE password='PLACEHOLDER_HASH_123456';" 2>/dev/null || \
mysql -u root -p -e "UPDATE \`sipd_penerimaan\`.\`users\` SET password='$HASH' WHERE password='PLACEHOLDER_HASH_123456';"
echo "      -> Password demo selesai."

echo "[4/4] Menjalankan server PHP di http://localhost:8082 ..."
echo "      Tekan Ctrl+C untuk menghentikan."
php -S localhost:8082

