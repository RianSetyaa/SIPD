#!/bin/bash
# ============================================================
# Setup SIPD Penatausahaan (Unix/Mac)
# Membuat database + data awal, lalu jalankan server PHP.
# ============================================================
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_NAME="sipd_penatausahaan"

echo "==> [1/3] Membuat database $DB_NAME ..."
MYSQL_CMD="mysql -u $DB_USER"
if [ -n "$DB_PASS" ]; then
    MYSQL_CMD="$MYSQL_CMD -p$DB_PASS"
fi

$MYSQL_CMD -e "DROP DATABASE IF EXISTS $DB_NAME;" 2>/dev/null || true
$MYSQL_CMD < database/schema.sql

echo "==> [2/3] Mengatur password akun demo (123456) ..."
HASH=$(php -r "echo password_hash('123456', PASSWORD_BCRYPT);")
$MYSQL_CMD $DB_NAME -e "UPDATE users SET password='$HASH';"

# Sesuaikan koneksi database di config
sed -i.bak "s/define('DB_USER', 'root');/define('DB_USER', '$DB_USER');/" config/database.php
sed -i.bak "s/define('DB_PASS', '');/define('DB_PASS', '$DB_PASS');/" config/database.php
rm -f config/database.php.bak

echo "==> [3/3] Menjalankan server di http://localhost:8081 ..."
echo "    Tekan Ctrl+C untuk menghentikan server."
echo ""
echo "    Login: admin / penatausahaan / bendahara / verifikator"
echo "    Password: 123456"
echo ""
php -S localhost:8081
