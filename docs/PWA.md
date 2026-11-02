# DOKUMENTASI PWA: HYBRID 20

Aplikasi HYBRID 20 dilengkapi dengan kapabilitas Progressive Web App (PWA) yang memungkinkan pengguna memasang (install) aplikasi ke layar utama ponsel (Android / iOS) maupun desktop (Windows / macOS) layaknya aplikasi mandiri (standalone app), tanpa overhead app store.

Sesuai aturan keras privasi (**PLAN.md §1 R8, R9, R10, R11**), Service Worker pada HYBRID 20 **hanya meng-cache aset statis**. Seluruh data pribadi, catatan latihan, jurnal makan, foto progres, dan endpoint grafik bersifat **Network-Only** dan tidak pernah disimpan ke cache browser.

---

## 1. Komponen PWA

### A. Web App Manifest (`public/manifest.webmanifest`)
- **Nama Aplikasi**: `HYBRID 20` (short name: `HYBRID 20`)
- **Warna Tema (`theme_color`)**: `#613dde` (warna primer brand)
- **Warna Latar (`background_color`)**: `#fdf8ff` (latar belakang aplikasi)
- **Mode Tampilan**: `standalone` (tanpa address bar browser saat dibuka dari layar utama)
- **Orientasi**: `portrait`
- **Ikon**:
  - `public/icons/icon-192.png` (192 × 192 px, purpose: `any`)
  - `public/icons/icon-512.png` (512 × 512 px, purpose: `any`)
  - `public/icons/icon-192-maskable.png` (192 × 192 px, safe area 20%, purpose: `maskable`)
  - `public/icons/icon-512-maskable.png` (512 × 512 px, safe area 20%, purpose: `maskable`)
  - `public/icons/icon.svg` (vektor resolusi bebas)

### B. Service Worker (`public/sw.js`)
Service Worker bertugas mengatur siklus cache dan offline fallback secara ketat:
1. **Precache Aset Statis**:
   - `/offline.html`
   - `/manifest.webmanifest`
   - Font variabel woff2 (`/fonts/plus-jakarta-sans-latin-variable.woff2`, `/fonts/space-grotesk-latin-variable.woff2`)
   - Ikon PWA (`/icons/*`)
2. **Cache Aset Kompilasi Vite (`/build/assets/*`)**:
   - Cache-first karena file build Vite menggunakan hash konten unik yang immutable.
3. **Network-Only untuk Navigasi & Data Privat (R8, R9)**:
   - Permintaan navigasi (`mode === 'navigate'`) selalu mengambil halaman terbaru dari jaringan.
   - Bila koneksi terputus (offline), Service Worker mengembalikan `/offline.html`. Halaman HTML hasil render **tidak pernah di-cache** untuk mencegah kebocoran data sesi.
   - Endpoint privat (`/progress-photos/*`, `/charts/*`, `/foods/*`, `/body/*`, `/workouts/*`, `/settings/*`, `/daily-activities/*`) diproteksi penuh dan tidak pernah disimpan ke cache.

### C. Halaman Offline (`public/offline.html`)
Halaman mandiri tanpa ketergantungan jaringan eksternal. Memuat pesan yang tenang dan ramah:
- *"Kamu Sedang Offline"*
- *"Menyimpan atau melihat data kebugaran membutuhkan koneksi internet aktif. Data kamu aman di server."*
- Tombol *"Coba Lagi"* (`window.location.reload()`).

### D. Banner Offline di Antarmuka
Terintegrasi langsung di `resources/views/components/app-shell.blade.php`:
- Mendeteksi event `@online` dan `@offline` peramban.
- Bila offline, banner atas muncul: *"Kamu offline. Menyimpan butuh koneksi."* lengkap dengan tombol coba lagi.

---

## 2. Cara Menguji Pemasangan (Install)

### Di Laptop / Komputer (Google Chrome / Microsoft Edge)
1. Jalankan aplikasi di localhost (misal: `http://localhost:8000` atau via virtual host Laragon `http://hybrid20.test`).
2. Buka DevTools (`F12`), masuk ke tab **Application** > **Manifest**:
   - Pastikan seluruh properti (Name, Icons, Theme color) terdeteksi tanpa error.
3. Klik tombol **Install application** pada bilah alamat URL browser (ikon komputer kecil di sebelah kanan address bar) atau via menu browser > *"Install HYBRID 20"*.
4. Aplikasi akan terbuka di jendela terpisah tanpa frame peramban standar (standalone mode).

### Di Ponsel Android (Google Chrome)
1. Akses URL aplikasi dari ponsel (atau remote debugging via USB / tunneling HTTPS).
2. Tekan menu tiga titik di pojok kanan atas Chrome.
3. Pilih menu **"Tambahkan ke Layar Utama"** atau **"Pasang Aplikasi"** (*Install app*).
4. Ikon "H20" akan muncul di app drawer dan layar utama ponsel.

### Di iPhone / iPad (Safari)
1. Akses aplikasi via Safari.
2. Tekan tombol **Share** (kotak dengan panah ke atas) di bilah bawah.
3. Pilih **"Add to Home Screen"** (*Tambah ke Layar Utama*).
4. Tekan **Add**. Aplikasi dapat dibuka langsung dalam mode layar penuh (apple-mobile-web-app-capable).

---

## 3. Cara Menguji Pembaruan (Update Lifecycle)

Pembaruan aset atau Service Worker dikelola dengan skema cache versioning:
1. Buka `public/sw.js`, ubah versi cache:
   ```javascript
   const CACHE_NAME = 'hybrid20-static-v1.0.1';
   ```
2. Muat ulang halaman di browser.
3. Buka DevTools > **Application** > **Service Workers**:
   - Service Worker baru akan diunduh dan masuk fase `installing` -> `waiting to activate`.
   - Melalui `skipWaiting()` dan `clients.claim()`, service worker baru langsung mengambil alih kendali.
   - Cache lama (`hybrid20-static-v1.0.0`) secara otomatis dihapus dari memori penyimpanan cache peramban.

---

## 4. Cara Menguji Mode Offline

1. Buka DevTools (`F12`) > masuk ke tab **Network**.
2. Centang checkbox **Offline** (atau pilih profil koneksi *Offline*).
3. **Uji Banner**: Perhatikan banner kuning/oranye muncul di bagian atas halaman dengan teks: *"Kamu offline. Menyimpan butuh koneksi."*.
4. **Uji Halaman Fallback**: Coba muat ulang halaman atau klik tautan ke rute lain saat koneksi offline.
   - Browser akan menyajikan halaman `offline.html` yang bersih dan informatif.
5. Hapus centang **Offline** pada DevTools, lalu klik tombol *"Coba Lagi"*. Halaman aplikasi akan kembali termuat secara normal.
