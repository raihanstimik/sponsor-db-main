# Indonesia Congress Management (ICM) — Sponsor & PIC Digital Archive

> **Platform Manajemen Database Sponsor, Kontak PIC, dan Arsip Kegiatan Kongres Medis**  
> Dibangun dengan standar enterprise untuk performa tinggi, keandalan data (*Single Source of Truth*), dan kemudahan operasional staf sponsorship.

---

## 📑 Dokumentasi Resmi & Panduan Migrasi

- 📘 **[Panduan Migrasi Arsitektur & Sistem Antar-Bahasa Pemrograman (docs/PANDUAN_MIGRASI_ARSITEKTUR_DAN_SISTEM.md)](docs/PANDUAN_MIGRASI_ARSITEKTUR_DAN_SISTEM.md)**  
  *Dokumentasi teknis lengkap dan independen terhadap bahasa pemrograman (language-agnostic blueprint). Berisi spesifikasi domain bisnis, topologi arsitektur, ERD & DDL skema database lengkap, pseudocode algoritma inti (Phone Normalizer, Canonical Company Naming, Smart Import Multi-Sheet, Multi-Token Smart Search, Audit Trail), matriks RBAC, kontrak REST API, spesifikasi UI/UX tokens, 122 acceptance criteria test cases, serta 7-fase roadmap migrasi ke Node.js/NestJS, Go, Python/FastAPI, atau .NET Core.*

- 🎨 **[Desain Sistem & Standar Antarmuka UI/UX (DESIGN-ICM.md)](DESIGN-ICM.md)**  
  *Panduan identitas visual ICM: Palet warna Royal Navy & Congress Orange, dual mode (Light / Deep Navy Command Center), tipografi, kepadatan data enterprise, dan mikro-animasi CSS 60 FPS.*

---

## 🚀 Fitur Utama Sistem

1. **Smart Search Multi-Token**: Pencarian kontak secepat kilat melintasi nama PIC, perusahaan sponsor, kegiatan, kategori medis, dan nomor telepon.
2. **Normalisasi Otomatis Nomor WhatsApp**: Membersihkan karakter non-digit, mengonversi format lokal (`08...`, `+62...`, `6208...`) menjadi standar E.164 Indonesia (`628...`), memvalidasi panjang 10–13 digit, dan menyediakan tautan langsung direct-to-WhatsApp.
3. **Canonical Company Resolver & Deteksi Duplikat**: Pencegahan duplikasi nama perusahaan dengan kanonisasi nama dan penanganan aman *soft-deleted records* (mencegah error MySQL 1062).
4. **Smart Multi-Sheet Excel/CSV Importer**: Ekstraksi multi-sheet otomatis, deteksi header cerdas (Indonesia & Inggris), pembersih baris sampah (*junk rows*), serta preview 2-fase sebelum disimpan ke database.
5. **Role-Based Access Control (RBAC)**: Pemisahan peran Administrator dan Karyawan, proteksi akun baru (*pending approval*), serta fitur penyamaran akun (*impersonation*) untuk troubleshooting.
6. **Differential Audit Trail**: Pencatatan riwayat perubahan data (perbedaan nilai sebelum dan sesudah) dengan isolasi privasi (karyawan hanya dapat melihat aktivitas miliknya).
7. **Ekspor CSV Kompatibel Excel**: Ekspor data berkecepatan tinggi dengan UTF-8 Byte Order Mark (`\xEF\xBB\xBF`) agar tidak rusak saat dibuka di Microsoft Excel.

---

## 🛠️ Tech Stack Saat Ini

- **Framework**: PHP 8.2+ / Laravel 12
- **Admin Engine**: Filament v3 & Livewire 3
- **Database**: MySQL 8.0+ / MariaDB 10.5+
- **Styling**: Tailwind CSS & Custom CSS Themes (`resources/css/theme.css`)
- **Excel Engine**: PhpSpreadsheet / Maatwebsite Excel

---

## ⚡ Petunjuk Instalasi & Menjalankan Aplikasi

### 1. Kloning & Dependensi
```bash
git clone <repository_url>
cd sponsor-db-main
composer install
npm install && npm run build
```

### 2. Konfigurasi Lingkungan (.env)
```bash
cp .env.example .env
php artisan key:generate
```
Sesuaikan konfigurasi database pada `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sponsor_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Migrasi & Seeder Database
```bash
php artisan migrate --seed
```

### 4. Menjalankan Server Pengembangan
```bash
php artisan serve
```
Buka browser pada `http://127.0.0.1:8000/admin`.

---

## 🧪 Pengujian Unit & Integrasi (Test Suite)

Seluruh logika bisnis dan keamanan dilindungi oleh 122 automated test cases (487 assertions):
```bash
php artisan test
```

---

## 📄 Lisensi
Hak Cipta © 2024–2026 Indonesia Congress Management (ICM). Seluruh hak dilindungi undang-undang.

