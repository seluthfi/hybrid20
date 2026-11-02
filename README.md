# HYBRID 20

Aplikasi web responsif dan Progressive Web App (PWA) fitness pribadi berbasis Laravel 13 untuk memantau program 20 minggu (cutting, gym, dan lari menuju 10K).

Sistem menyimpan seluruh data di server database pribadi, mencatat log latihan gym, lari, nutrisi harian, langkah, dan pengukuran tubuh, serta memantau progres template program 20 minggu dengan analitik tren dan kontrol privasi ketat.

---

## Spesifikasi & Lingkungan

- **Framework:** Laravel 13.x (PHP ^8.4.1 / disarankan PHP 8.5)
- **Frontend:** Blade + Tailwind CSS 4 (tanpa `tailwind.config.js`) + Alpine.js + Vite
- **Database:** SQLite (lokal / development)
- **Lingkungan Pengembang:** Windows + Laragon + PowerShell
- **Panduan Proyek & Aturan Agen:**
  - [PLAN.md](PLAN.md) — Sumber kebenaran arsitektur, aturan keras, dan panduan fase
  - [AGENTS.md](AGENTS.md) — Aturan kerja agen AI dan pembatasan teknis

---

## Panduan Instalasi (Windows / PowerShell)

Jalankan perintah berikut di PowerShell dari direktori proyek:

```powershell
# 1. Pasang dependensi PHP
composer install

# 2. Siapkan berkas konfigurasi lingkungan
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate

# 3. Siapkan database SQLite lokal
if (-not (Test-Path database\database.sqlite)) { New-Item database\database.sqlite -ItemType File }
php artisan migrate

# 4. Pasang dependensi JavaScript dan kompilasi aset
npm install   # atau: npm.cmd install
npm run build # atau: npm.cmd run build

# 5. Jalankan server pengembang (Laravel serve + Vite dev)
composer dev
# Atau jalankan secara terpisah di dua terminal:
# Terminal 1: php artisan serve
# Terminal 2: npm run dev
```

---

## Akun Pengguna

HYBRID 20 adalah aplikasi single-user tanpa registrasi publik. Pembuatan akun dilakukan melalui perintah Artisan (tersedia mulai Fase 1):

```powershell
php artisan hybrid:create-user "Nama Pengguna" email@contoh.com
```

---

## Lisensi & Hak Cipta

Proyek pribadi HYBRID 20. Semua hak cipta dilindungi.
