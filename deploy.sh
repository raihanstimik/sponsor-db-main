#!/usr/bin/env bash
# =============================================================================
# deploy.sh — ICM Sponsor DB Production Deploy Script
# =============================================================================
# Jalankan dari root project di server produksi:
#   chmod +x deploy.sh
#   ./deploy.sh
#
# Prasyarat:
#   - PHP 8.2+, Composer, Node 20+, NPM
#   - MySQL 8.x berjalan dan database sudah dibuat
#   - File .env sudah dikonfigurasi (salin dari .env.example)
# =============================================================================

set -e

echo "==============================="
echo "  ICM Sponsor DB — Production Deploy"
echo "==============================="

# 1. Aktifkan maintenance mode agar request tidak masuk saat deploy
echo "→ Maintenance mode ON"
php artisan down --retry=60

# 2. Tarik kode terbaru (jika pakai git)
# echo "→ Git pull"
# git pull origin main

# 3. Install PHP dependencies (tanpa dev packages untuk produksi)
echo "→ Composer install (no-dev)"
composer install --no-dev --no-interaction --optimize-autoloader --prefer-dist

# 4. Install Node dependencies & build aset
echo "→ NPM build"
npm ci --prefer-offline
npm run build

# 5. Jalankan migrasi database
echo "→ Database migrations"
php artisan migrate --force

# 6. Sinkronisasi storage symlink
echo "→ Storage link"
php artisan storage:link 2>/dev/null || true

# 7. Bersihkan semua cache lama
echo "→ Clearing old caches"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# 8. Compile cache baru (optimasi produksi)
echo "→ Caching config, routes, views, events"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 9. Optimasi Composer classmap
echo "→ Optimizing autoloader"
composer dump-autoload --optimize --no-dev

# 10. Atur izin folder (sesuaikan user web server: www-data / nginx / apache)
echo "→ Setting directory permissions"
chmod -R 775 storage bootstrap/cache
# chown -R www-data:www-data storage bootstrap/cache

# 11. Non-aktifkan maintenance mode
echo "→ Maintenance mode OFF"
php artisan up

echo ""
echo "✓ Deploy selesai!"
echo ""
echo "Catatan post-deploy:"
echo "  - Verifikasi APP_DEBUG=false di .env"
echo "  - Pastikan LOG_LEVEL=error di .env"
echo "  - Jalankan: php artisan kontak:normalisasi-nomor (sekali, opsional)"

