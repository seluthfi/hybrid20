# AUDIT FUNGSIONAL FASE 1–6 (HYBRID 20)

Dokumen ini mencatat audit fungsional menyeluruh dari seluruh antarmuka dan alur pengguna aplikasi HYBRID 20 (Fase 1 sampai Fase 6) pada tampilan mobile (390 px) dan desktop (1440 px), sesuai mandat Fase 6C.

---

## 1. Tabel Temuan Audit dan Status Perbaikan

| Layar | Elemen | Harapan (Sesuai PLAN.md & AGENTS.md) | Kenyataan Sebelum Perbaikan | Status Perbaikan |
|---|---|---|---|---|
| **Metrik Tubuh** (`/body`) | Tombol "Catat Pengukuran" | Tombol membuka sheet modal pengukuran tanpa nesting tag | Ditemukan `<button @click><x-button>` (button di dalam button), klik tidak valid secara HTML | **Selesai (Diperbaiki)**: `@click` dipindahkan langsung ke `<x-button>`, nesting dibersihkan |
| **Foto Progres** (`/progress-photos`) | Tombol "Unggah Foto" (Header & Empty State) | Membuka modal unggah foto | Berbentuk `<button @click><x-button>` (dua kali di header dan empty state) | **Selesai (Diperbaiki)**: `@click` dipindahkan ke `<x-button>` |
| **Komponen Tombol** (`components/button.blade.php`) | Pemakaian tag `<a>` membungkus `<x-button>` | `<x-button href="...">` merender `<a>` langsung dengan style tombol tanpa nesting | Ditemukan 10+ file memakai `<a><x-button>...</x-button></a>` | **Selesai (Diperbaiki)**: Ditambahkan prop `href` pada `x-button`. Semua `<a><x-button>` diganti menjadi `<x-button href="...">` |
| **Komponen Ikon** (`components/icon.blade.php`) | 26 ikon Material Symbols (`hotel`, `scale`, `lock`, `event`, dll.) | Render SVG Material Symbols yang valid dan tajam | Tidak terdefinisi dalam array `$icons`, merender fallback dot/lingkaran | **Selesai (Diperbaiki)**: Seluruh 26 SVG diekstrak dari `@material-symbols/svg-400` dan didaftarkan dengan koordinat `viewBox="0 -960 960 960"`. Fallback melempar `InvalidArgumentException` di local/testing |
| **Metrik Tubuh** (`/body`) | Modal "Catat Pengukuran" | Nilai input kosong saat dibuka baru, dengan hint `Terakhir: {nilai}` | Input terisi nilai pengukuran lama atau 0 | **Selesai (Diperbaiki)**: Input direset kosong secara default dengan placeholder `Terakhir: {nilai}` |
| **Seluruh Form Aplikasi** | Input angka numerik/desimal | Menggunakan `<input type="text" inputmode="decimal|numeric">` diparse oleh `App\Support\Num` | Beberapa form memakai `<input type="number">` bawaan browser dengan spinner | **Selesai (Diperbaiki)**: Seluruh `type="number"` diganti ke `type="text"` dengan `inputmode` yang sesuai (0 instance `type="number"` tersisa) |
| **Modal & Bottom Sheet** | Validasi error pada form di modal | Modal otomatis terbuka kembali jika validasi gagal, form terisi `old()`, pesan error inline dengan `aria-describedby` | Modal tertutup saat error validasi, pesan error tidak terlihat tanpa membuka ulang | **Selesai (Diperbaiki)**: Seluruh modal (`app-shell`, `body`, `photos`, `plan`, `food-logs`) mendeteksi `$errors->any()` atau error bag, terisi `old()`, dan memiliki `@error` inline |
| **Seluruh Halaman** | Elemen dengan atribut `x-show` | Seluruh elemen wajib memiliki atribut `x-cloak` | Ditemukan beberapa elemen anak modal dan span toggle password tanpa `x-cloak` | **Selesai (Diperbaiki)**: Seluruh elemen ber-`x-show` dipasangkan `x-cloak` (0 pelanggaran) |
| **Komponen Week Strip** | Item tanggal | Memiliki handler interaksi yang jelas | Tombol `<button type="button">` tanpa handler aksi | **Selesai (Diperbaiki)**: Ditambahkan handler `@click="$dispatch('date-selected', ...)"` |
| **Pengaturan** (`/settings`) | Tombol Backup Database | Dinonaktifkan dengan label jelas | Belum memiliki label penjelasan status | **Selesai (Diperbaiki)**: Diberi atribut `disabled` dan catatan `Fitur backup lokal tersedia pada fase pemeliharaan` |

---

## 2. Verifikasi 7 Alur Kritis

### Alur 1: Autentikasi
- **Login**: Mengakses `/login`, memasukkan email dan kata sandi yang valid, diarahkan ke dasbor `/`. Menolak input kosong atau salah dengan pesan berbahasa Indonesia yang ramah. Toggle visibility kata sandi menggunakan `x-cloak` tanpa kedip icon.
- **Logout**: Form POST `/logout` dengan token CSRF memvalidasi sesi dan mengembalikan pengguna ke `/login`.
- **Ganti Kata Sandi**: PUT `/password` di halaman pengaturan memvalidasi kata sandi saat ini dan kecocokan kata sandi baru (minimal 8 karakter).

### Alur 2: Dasbor Hari Ini (`/`)
- **Tampilan Sesi Terjadwal**: Menampilkan kartu latihan hari ini (Gym/Lari) atau status istirahat.
- **Mulai Latihan**: Tombol aksi mengarahkan langsung ke form pencatatan sesi dengan parameter jadwal terkait.
- **Kartu Info Nyeri**: Menampilkan peringatan netral dengan teks baku jika sesi sebelumnya memiliki bendera nyeri (R3, §8.12).
- **Quick Action [+] Floating Button**: Membuka modal dengan 4 pilihan cepat:
  1. Catat Makanan -> diarahkan ke pencarian makanan.
  2. Catat Berat Badan -> membuka modal berat badan cepat (reopen saat error, `inputmode="decimal"`).
  3. Catat Langkah -> membuka modal input total langkah (reopen saat error, `inputmode="numeric"`).
  4. Latihan Lepas -> memilih latihan gym/lari tanpa jadwal 20 minggu.

### Alur 3: Form Latihan Gym & Lari (`/workouts`)
- **Pencatatan Sesi Gym**: Tambah latihan, input beban (desimal), repetisi/durasi detik (integer), RPE (1–10). Fitur salin set (copy set) berfungsi presisi.
- **Pencatatan Sesi Lari**: Input jarak (km desimal), durasi (menit/detik), auto-calculate pace (menit:detik / km), RPE, langkah kaki akumulasi.
- **Pemeriksaan Nyeri**: Checkbox nyeri netral tanpa diagnosis medis.
- **CRUD & Filter Riwayat**: Menampilkan riwayat latihan dengan filter tipe (Semua, Gym, Lari) dan status.

### Alur 4: Jurnal Makan & Perpustakaan Makanan (`/food-logs`, `/foods`)
- **Navigasi Tanggal**: Pindah tanggal (hari ini, kemarin, besok) memperbarui ringkasan total kalori dan makronutrisi (protein, karbohidrat, lemak).
- **Porsi Desimal**: Mendukung koma atau titik (misal `1,5` atau `0.75`), snapshot nutrisi dihitung proporsional dari master makanan.
- **CRUD Perpustakaan Makanan**: Tambah makanan baru, pencarian instan JSON via `/foods/search`, penanda favorit (bintang), edit, dan hapus.

### Alur 5: Metrik Tubuh & Foto Privat (`/body`, `/progress-photos`)
- **Pencatatan Pengukuran**: Modal "Catat Pengukuran" dibuka dengan input kosong dan placeholder `Terakhir: {nilai}`. Menyimpan berat, lingkar pinggang, dada, lengan, paha.
- **Grafik Metrik**: Chart berat badan dengan garis rata-rata bergerak 7 hari, lingkar tubuh, jarak lari mingguan target vs aktual, serta riwayat beban/volume latihan. Empty state tampil bila data kosong tanpa angka rekaan.
- **Foto Progres Privat**:
  * Upload foto format JPEG, PNG, WebP (maksimal 10 MB).
  * Pemilihan pose: Depan (`front`), Samping (`side`), Belakang (`back`), Lainnya (`other`).
  * File disimpan pada disk privat (`storage/app/private`), tidak dapat diakses secara publik.
  * Route gambar `/progress-photos/{id}/image` dan `/progress-photos/{id}/thumb` diverifikasi oleh middleware `auth` dan otorisasi kepemilikan pengguna.
  * Metadata EXIF GPS dilucuti saat proses kompresi/re-encoding dengan GD.
  * Layar komparasi foto (`/body/photos/compare`) memungkinkan perbandingan sebelum dan sesudah.

### Alur 6: Rencana Program 20 Minggu (`/plan`)
- **Tampilan Kalender & Minggu**: Navigasi Minggu 1 sampai 20 dengan status sesi lengkap (Direncanakan, Selesai, Parsial, Terlewat, Ditunda).
- **Pengubahan Status & Penundaan**: Bottom sheet detail sesi memungkinkan pemindahan tanggal sesi yang ditunda (`rescheduled_to`), lewati sesi, dan catatan minggu.
- **Pengulangan Minggu**: Tombol "Ulangi Minggu Ini" dan "Batalkan Pengulangan" dengan dialog konfirmasi yang aman.
- **Posisi Scroll**: Preservasi posisi scroll saat menyimpan catatan atau status.

### Alur 7: Pengaturan Akun & Target (`/settings`)
- **Target Nutrisi & Fisik**: Input tinggi badan, berat awal, target berat, target langkah harian, target kalori, target protein, karbohidrat, dan lemak. Diparse oleh `Num` (mendukung koma/titik desimal dan pemisah ribuan).
- **Validasi Mulai Program**: Tanggal mulai program divalidasi harus hari Senin.
- **Tombol Cadangkan Data**: Ditampilkan dengan status `disabled` dan penjelasan yang jelas bahwa fitur backup offline/lokal tersedia di fase pemeliharaan mendatang.

---

## 3. Hasil Pengujian Otomatis

- `tests/Feature/BladeIntegrityAndIconsTest.php`: **5/5 LULUS**
  1. `test_no_nested_interactive_elements_in_blade_views`: 0 elemen `<button>` atau `<a>` bersarang di seluruh template Blade.
  2. `test_all_icon_names_in_blade_views_are_defined`: 100% nama ikon di Blade terdaftar di `components/icon.blade.php`.
  3. `test_all_x_show_elements_have_x_cloak`: 100% tag berelemen `x-show` memiliki `x-cloak`.
  4. `test_no_type_number_inputs_in_views`: 0 elemen `<input type="number">` di seluruh aplikasi.
  5. `test_all_button_type_button_have_handlers`: Seluruh tombol memiliki event listener atau terproteksi.
- `tests/Feature/FunctionalAuditPhase1To6Test.php`: **7/7 LULUS**
  1. `test_get_routes_return_200_on_empty_database`: Status 200 pada seluruh rute saat DB kosong.
  2. `test_get_routes_return_200_on_populated_database`: Status 200 pada seluruh rute saat DB terisi data.
  3. `test_food_crud_and_validation`: CRUD makanan dan validasi error berhasil.
  4. `test_food_log_crud_and_validation`: Pencatatan log makan porsi desimal dan validasi error berhasil.
  5. `test_measurements_and_steps_upsert`: Upsert berat badan dan langkah harian berhasil.
  6. `test_cross_user_access_is_forbidden`: Akses silang antar-pengguna ditolak dengan 403/404.
  7. `test_photo_upload_stores_in_private_disk_and_strips_exif`: Upload foto tersimpan di disk privat dengan EXIF GPS dilucuti.
- **Total Test Suite Keseluruhan**: **121 Tests, 896 Assertions, 100% Green**.
- **Asset Compilation (Vite)**: **Lulus bersih (1.63s)** tanpa peringatan atau kegagalan.
