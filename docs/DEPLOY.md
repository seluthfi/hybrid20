# Panduan Deployment Produksi: HYBRID 20

Dokumen ini memuat panduan lengkap penerapan (deployment) aplikasi HYBRID 20 pada lingkungan produksi sesuai spesifikasi arsitektur PLAN.md §2, §12, §13 (Fase 8), dan §14.

---

## 1. Prasyarat Server & Sistem

HYBRID 20 menggunakan Laravel 13 dan dependensi Symfony 8.1.
- **PHP:** Versi **≥ 8.4.1** (sangat disarankan **PHP 8.5**).
- **Ekstensi PHP Wajib:**
  - `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd` (atau `imagick`), `intl`, `json`, `libxml`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_sqlite` (atau `pdo_mysql`), `session`, `tokenizer`, `xml`, `xmlwriter`, `zip`.
- **Node.js & NPM:** Node.js LTS (v20+ / v22+) & npm 10+.
- **Database:** SQLite (bawaan single-user) atau MySQL 8.0+ / MariaDB 10.11+.
- **Web Server:** Nginx, Caddy, atau Apache dengan dukungan **HTTPS wajib** (TLS 1.3 / Let's Encrypt).

---

## 2. Alur Instalasi di Server Produksi

Jalankan perintah berikut di direktori root server:

```bash
# 1. Unduh / Clone repository
git clone <url-repo> /var/www/hybrid20
cd /var/www/hybrid20

# 2. Instal dependensi Composer (tanpa dev)
composer install --no-dev --optimize-autoloader

# 3. Instal dependensi frontend & build aset statis
npm ci
npm run build
```

---

## 3. Konfigurasi Lingkungan (`.env`)

Salin konfigurasi default dan amankan:

```bash
cp .env.example .env
php artisan key:generate
```

Pastikan variabel-variabel penting berikut disetel dengan tepat:

```env
APP_NAME="HYBRID 20"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://hybrid20.domainmu.com

LOG_CHANNEL=daily
LOG_LEVEL=error

# Database (Default SQLite)
DB_CONNECTION=sqlite
# Pastikan berkas database ada:
# touch /var/www/hybrid20/database/database.sqlite

# Keamanan Sesi & Cookie (Wajib HTTPS)
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Storage Lokal Privat
FILESYSTEM_DISK=local
```

---

## 4. Izin Akses Direktori (Permissions) & Keamanan Storage

Aplikasi menyimpan foto progres pribadi pada disk privat (`storage/app/private/progress-photos/{user_id}/`).

> **PENTING (R8 & §12):**  
> Dilarang membuat symbolic link publik (`php artisan storage:link`) untuk direktori `storage/app/private`. Semua foto progres dilayani melalui route terotorisasi (`/progress-photos/{id}/image`).

Setel kepemilikan dan izin folder:

```bash
# Sesuaikan 'www-data' dengan user web server kamu
chown -R www-data:www-data /var/www/hybrid20
chmod -R 775 /var/www/hybrid20/storage /var/www/hybrid20/bootstrap/cache

# Jika memakai SQLite:
chmod 775 /var/www/hybrid20/database
chmod 664 /var/www/hybrid20/database/database.sqlite
```

---

## 5. Migrasi & Pembuatan Akun Awal

HYBRID 20 **tidak menyediakan registrasi publik** demi keamanan data pribadi.

```bash
# Jalankan migrasi database
php artisan migrate --force

# Buat akun pemilik aplikasi pertama kali:
php artisan hybrid:create-user "Nama Pemilik" email@domainmu.com
```

Perintah di atas akan meminta kata sandi secara aman dan langsung menyiapkan data target pengaturan fisik serta program 20 minggu dengan tanggal mulai hari Senin.

---

## 6. Optimasi Kinerja Produksi

Kompilasi dan cache semua konfigurasi, route, dan view:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Jika ada pembaruan kode di kemudian hari:

```bash
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

---

## 7. Konfigurasi Web Server (Nginx)

Contoh konfigurasi Nginx dengan HTTP/2, HTTPS, dan proteksi header:

```nginx
server {
    listen 80;
    server_name hybrid20.domainmu.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name hybrid20.domainmu.com;

    root /var/www/hybrid20/public;
    index index.php index.html;

    # SSL Certs
    ssl_certificate /etc/letsencrypt/live/hybrid20.domainmu.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/hybrid20.domainmu.com/privkey.pem;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    add_header Permissions-Policy "geolocation=(), camera=(), microphone=()";

    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Blokir akses langsung ke file tersembunyi
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # PHP-FPM Handler
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Cache aset statis build (PWA & Vite)
    location ~* \.(css|js|woff2|woff|ttf|png|svg|webp|webmanifest)$ {
        expires 1y;
        add_header Cache-Control "public, max-age=31536000, immutable";
        access_log off;
    }
}
```

---

## 8. Penjadwalan Tugas (Cron Scheduler)

Tambahkan entri cron pada server agar scheduler Laravel berjalan:

```bash
crontab -e -u www-data
```

Tambahkan baris berikut:

```cron
* * * * * php /var/www/hybrid20/artisan schedule:run >> /dev/null 2>&1
```

---

## 9. Prosedur Pencadangan & Pemulihan (Backup & Disaster Recovery)

### A. Pencadangan Mandiri lewat Web UI
1. Buka menu **Pengaturan** (`/settings`).
2. Gulir ke bagian **Pencadangan & Pemulihan Data**.
3. Klik tombol **Ekspor Cadangan (ZIP)**.
4. Berkas arsip `.zip` berisi `manifest.json`, `data.json`, dan seluruh foto progres fisik akan terunduh.

### B. Pemulihan Mandiri (Restore)
1. Buka modal **Pulihkan Data Cadangan**.
2. Pilih berkas `.zip`.
3. Tinjau rincian manifest dan jumlah entitas.
4. Centang persetujuan: *"Saya mengerti bahwa data saat ini akan diganti."*
5. Klik **Mulai Pulihkan Data**. Seluruh database dan foto dipulihkan secara atomik (dengan remapping ID foreign key otomatis).

---

## 10. Mode Pemeliharaan (Maintenance Mode)

Jika perlu melakukan perawatan server tanpa mengganggu data:

```bash
# Mengaktifkan mode pemeliharaan (dengan bypass token rahasia)
php artisan down --secret="hybrid-admin-2026"

# Menonaktifkan mode pemeliharaan
php artisan up
```
