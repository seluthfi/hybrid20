# HYBRID 20: PLAN.md (v2.0)

> Sumber kebenaran untuk agen implementasi (Antigravity). Menggantikan PLAN.md v1. Diperbarui 31 Okt 2026, setelah desain Stitch batch 1 sampai 3 ditinjau dan semua keputusan dikunci.
> Aplikasi: Laravel single-user, web responsif + PWA, memantau program 20 minggu (cutting + gym + lari menuju 10K).

---

## 0. Cara memakai dokumen ini

1. **Prioritas jika ada konflik:** (1) §1 Aturan keras → (2) §4 Keputusan terkunci → (3) bagian lain PLAN.md ini → (4) file HTML referensi desain → (5) `docs/design/DESIGN.md`.
2. **File HTML di `docs/design/reference/` hanya referensi tata letak, gaya, dan komponen.** Semua teks, angka, nama fitur, dan data di dalamnya adalah placeholder buatan generator dan **bukan** sumber kebenaran. Jangan menyalin HTML mentah, jangan memakai Tailwind CDN, Google Fonts, atau Material Symbols dari CDN. Bangun ulang sebagai Blade component.
3. Kerjakan **per fase** (§13). Berhenti di akhir tiap fase, jalankan tes, ringkas perubahan, tunggu review.
4. Jangan mengarang file, route, model, atau hasil eksekusi. Baca repository sebelum mengubah.
5. Jika menemukan konflik atau ambiguitas, catat di `docs/OPEN_QUESTIONS.md` dan tanyakan. Jangan mengubah requirement sendiri.
6. Bahasa komunikasi dengan pemilik proyek: Indonesia, sederhana, jelaskan alasan keputusan penting secara ringkas (pemilik sedang belajar Laravel).

---

## 1. Aturan keras (tidak boleh dilanggar)

| # | Aturan |
|---|---|
| R1 | Semua target (jarak, kalori, berat, langkah) adalah **panduan fleksibel**. Tidak ada bahasa menghakimi atau gamifikasi: tanpa skor, streak, lencana, "100% selesai", "on-track", "tuntas", "kepatuhan". |
| R2 | **Tidak ada klaim medis atau keselamatan.** Tidak ada diagnosis, kata "aman", "siap", "kesiapan", "pulih", ikon perisai/verified di konteks kesehatan. Nyeri hanya memunculkan kartu info netral (§8.12). |
| R3 | **Tidak ada angka atau konten yang tidak berasal dari data pengguna atau seed** (§11). Dashboard tidak boleh mengarang nilai. |
| R4 | Berat: tampilkan tren, bukan satu timbangan. Tidak menjanjikan 60 kg dalam 20 minggu, tidak menyebut "laju penurunan target", tidak mengubah target kalori otomatis. Warna perubahan berat netral (bukan hijau/merah). |
| R5 | Langkah 10.000 adalah **total harian termasuk langkah saat berlari**. `workouts.steps` tidak pernah dijumlahkan ke `daily_activities.steps`. |
| R6 | Template program (`program_weeks`, `scheduled_workouts`, `scheduled_workout_exercises`) terpisah dari catatan aktual (`workouts`, `food_logs`, `body_measurements`). Histori tidak berubah saat template atau master makanan diedit (snapshot). |
| R7 | Semua data dibatasi ke pengguna login. Jangan pernah menerima `user_id` dari input. Gunakan relasi `$request->user()->...` dan Policy. |
| R8 | Foto progres privat: disimpan di private disk, diakses lewat route terotorisasi, tidak ada URL publik, tidak di-cache service worker. |
| R9 | PWA hanya cache **aset statis**. Jangan cache halaman privat, JSON, atau gambar foto. |
| R10 | Di luar MVP (jangan dibuat): AI, wearable/Apple Health/GPS, sosial, multi-user, pembayaran, native app, kalkulasi kalori terbakar, timer sesi live, push notification, offline write/sync, mode gelap, resep makanan. |
| R11 | **Tidak ada klaim privasi palsu.** Data disimpan di server pribadi milik pemilik. Dilarang menulis "on-device", "terenkripsi lokal", "tanpa cloud", "tersimpan di perangkatmu". |
| R12 | UI berbahasa Indonesia, sapaan **"kamu"** (bukan "Anda"), istilah **"Minggu"** (bukan "Pekan"). Nama latihan boleh Inggris (Push, Easy run, Long run). |
| R13 | Aksesibilitas: target sentuh ≥ 44 px; kontras teks ≥ 4,5:1; status selalu ikon + teks; fokus terlihat; zoom tidak diblokir (tanpa `user-scalable=no`); hormati `prefers-reduced-motion`; teks ≥ 12 px. |
| R14 | Sesi terlewat tidak dihapus. Status "Terlewat" memakai warna netral, bukan merah. |
| R15 | Tidak ada aset CDN runtime. Tailwind dikompilasi lewat Vite, font di-self-host, ikon SVG di-bundle. |
| R16 | Setiap aturan bisnis di §8 punya tes otomatis. |

---

## 2. Stack

| Lapisan | Pilihan | Catatan |
|---|---|---|
| Backend | Laravel, rilis stabil terbaru yang kompatibel dengan PHP lokal dan hosting | Catat versi PHP dan Laravel di `README.md` dan `composer.json`. Jangan mengunci versi tanpa memeriksa `php -v`. |
| Auth | Laravel Breeze (Blade) | **Registrasi publik dimatikan.** Akun dibuat lewat `php artisan hybrid:create-user`. Ganti kata sandi lewat artisan `hybrid:reset-password` dan halaman profil. |
| Frontend | Blade + Tailwind CSS (Vite) + Alpine.js | Tanpa SPA. |
| Grafik | Chart.js (npm, di-bundle) | |
| Font | `@fontsource` Plus Jakarta Sans (400,500,600,700) dan Space Grotesk (500,600,700), self-host | |
| Ikon | SVG di-bundle (mis. paket `@material-symbols/svg-400` per ikon atau `blade-icons`) lewat `<x-icon name="...">` | Tanpa font ikon dari CDN. |
| DB | SQLite (dev), MySQL/MariaDB atau PostgreSQL (production, sesuai hosting) | Hindari fungsi SQL khusus satu DB. |
| Tes | Pest atau PHPUnit (ikuti bawaan installer) | |
| Locale | `app.locale=id`, Carbon locale `id`, `app.timezone=UTC` | Zona waktu pengguna dari `user_settings.timezone` (default `Asia/Jakarta`). |
| Tailwind content | **Jangan** sertakan `docs/` | Supaya kelas dari HTML referensi tidak masuk build. |

---

## 3. Ruang lingkup

**MVP:** login/logout, Hari Ini, workout gym, running log, langkah harian, nutrisi + database makanan pribadi, pengukuran tubuh + foto privat, program 20 minggu (jadwal, status, ulangi minggu), grafik tren, pengaturan target, export/import backup, PWA + responsif.

**Bukan MVP:** lihat R10. Fitur lanjutan hanya setelah MVP dipakai nyata: offline write, impor CSV/GPX, Health Connect, reminder, API native, template latihan yang lebih nyaman.

---

## 4. Keputusan terkunci

| # | Keputusan |
|---|---|
| D1 | **Hari Ini** adalah pusat pencatatan harian (HP dan laptop). Menu lain untuk riwayat, pengelolaan, grafik. |
| D2 | Status sesi disimpan di `scheduled_workouts.status` (`planned, completed, partial, skipped, rescheduled`) + `rescheduled_to` (date, nullable). |
| D3 | Target gym (set × rep per latihan) di tabel baru `scheduled_workout_exercises`. |
| D4 | **Ulangi minggu:** salin minggu menjadi baris baru, geser sisa program satu minggu. Kolom `program_weeks.calendar_position` dan `is_repeat`. Unique = (`program_id`, `calendar_position`). Tanggal = `start_date + 7×(calendar_position−1)` hari. |
| D5 | `programs.start_date` harus hari **Senin** (validasi server dan klien). |
| D6 | `workouts.pain_flag` (boolean) untuk checkbox "Nyeri memburuk / mengubah cara berlari / berulang", di samping `pain_notes`. |
| D7 | Durasi disimpan sebagai `duration_seconds` (bukan menit). |
| D8 | Backup berupa **ZIP** (`manifest.json` + `data.json` + foto). |
| D9 | Form gym memakai **accordion** (satu latihan terbuka di HP, semua terbuka di laptop). |
| D10 | **RPE bilangan bulat 1–10** (per set dan per sesi). Tidak ada nilai default terpilih. *(Default yang dipilih agen; ubah hanya bila pemilik meminta.)* |
| D11 | **Tanpa timer live.** `performed_at` = jam mulai (default: saat form dibuka, bisa diubah). Durasi diisi manual `menit:detik`; form menyarankan durasi = waktu sejak form dibuka (bisa diedit). *(Default.)* |
| D12 | Format angka tampil `id-ID` (koma desimal, titik ribuan). Input menerima koma atau titik, disimpan ternormalisasi. Pakai `inputmode="decimal"/"numeric"`, bukan `type="number"`. *(Default.)* |
| D13 | **Tren berat:** dihitung bila ada ≥ 4 pengukuran berat dalam 7 hari terakhir. Selisih = rata-rata 7 hari terakhir dikurangi rata-rata 7 hari sebelumnya (butuh ≥ 4 data di masing-masing). Selain itu tampil "Belum cukup data (n dari 7 hari)". |
| D14 | Satu pengukuran tubuh per tanggal: unique (`user_id`, `measured_at`). Menyimpan ulang pada tanggal sama = memperbarui. |
| D15 | Unggah foto: JPG/PNG/WebP/HEIC bila didukung, maks 10 MB, diperkecil ke sisi terpanjang 1.600 px, dikonversi JPEG, **EXIF (termasuk GPS) dihapus**, thumbnail 400 px. |
| D16 | Ukuran porsi makanan dalam **gram** saja (`serving_size_g`). Minuman: tulis di nama porsi, mis. "1 gelas (250 ml)". |
| D17 | Tambah makanan: **halaman penuh di HP, modal di laptop**. |
| D18 | Satuan MVP: **kg dan km** saja. Tidak ada lbs, mil, kJ. Kolom `weight_unit` tetap ada (nilai `kg`). |
| D19 | Kolom tanggal lokal `workouts.performed_on` dan `food_logs.logged_on` (date), dihitung saat simpan dari zona waktu pengguna, agar pengelompokan harian konsisten di semua DB. |
| D20 | **Shell aplikasi:** HP = header (judul kiri; ikon Rencana dan Pengaturan kanan; tanpa logo bolt, avatar, lonceng) + tab bar melayang 5 slot (Hari Ini, Latihan, [+], Makan, Tubuh). Laptop = sidebar kiri (Hari Ini, Latihan, Makan, Tubuh, Rencana, Pengaturan) + nama pengguna + tombol Keluar. |
| D21 | Hanya tema terang. Mode gelap ditunda. |
| D22 | **Import = ganti seluruh data pengguna secara atomik** (satu transaksi, rollback jika gagal), dengan pratinjau dan konfirmasi eksplisit. ID di-remap saat import. |
| D23 | "Lewati" pada kartu sesi = `status=skipped` di `scheduled_workouts`, **tanpa** membuat record `workouts`. Menghapus workout yang tertaut mengembalikan sesi ke `planned` (bila tidak ada workout lain). |
| D24 | Hanya **set yang dicentang** yang disimpan di form gym. Latihan tanpa set tercentang tidak disimpan. Nilai awal set diisi dari sesi terakhir untuk latihan yang sama sebagai saran. |
| D25 | Hanya label fase dari data: `program_weeks.phase_label` (`deload` tampil "Minggu ringan", `taper` tampil "Taper"). Dilarang menampilkan "Fase Cutting/Anabolik/Fondasi/Adaptasi" dan sejenisnya. |

---

## 5. Sitemap, route, dan peta file referensi

File referensi disalin ke `docs/design/reference/mobile/` (18 file) dan `docs/design/reference/desktop/` (4 file). Panduan penyalinan ada di `ANTIGRAVITY_GUIDE.md`.

### 5.1 Peta layar

| Layar | Route (nama) | Referensi HP | Referensi laptop |
|---|---|---|---|
| Login | `/login` | `mobile/masuk_login.html` | tidak ada (kartu di tengah) |
| Hari Ini (aktif) | `/` (`today`) | `mobile/hari_ini_sesi_aktif.html` | `desktop/hari_ini_desktop.html` |
| Hari Ini (sesi selesai) | `/` | `mobile/hari_ini_sesi_selesai.html` | idem |
| Hari Ini (istirahat) | `/` | `mobile/hari_ini_istirahat.html` | idem |
| Menu catat cepat (+) | komponen sheet | `mobile/quick_action_bottom.html` | popover/modal dari tombol "Catat" di top bar |
| Form gym | `/workouts/create?scheduled=…` | `mobile/form_latihan_gym.html` | `desktop/form_latihan_gym_desktop.html` |
| Form lari | `/workouts/create?type=run…` | `mobile/form_sesi_lari_nyeri_alert.html` | modal/halaman tengah |
| Riwayat latihan | `/workouts` (`workouts.index`) | `mobile/latihan_riwayat_sesi.html` | daftar lebar (turunkan dari HP) |
| Detail latihan gym | `/workouts/{id}` | `mobile/detail_latihan_back_biceps.html` | turunkan |
| Detail latihan lari | `/workouts/{id}` | pola dari detail gym + form lari | turunkan |
| Jurnal makan | `/food-logs?date=` | `mobile/makan_jurnal_harian.html` | turunkan (dua kolom) |
| Tambah makanan | `/food-logs/create` | `mobile/tambah_makanan.html` | modal |
| Pustaka makanan | `/foods` | pola baris dari `tambah_makanan.html` | turunkan |
| Form makanan | `/foods/create`, `/foods/{id}/edit` | `mobile/form_makanan_baru.html` | modal/halaman |
| Tubuh: Berat & Lingkar | `/body` | `mobile/tubuh_berat_lingkar.html` | `desktop/tubuh_metrik_riwayat_desktop.html` |
| Tubuh: Foto | `/body/photos` | `mobile/tubuh_foto_progres_privat.html` | turunkan |
| Rencana (minggu) | `/plan/{calendar_position?}` | `mobile/rencana_20_minggu.html` | `desktop/rencana_kalender_desktop.html` |
| Sheet detail sesi rencana | komponen | `mobile/detail_sesi_lari_ditunda.html` | modal |
| Pengaturan & Data | `/settings` | `mobile/pengaturan_data.html` | turunkan |
| Papan komponen/state | `/_dev/components` (hanya `local`) | `mobile/papan_status_umpan_balik.html` | |
| Token desain | n/a | `docs/design/DESIGN.md` | |

### 5.2 Layar tanpa referensi (rancang dengan komponen yang sama)
Catat berat, Catat langkah, Latihan lepas, Detail lari, Pustaka makanan, Unggah foto, Viewer foto, Bandingkan foto, 404/500, Program belum dimulai, Login laptop.

### 5.3 Route (rancangan)
Semua di balik `auth`, kecuali login, `manifest.webmanifest`, `sw.js`, `offline.html`.

```
GET  /                         today
Resource workouts              index(?type=gym|run&week=) create store show edit update destroy
PATCH /scheduled-workouts/{id} scheduled-workouts.update   (status, rescheduled_to, notes)
POST  /scheduled-workouts/{id}/skip
PUT   /daily-activities/{date} daily-activities.upsert     (set TOTAL langkah, bukan tambah)
Resource foods                 index create store edit update destroy
GET  /foods/search?q=&filter=  (JSON; filter favorit|terakhir)
Resource food-logs             index(?date=) create store edit update destroy
GET  /body                     measurements.index
PUT  /body/{date}              measurements.upsert
Resource measurements          edit update destroy
Resource progress-photos       index create store destroy
GET  /progress-photos/{id}/image|thumb   (Policy; Cache-Control: private, no-store)
GET  /body/photos/compare?a=&b=
GET  /plan/{calendar_position?}          plan.show
PATCH /program-weeks/{id}                (notes)
POST  /program-weeks/{id}/repeat
DELETE /program-weeks/{id}/repeat        (batalkan ulangan bila belum ada catatan)
GET/PUT /settings                        settings.edit/update
PUT /settings/program                    (tanggal mulai Senin)
GET  /backup/export
POST /backup/preview                     (validasi file → token pratinjau)
POST /backup/import                      (konfirmasi + token)
GET  /charts/weight?metric=&range=       (JSON, ter-scope user)
GET  /charts/run-weekly                  (JSON, ter-scope user)
```

---

## 6. Data program (seed)

### 6.1 Lari: jarak target per sesi (km). Jangan ubah angka.

Hari lari: Selasa (2), Jumat (5), Minggu (7). Total adalah penjumlahan tiga sesi dan dipakai sebagai fixture tes.

| Mgg | Sel | Jum | Min | Total | phase_label |
|---:|---:|---:|---:|---:|---|
| 1 | 2.0 | 2.0 | 2.5 | 6.5 | |
| 2 | 2.5 | 2.0 | 3.0 | 7.5 | |
| 3 | 2.5 | 2.5 | 3.5 | 8.5 | |
| 4 | 2.0 | 2.0 | 3.0 | 7.0 | deload |
| 5 | 3.0 | 2.5 | 4.0 | 9.5 | |
| 6 | 3.0 | 3.0 | 4.5 | 10.5 | |
| 7 | 3.5 | 3.0 | 5.0 | 11.5 | |
| 8 | 2.5 | 2.5 | 4.0 | 9.0 | deload |
| 9 | 3.5 | 3.0 | 5.5 | 12.0 | |
| 10 | 4.0 | 3.0 | 6.0 | 13.0 | |
| 11 | 4.0 | 3.5 | 6.5 | 14.0 | |
| 12 | 3.0 | 3.0 | 5.0 | 11.0 | deload |
| 13 | 4.0 | 3.5 | 7.0 | 14.5 | |
| 14 | 4.0 | 4.0 | 7.5 | 15.5 | |
| 15 | 4.5 | 4.0 | 8.0 | 16.5 | |
| 16 | 3.5 | 3.0 | 6.0 | 12.5 | deload |
| 17 | 4.5 | 4.0 | 8.5 | 17.0 | |
| 18 | 5.0 | 4.0 | 9.0 | 18.0 | |
| 19 | 3.5 | 3.0 | 7.0 | 13.5 | deload |
| 20 | 3.0 | 2.0 | 10.0 | 15.0 | taper |

Label sesi (`intensity_label`): Selasa dan Jumat = `easy`; Minggu = `long_run`; Minggu 20 hari Minggu = `taper_or_test` (judul "Percobaan 10K"). `steady_optional` valid tetapi tidak di-seed. Minggu ringan = 4, 8, 12, 16, 19; Minggu 20 mengurangi volume sebelum percobaan 10K.

Teks `instructions` seed:
- `easy`: "Pace mudah, masih bisa berbicara. Jarak adalah target fleksibel."
- Jumat (lari): "Easy run atau sesi ringan sesuai kesiapan."
- `long_run`: "Long run dengan pace mudah. Boleh dikurangi atau dibagi bila belum pulih."
- `taper_or_test`: "Percobaan 10K hanya bila progres sebelumnya dapat ditoleransi, tubuh pulih, dan tidak ada nyeri yang mengubah cara berlari. Pace mudah; ini bukan tes kecepatan."
- Hari dengan gym + lari (Selasa, Jumat): tampilkan catatan "Beri jeda beberapa jam antara gym dan lari." Jumat gym: "Sesi gym ringan."
- Fartlek hanya opsional setelah fondasi nyaman; **tidak** di-seed, tidak ada tombol sprint.

### 6.2 Gym: template mingguan (sama untuk semua minggu)

| Hari | Judul sesi | Latihan (set×rep, atau set×detik) | Σ set |
|---|---|---|---:|
| Senin (1) | Push | DB bench press 3×10; DB fly 2×12; Overhead triceps extension 2×12; Triceps kickback 2×12; Shoulder press 3×10; Lateral raise 3×12 | 15 |
| Selasa (2) | Back / Biceps / Traps | Bent-over DB row 3×12; Rear delt fly 2×12; Biceps curl 2×12; Hammer curl 2×12; Shrug 2×15; Dead hang 2×**30 detik** | 13 |
| Rabu (3) | Legs / Abs | Goblet squat 2×10; Romanian deadlift 3×10; Glute bridge 2×12; Calf raise 3×15; Leg raise 2×12; Plank 3×**30 detik** | 15 |
| Kamis (4) | Upper | Incline bench press 3×10; Lat pulldown 3×12; Seated cable row 2×12; Lateral raise 2×12; Triceps extension 2×12; Biceps curl 2×12 | 14 |
| Jumat (5) | Back / Biceps / Traps (ringan) | DB row 2×12; Reverse snow angel 2×15; Biceps curl 2×12; Hammer curl 2×12; Wall angel 2×12; Shrug 2×15 | 12 |
| Sabtu (6) | Istirahat penuh (`rest`) | tanpa latihan | 0 |
| Minggu (7) | Long run | lihat 6.1 | |

Total per minggu: **5 sesi gym + 3 sesi lari = 8 sesi terjadwal**; 69 set gym. Hari istirahat tidak dihitung sebagai sesi. `sort_order`: gym = 1, lari = 2 pada hari yang sama.

`DefaultExerciseSeeder`: semua nama latihan di atas; `muscle_group` dari kolom "Fokus" Lampiran B PLAN v1 (Push, Triceps, Shoulder, Back, Rear delt, Biceps, Traps, Grip, Legs, Glutes, Calves, Abs, Upper, Mobility). Latihan berbasis waktu (Dead hang, Plank) memakai `target_duration_seconds`.

### 6.3 Nilai awal `user_settings` (dapat diedit)
Berat awal 74 kg; tinggi 163 cm; target berat jangka panjang 60 kg; langkah 10.000; kalori 2.200; protein 140 g; karbohidrat 260 g; lemak 65 g; timezone `Asia/Jakarta`; `weight_unit=kg`.

---

## 7. Database v2

Migration terpisah, foreign key, index. Tanda: **[BARU]**, **[UBAH]** terhadap PLAN v1.

- **users**: bawaan Laravel.
- **user_settings**: `user_id` (FK, unique), `height_cm`, `starting_weight_kg`, `target_weight_kg`, `daily_calorie_target`, `protein_target_g`, `carbs_target_g`, `fat_target_g`, `daily_steps_target` (default 10000), `timezone`, `weight_unit` (default `kg`), timestamps.
- **programs**: `user_id`, `name`, `start_date` (Senin), `duration_weeks` (default 20), `status` (`planned|active|completed|paused`), `notes`, timestamps. Satu program aktif per pengguna (ditegakkan di aplikasi).
- **program_weeks [UBAH]**: `program_id`, `week_number` (1–20, nomor template), **`calendar_position`** (urutan di kalender, ≥ 1), **`is_repeat`** (bool), `phase_label` (nullable: `deload|taper`), `notes`. **Unique (`program_id`, `calendar_position`)**; index (`program_id`, `week_number`).
- **scheduled_workouts [UBAH]**: `program_week_id`, `weekday` (1–7), `workout_type` (`gym|run|rest`), `title`, `planned_distance_km` dec(6,2), `planned_duration_minutes`, `intensity_label`, `sort_order`, `instructions`, **`status`** (default `planned`), **`rescheduled_to`** (date, nullable), timestamps.
- **scheduled_workout_exercises [BARU]**: `scheduled_workout_id` (cascade), `exercise_id` (nullable, nullOnDelete), `exercise_name`, `target_sets`, `target_reps` (nullable), `target_duration_seconds` (nullable), `sort_order`, `notes`, timestamps.
- **workouts [UBAH]**: `user_id`, `scheduled_workout_id` (nullable, nullOnDelete), `workout_type` (`gym|run|other`), `title`, `performed_at` (UTC datetime), **`performed_on`** (date lokal), **`duration_seconds`** (nullable, menggantikan `duration_minutes`), `distance_km` dec(6,2), `pace_seconds_per_km` (dihitung saat simpan), `steps` (informasi saja), `rpe` (1–10), `status` (`completed|partial|skipped`; `skipped` tidak dipakai UI di MVP), `body_condition` string(20) nullable (`segar|normal|lelah`, **[UBAH]** dari text), **`pain_flag`** bool default false, `pain_notes`, `general_notes`, timestamps. Index: (`user_id`, `performed_on`), `scheduled_workout_id`.
- **exercises**: `user_id` (nullable), `name`, `muscle_group`, `equipment`, `is_custom`, timestamps.
- **workout_exercises**: `workout_id` (cascade), `exercise_id` (nullable, nullOnDelete), `exercise_name` (snapshot), `sort_order`, `notes`.
- **exercise_sets**: `workout_exercise_id` (cascade), `set_number`, `reps`, `weight_kg` dec(7,2), `duration_seconds`, `distance_m`, `rpe` (1–10), `is_warmup` (tidak dipakai UI), timestamps.
- **daily_activities**: `user_id`, `activity_date`, `steps` (default 0), `source`, `notes`. Unique (`user_id`, `activity_date`).
- **foods**: `user_id`, `name`, `serving_name`, `serving_size_g`, `calories_kcal`, `protein_g`, `carbs_g`, `fat_g`, `is_favorite` (default false), `notes`.
- **food_logs [UBAH]**: `user_id`, `food_id` (nullable, nullOnDelete), `logged_at` (UTC), **`logged_on`** (date lokal), `meal_type` (`breakfast|lunch|dinner|snack`, wajib di UI), `food_name_snapshot`, **`serving_name_snapshot`**, **`serving_size_g_snapshot`**, `servings` dec(8,2) > 0, `calories_kcal`, `protein_g`, `carbs_g`, `fat_g` (snapshot × servings), `notes`. Index (`user_id`, `logged_on`).
- **body_measurements [UBAH]**: `user_id`, `measured_at` (date), `weight_kg`, `waist_cm`, `chest_cm`, `arm_cm`, `thigh_cm`, `notes`. **Unique (`user_id`, `measured_at`)**. Semua angka nullable; simpan hanya yang diisi.
- **progress_photos [UBAH]**: `user_id`, `taken_at` (date), `photo_path`, **`thumb_path`**, **`mime`**, **`size_bytes`**, `pose_label` (`front|side|back|other`), `notes`. File dihapus saat record dihapus.

Aturan FK: `cascadeOnDelete` hanya untuk child murni (sets, workout_exercises, scheduled_workout_exercises, weeks → workouts jadwal). Data historis memakai `nullOnDelete`. Validasi lewat Form Request. Casts untuk date/decimal/boolean. `$fillable` eksplisit.

---

## 8. Aturan bisnis dan kalkulasi

### 8.1 Tanggal program
- `week_start(pos) = start_date + 7×(pos−1)` hari; `session_date = week_start + (weekday−1)`.
- Posisi aktif = `floor((hari_ini_lokal − start_date)/7) + 1`.
- Sebelum `start_date`: tampilkan "Program dimulai N hari lagi". Setelah posisi terakhir: "Program selesai". Tanpa program: empty state "Pilih tanggal mulai (Senin)".
- Label: "Minggu {week_number} dari {duration_weeks}" + badge "Ulangan" bila `is_repeat`.
- Contoh uji: `start_date = 2026-11-02` (Senin). Selasa 3 Nov 2026 = Minggu 1, hari ke-2. Minggu 20 dimulai 15 Mar 2027.

### 8.2 Status sesi
`planned, completed, partial, skipped, rescheduled`. Menyimpan workout tertaut mengubah status sesi (`completed`/`partial`). "Lewati" = `skipped` (D23). Sesi `planned` yang tanggalnya lewat tetap berlabel "Direncanakan" dengan keterangan netral "belum dicatat"; **tidak** diubah otomatis. `rescheduled` menyimpan `rescheduled_to`; sesi tampil di tanggal tujuan dengan keterangan "dipindahkan dari {tanggal}", dan tetap dihitung di minggu aslinya. Tidak ada sesi yang dihapus.

### 8.3 Hitungan sesi mingguan
Total = jumlah `scheduled_workouts` bertipe `gym|run` pada minggu itu (default **8**). "Selesai" = `completed`. `partial` ditampilkan terpisah ("1 parsial"). Tampilan: "Minggu ini: 3 dari 8 sesi selesai".

### 8.4 Pace
`pace_seconds_per_km = round(duration_seconds / distance_km)`. Jangan dihitung bila jarak kosong/0 atau durasi kosong; tampil "-". Format `m'ss"/km` (mis. 5'48"/km). Input durasi `menit:detik`; menit boleh ≥ 60; detik 0–59.

### 8.5 Volume gym
`Σ weight_kg × reps` untuk set bukan warm-up dengan beban > 0 dan reps terisi. Set berbasis waktu dan berat badan dikecualikan. Label selalu "Volume (beban × rep)" dengan keterangan itu.

### 8.6 Langkah
Satu baris per hari. Menyimpan = **set total hari itu** (upsert). Target 10.000 = total termasuk lari.

### 8.7 Nutrisi
Snapshot makro = nilai master × `servings` saat dicatat. Total harian = Σ snapshot `food_logs` pada `logged_on`. Header tiap waktu makan = Σ itemnya (tidak boleh ada angka terpisah). Bila melebihi target: tampil netral "melebihi target {n} kcal", bar dibatasi 100% secara visual, tanpa warna merah. Takaran tampil "{servings} porsi · {servings × serving_size_g} g".

### 8.8 Tren berat
Lihat D13. Tampil: berat terakhir + "n hari lalu"; baris tren: "Tren 7 hari: turun/naik/stabil {x} kg" (netral) atau "Belum cukup data ({n} dari 7 hari)". Dilarang kata "aman", "sukses", "target tercapai". Grafik memakai rata-rata bergerak hanya pada titik yang jendelanya punya ≥ 4 data.

### 8.9 Zona waktu
Simpan `*_at` dalam UTC. `performed_on`/`logged_on` dihitung dari `user_settings.timezone` saat simpan dan dihitung ulang saat `*_at` diedit. Tes wajib: 23:30 WIB dan 00:30 WIB jatuh ke tanggal yang benar.

### 8.10 Ulangi minggu (D4)
Dalam satu transaksi: salin `program_weeks` + `scheduled_workouts` + `scheduled_workout_exercises` minggu terpilih menjadi baris baru (`is_repeat=true`, `calendar_position = pos+1`, status `planned`, `rescheduled_to=null`); geser semua `calendar_position` setelahnya +1 (proses dari terbesar ke terkecil karena unique). Catatan aktual tidak berubah. Dialog konfirmasi: "Sisa program bergeser 1 minggu. Catatan yang sudah ada tidak berubah." "Batalkan ulangan" hanya bila tidak ada workout tertaut dan semua status `planned`.

### 8.11 Format angka (D12)
Helper `App\Support\Num`. Parser menerima tipe field: `integer` (buang titik/koma ribuan; "2.420" → 2420) dan `decimal` (koma = desimal; bila tanpa koma, satu titik = desimal; "1.234,5" → 1234.5). Tampil: titik ribuan, koma desimal, 1 desimal untuk berat/jarak/lingkar.

### 8.12 Pengingat nyeri (konservatif)
Tampil kartu info netral (ikon `info`, bukan perisai) bila `pain_flag` dicentang di form, dan di Hari Ini serta Rencana bila ada workout dengan `pain_flag=true` dalam 14 hari terakhir (dengan tautan ke sesi itu; tidak bisa ditutup). Teks tetap: **"Pertimbangkan menghentikan progresi dan berkonsultasi dengan tenaga kesehatan."** Bila ≥ 2 sesi bertanda dalam 14 hari, awali dengan "Ada catatan nyeri yang berulang." Tanpa diagnosis, tanpa saran latihan, tanpa pernyataan aman/tidak aman, tanpa nama sendi karangan.

### 8.13 Backup (D8, D22)
- ZIP: `manifest.json` {`format:"hybrid20-backup"`, `version:1`, `exported_at`, `app_version`, `counts{tabel:n}`, `sha256{file}`}, `data.json` (semua tabel milik pengguna kecuali `users`/password), `photos/{id}.jpg`.
- Preview (`POST /backup/preview`): validasi tipe, ukuran, `format`, `version`, checksum, struktur; tampilkan nama file, tanggal ekspor, jumlah per tabel. Belum mengubah data.
- Import: wajib centang "Saya mengerti data saat ini akan diganti" (+ tautan "Unduh cadangan saat ini dulu"). Satu transaksi: hapus data pengguna, sisipkan ulang dengan remap ID lama→baru, kembalikan foto. Gagal = rollback penuh.
- Tes: export → import ke DB kosong → data identik (round-trip), file rusak/versi asing/checksum salah ditolak, tidak bisa meng-import ke akun lain.

---

## 9. Design system "Liquid Glass"

Sumber token: `docs/design/DESIGN.md` bagian YAML + palet pada HTML (nilai HTML dan YAML sama). Nilai heks di prosa DESIGN.md (#7A5AF8, #22CCB2, #EE7BA2) **diabaikan**. Daftarkan token di `tailwind.config.js`/CSS `@theme` dengan **nama semantik**.

### 9.1 Warna inti
primary `#613dde`, primary-container `#7a5af8`, primary-fixed `#e6deff`, secondary `#006b5c`, secondary-container `#61f7db`, secondary-fixed-dim `#40ddc2`, tertiary `#9d3b60`, tertiary-fixed `#ffd9e2`, surface `#fdf8ff`, surface-container-low `#f7f1ff`, surface-container `#f2ebff`, surface-container-high `#ece5fc`, surface-container-highest `#e6dff6`, on-surface `#1c1929`, on-surface-variant `#484555`, outline `#797586`, outline-variant `#cac4d7`, error `#ba1a1a`, error-container `#ffdad6`. (Daftar lengkap ada di blok YAML DESIGN.md.)

Pemetaan fungsi: ungu = gym/aksi utama; teal = lari; rose = nutrisi. Status **tidak** memakai merah kecuali error validasi dan tombol hapus.

### 9.2 Aturan kontras (wajib)
- Tombol teal dengan teks putih memakai `secondary` `#006b5c` (gradien ke `#00574b`). **Dilarang** teks putih di atas `#40ddc2`/`#61f7db`; di atasnya pakai `on-secondary-fixed` `#00201b`.
- Gradien tombol ungu `primary-container → primary` boleh bila ujung terang ≥ 4,5:1 dengan putih; periksa dengan alat kontras, jika kurang pakai `primary` solid.
- Teks ≥ 12 px (label tab, chip, badge). Hapus semua `text-[9px]`, `text-[10px]`, `text-[11px]`.

### 9.3 Tipografi
Space Grotesk untuk judul dan semua angka (beban, rep, kcal, pace, langkah) dengan `font-variant-numeric: tabular-nums`; Plus Jakarta Sans untuk isi dan label. Skala dari DESIGN.md (display 48/36, headline-lg 32/26, md 24, sm 20, body 18/15/13, label 14/12, metric 28).

### 9.4 Token bentuk dan spasi
`rounded-card` 24px, `rounded-field` 16px, `rounded-pill` 9999px, `rounded-sheet` 32px (sudut atas sheet). **Jangan** menimpa `rounded-lg/xl` bawaan Tailwind seperti di HTML (di sana `lg`=32px, `xl`=48px menyebabkan radius campur). Spasi: margin 16px, gutter 20px, desktop margin 48px / gutter 32px; konten desktop maks 1360 px.

### 9.5 Tingkat kaca (komponen CSS, bukan string utility berulang)
| Kelas | Latar | Blur | Pemakaian |
|---|---|---|---|
| `.glass-1` | putih 55% | 24px, saturate 160% | kartu umum |
| `.glass-2` | putih 72% | 28px, saturate 180% | tab bar, sheet, modal, header lengket |
| `.glass-3` | putih 90% | 20px | input, tabel, area data padat, baris set |

Semua: `border: 1px solid rgb(255 255 255 / .7)`, `box-shadow: inset 0 1px 0 rgb(255 255 255 / .8), 0 12px 32px -4px rgb(31 25 54 / .08)`. Maks **dua lapisan kaca bertumpuk**. `@supports not (backdrop-filter: blur(1px))` → latar solid `surface-container-lowest`. Blur mobile ≤ 28px. Latar halaman = `.app-bg` memakai `radial-gradient` statis (tiga warna lembut), **bukan** blob `fixed` berblur 90–140px. Hapus `animate-pulse`/`animate-ping` dekoratif; skeleton boleh berkilau dan mati saat `prefers-reduced-motion`.

### 9.6 Komponen Blade (`resources/views/components/`)
`app-shell` (mobile tab bar + header; desktop sidebar + top bar), `icon`, `button` (primary ungu, secondary teal, ghost, danger), `icon-button`, `field` (label, hint, error + `aria-describedby`), `input-number` (stepper ≥ 44 px, ketik langsung), `segmented`, `chip`, `card` (glass-1/2/3), `badge-status`, `progress-bar` (dibatasi, netral saat melebihi), `metric`, `list-row`, `set-row`, `sheet` (bottom sheet HP / modal laptop), `confirm-dialog`, `toast`, `banner` (info/error/offline), `empty-state`, `skeleton`, `chart-card`, `week-strip`, `info-card`, `rpe-picker` (grid 5×2, tiap tombol ≥ 44 px).

**Badge status:**
| Status | Teks | Ikon | Warna |
|---|---|---|---|
| planned | Direncanakan | `schedule` | netral (surface-container-high) |
| completed | Selesai | `check_circle` | secondary-container + on-secondary-container |
| partial | Parsial | `timelapse` | primary-fixed + on-primary-fixed-variant |
| skipped | Terlewat | `event_busy` | netral gelap (surface-container-highest), **bukan merah** |
| rescheduled | Ditunda | `update` | tertiary-fixed + on-tertiary-fixed-variant |

### 9.7 Navigasi (D20)
Tab bar melayang `max-w-[420px]`, tinggi 64–72 px, label ≥ 12 px, ikon terisi saat aktif, status aktif dari `routeIs`, `aria-current="page"`. Tombol [+] di tengah membuka sheet 4 aksi: Catat makanan, Catat berat, Catat langkah, Latihan lepas. Halaman anak (stack): tombol kembali 44 px + judul, tanpa avatar. Sidebar laptop: "HYBRID 20" (tanpa subjudul "Athletic Performance"), 6 menu, di bawah nama pengguna + **Keluar** (form POST). Top bar laptop: pill "Minggu N dari 20" + tombol "Catat" (membuka menu yang sama). Hapus lonceng, breadcrumb "Fase 1", chip target 10K.

### 9.8 Grafik (Chart.js)
- **Berat:** titik data asli + garis rata-rata 7 hari (D13), sumbu x berupa tanggal, sumbu y otomatis dengan padding, tooltip, pemilih metrik (berat, pinggang, dada, lengan, paha) dan rentang (4 minggu, 8 minggu, semua). Tidak ada garis target 60 kg di grafik. Ada `<table class="sr-only">` ringkasan dan `aria-label`. Tidak ada interpolasi titik yang tidak ada.
- **Jarak lari per minggu:** bar berkelompok target vs aktual per `calendar_position`; target = Σ `planned_distance_km`, aktual = Σ `distance_km` workout lari pada tanggal minggu itu; minggu ringan diberi pola/label teks "Minggu ringan"; minggu berjalan disorot; label "M1…M20". Angka mengikuti tabel §6.1 (bukan angka karangan).
- **Riwayat latihan terpilih** (Fase 6): beban tertinggi dan volume per sesi untuk satu latihan.
- Warna dari token; animasi mati pada `prefers-reduced-motion`; data dari endpoint JSON ter-scope user.

---

## 10. Spesifikasi layar dan koreksi terhadap referensi

Format: **Ambil** = apa yang dipertahankan dari referensi. **Wajib diubah** = penyimpangan yang harus diperbaiki. Semua teks contoh di referensi dianggap placeholder.

### 10.1 Login (`masuk_login.html`)
- Ambil: kartu kaca di tengah, field email/kata sandi, tombol Masuk, toggle tampil sandi.
- Wajib diubah: hapus header "Pengaturan" + panah kembali + avatar (login tanpa header); **kosongkan** nilai awal email dan sandi (hanya placeholder generik); hapus "Lupa kata sandi?" (pemulihan lewat artisan); hapus "Data tersimpan terenkripsi secara lokal di perangkatmu", "Versi 2.4.0", "Athlete tunggal"; hapus tautan registrasi; "Ingat sesi" tidak tercentang default; tampilkan error login sebagai pesan inline, bukan hanya warna; `h-13`, `w-13` tidak valid.

### 10.2 Hari Ini (`hari_ini_*`, `hari_ini_desktop.html`)
- Ambil: header tanggal + "Minggu N dari 20", kartu sesi (gym ungu, lari teal), tiga baris progres, kartu berat, ringkasan minggu, nav.
- Data: kartu sesi dari `scheduled_workouts` hari itu (+ yang `rescheduled_to` = hari ini), diurutkan `sort_order`. Gym: judul, jumlah latihan, jumlah set dari template. Lari: judul, "Target {x} km", instruksi seed. Tombol: "Mulai latihan", "Catat lari", "Lewati". Sesi selesai: ringkasan hasil (mis. "2,1 km · 5'48"/km") + badge Selesai + "Lihat detail", tanpa tombol aksi. Sabtu: satu kartu "Istirahat penuh" tanpa tombol. Progres: Kalori, Protein, Langkah vs target. Berat sesuai §8.8. Ringkasan minggu sesuai §8.3 (baris 7 hari dengan ikon status + `aria-label`).
- Wajib diubah: hapus streak "3 hari", "Defisit -350 kcal", "Zona 2", rentang pace, "~3,1 km ditempuh", "Beban Puncak 70 kg", "Target RPE", "Estimasi 55 mnt", Hidrasi/Tidur/Kesiapan 94%, "Mode Pemulihan", "Fase Anabolik/Cutting", "100% Selesai", "Target Harian Tuntas 2/2", "Semua Beres", "Target 2.0g/kg LBM", "Kondisi Kebugaran: Siap Latihan", "Fisioterapi"; target hari istirahat tetap 2.200 kcal dan 10.000 langkah (bukan 2.000/7.000); data minggu dari template (Rabu = Legs/Abs, Kamis = Upper, hari Minggu minggu 1 = 2,5 km); tanggal dari server, bukan "14 Mei 2024"; "Hari 2" dihitung (Senin = hari 1); angka dan label konsisten dengan data; selisih berat netral. Laptop: hapus kartu pengguna "Bima Satria · 68.4 kg · 14.8% BF", "Target 10K Sub-50m", "Pace 4:59", "Volume -30%", teks "Rerata pekan lalu" yang tidak dihitung; grafik bawah diganti grafik §9.8 dengan angka dari §6.1. Tampilkan kartu info nyeri (§8.12) bila berlaku.
- State: tanpa program, sebelum mulai, program selesai, loading skeleton, error muat.

### 10.3 Menu catat cepat (`quick_action_bottom.html`)
- Ambil: bottom sheet kaca dengan 4 aksi. Latar blur nyata di belakang sheet.
- Wajib diubah: hapus isi latar karangan ("Fase 2 Barbell Hypertrophy", "Overhead Press & Pendlay Row", "Defisit Target -420"), "Pembaruan terakhir 14:20", badge "HYBRID", "NEAT"; teks "Sinkronisasi langkah manual" → "Isi total langkah hari ini"; fokus terjebak di sheet, Esc menutup, tombol tutup 44 px.
- Sub-layar baru: **Catat berat** (tanggal default hari ini, satu field berat, "Terakhir: 73,8 kg", upsert ke `body_measurements`); **Catat langkah** (total hari ini, upsert, keterangan "Total termasuk langkah saat lari"); **Latihan lepas** (pilih Gym atau Lari, tanpa `scheduled_workout_id`); **Catat makanan** → §10.8.

### 10.4 Form gym (`form_latihan_gym.html`, `form_latihan_gym_desktop.html`)
- Ambil: header sesi, accordion latihan, baris set (Set, Beban, Rep, RPE, centang), "+ Set", "Salin set sebelumnya", catatan, segmented Selesai/Parsial, RPE sesi, catatan sesi, Simpan.
- Data: daftar latihan dari `scheduled_workout_exercises`; "Target 3×12 · Terakhir 10 kg × 12" (set pertama non-warm-up sesi terakhir untuk latihan sama; sembunyikan bila belum ada); latihan berbasis waktu memakai kolom **Detik**; D24 (hanya set tercentang disimpan).
- Tambahan wajib: field **jam mulai** dan **durasi** (D11); tombol **"+ Tambah latihan"** (cari/pilih dari `exercises` atau buat baru); RPE per set bisa diisi (angka 1–10, opsional); status Selesai/Parsial disarankan otomatis (semua set template tercentang → Selesai) dan bisa diubah; draf disimpan di `localStorage` (Alpine persist) dan dibersihkan saat simpan/logout; banner "Belum tersimpan, data masih di form" bila gagal; konfirmasi bila meninggalkan halaman berisi perubahan; catatan "Sisakan sekitar 2–3 repetisi sebelum gagal pada sebagian besar set." (teks v1).
- Wajib diubah: hapus timer live (`00:24:18`, tombol jeda), "Target Defisit Terjaga", "Hipertrofi Terukur", "Fokus kontraksi latissimus…", foto gerakan buatan AI dan kutipan teknik, "Tersinkronisasi lokal", "Data siap direkapitulasi…", "Est. Kalori Aktif", "Detak jantung ~128 bpm", "+8% vs minggu lalu", "18/18 set 100% target", "Estimasi RPE", "Intensitas Kuat", "Buang Sesi" (ganti "Batal" dengan konfirmasi); angka target harus dari template (mis. Selasa: Rear delt fly 2×12, Biceps/Hammer curl 2×12, Shrug 2×15, Dead hang 2×30 detik; bukan 3 set / 45 detik); beban "Dumbbell 6 kg / Target 8 kg" dan sejenisnya adalah placeholder, jangan di-seed; RPE bilangan bulat saja (`@7.5` dan chip 7,5/8,5 dihapus); `10.0` → "10" atau "10,5"; stepper **≥ 44 px**.
- Layout set (HP): baris atas = "Set n", input RPE (≥ 56 px lebar), centang 44 px; baris bawah = Beban `[−][nilai][+] kg` dan Rep `[−][nilai][+]` masing-masing penuh. Langkah: beban 0,5 kg; rep 1; detik 5. Laptop: satu baris grid seperti referensi, semua latihan terbuka dua kolom.
- Kesalahan referensi: `p- space-md` typo; satu `onclick` global ke `history.back()`; jangan `select-none` pada `<body>`.

### 10.5 Form lari (`form_sesi_lari_nyeri_alert.html`)
- Ambil: kartu info sesi, jarak (stepper), durasi, pace otomatis, langkah opsional, RPE, kondisi tubuh, catatan nyeri, checkbox nyeri, kartu info.
- Data/tambahan: tampilkan **target jarak sesi** ("Target 2,0 km"); field **tanggal & jam mulai**; **status Selesai/Parsial**; durasi `menit:detik` (menit boleh ≥ 60); "Langkah saat lari" dengan keterangan "Tidak ditambahkan ke total langkah harian"; tombol Simpan dan Batal.
- Wajib diubah: tidak ada nilai awal (jarak 2.1, durasi 12:11, langkah 2.420, RPE 5, kondisi "Sedikit lelah", teks nyeri, checkbox tercentang adalah **placeholder**: semua kosong); pace tampil "-" bila jarak atau durasi kosong dan **tidak** menyembunyikan seluruh komponen; hapus "Fase Aerobik", "Target 10K Base", "Zone Denyut Zona 2 (132–145)", "Karakter Regenerasi Aktif", "Easy Run 10K Foundation", "Kecepatan Rata-rata"; "Langkah Kadens" → "Langkah saat lari"; "mm:dd" → "menit:detik"; deskripsi RPE ("batas ambang anaerobik", "sprint penuh tenaga") dihapus; ikon `verified_user` → `info`; kartu info muncul **hanya** bila checkbox dicentang dan memakai teks §8.12; jarak maksimum wajar divalidasi (mis. ≤ 100 km); satu event pace tanpa `NaN`.

### 10.6 Riwayat latihan (`latihan_riwayat_sesi.html`)
- Ambil: filter segmented Semua/Gym/Lari, ringkasan minggu, kartu per sesi.
- Wajib diubah: filter **server-side** (`?type=`) dengan paginasi per minggu, bukan `display:none`; kartu adalah `<a>` ke detail; empty state "Belum ada catatan lari. Catat sesi pertamamu." **hanya** saat hasil filter kosong (bukan di tab Semua dan bukan "target 1 sesi lari"); contoh status Parsial dan Terlewat; "Pekan" → "Minggu"; "12m 11d" → "12:11"; judul sesi dari data ("Easy run", "Push"); hapus "Zona 2", "Fase Cut Aktif", "Easy Run 10K Foundation", "Chest/Shoulder/Triceps", "Disinkronisasi dengan Apple Health & GPS", ringkasan "Frekuensi 3/5"; ringkasan minggu: volume (dengan definisi §8.5), jarak lari, "X dari 8 sesi".

### 10.7 Detail latihan (`detail_latihan_back_biceps.html`)
- Ambil: header ringkasan, kartu per latihan dengan tabel set, catatan.
- Wajib diubah: header halaman "Detail Latihan" (bukan "Catat Aktivitas") tanpa tombol centang ambigu; tanggal dari data (bukan "3 Nov 2025"); tampilkan jam mulai + durasi (tanpa jam selesai); semua latihan tampil (referensi menyembunyikan Dead hang dan mengatakan "4 Gerakan" padahal 5); badge "Optimal/Sesuai/Dekat Limit" dihapus; foto stok, lokasi "Pusat Kebugaran Senopati", "Kalori Est.", "Refleksi Tubuh" ("Segar" hanya bila ada), kutipan teknik dihapus; RPE bulat; tampilkan **catatan nyeri** dan info §8.12 bila `pain_flag`; tombol **Ubah** dan **Hapus** (konfirmasi menyebut jumlah latihan/set nyata); detail lari: jarak, durasi, pace, RPE, kondisi, langkah, catatan.

### 10.8 Makan (`makan_jurnal_harian.html`, `tambah_makanan.html`, `form_makanan_baru.html`)
- **Jurnal:** Ambil: pengganti tanggal, kartu total, kelompok waktu makan (Sarapan, Makan siang, Makan malam, Camilan), "+ Tambah". Wajib: header kelompok = Σ item; makro P/K/L konsisten (bukan "C:" dan "K:" campur); hapus "Defisit -350", "Batas Aman 2.200", "Fokus Defisit Terjaga", "Minggu 1 · Hari 1" (hitung dari program); "Sisa 515 kcal" → "Sisa" netral atau "melebihi" (§8.7); baris item bisa diketuk untuk **ubah porsi / hapus** (konfirmasi); waktu makan kosong punya state "Belum ada catatan"; chevron tanggal 44 px; tombol "+ Tambah" 44 px; label tanggal "Hari ini" dari data.
- **Tambah makanan (D17):** halaman penuh di HP, modal di laptop (referensi memperlihatkan header "Rincian Nutrisi" di belakang sheet: dihapus). Pencarian server-side dengan debounce 250 ms; chip **Favorit** dan **Terakhir dipakai** sebagai filter (chip "Sumber Protein" dihapus); ketuk baris untuk memilih (tombol "+" per baris dihapus); porsi: stepper langkah 0,5 + ketik langsung, desimal; pilihan **waktu makan** dan **tanggal & jam** (jangan terkunci "Simpan ke Makan Siang"); pratinjau nutrisi hidup dari master × porsi; tombol Simpan lengket di bawah, tanpa scroll bersarang; empty state hasil pencarian + tautan "Buat makanan baru"; hapus "resep kustom", "defisit aktif", "Pencatatan nutrisi presisi".
- **Form makanan:** judul "Makanan baru"/"Ubah makanan"; Favorit **tidak** menyala default; rasio P/K/L menampilkan "-" saat kosong (bukan "P 35%…"); wajib hanya nama dan kalori; ukuran porsi gram saja (D16); `inputmode="decimal"`; error inline permanen dengan `aria-describedby` (bukan berkedip 1,2 dtk); tombol **Hapus** di mode ubah (`food_logs.food_id` jadi null, snapshot aman); catatan "Nilai gizi adalah perkiraan."; hapus "Tersimpan aman di sinkronisasi cloud akun Anda" (perisai), "Anda" → "kamu"; perbaiki kelas toggle (`w-13`, `after:h-5.5` tidak valid).

### 10.9 Tubuh (`tubuh_berat_lingkar.html`, `tubuh_foto_progres_privat.html`, `tubuh_metrik_riwayat_desktop.html`)
- **Berat & Lingkar:** Ambil: segmented, kartu berat + tren, kartu input, riwayat. Wajib: grafik Chart.js (§9.8) menggantikan SVG buatan tangan; kartu input **tidak punya nilai awal** (kosong, placeholder = nilai terakhir "Terakhir: 82 cm"), hanya field yang diisi yang disimpan, ada pilihan tanggal (default hari ini), stepper opsional, ketik langsung; hapus "Target: -0.4 kg/minggu", "Target Akhir Minggu 20: 68,0 kg", "penurunan stabil dalam batas target defisit", "tanpa kehilangan massa otot", "mengindikasikan penurunan lemak visceral", "Defisit −350/−400", "Terverifikasi", "Unduh Rekap", "Protokol…", "Base: 60.0 kg", "Fase Cutting 20 Minggu", "Tersimpan di Cloud!" (→ "Tersimpan"), "Minggu 8/20" dan "Sabtu 3 Nov" (hitung dari data); tombol ± 44 px; panah/warna selisih netral; riwayat memakai tabel Glass-3 di laptop dan kartu di HP; aksi Ubah/Hapus per baris; ukuran lengan/paha keterangan sisi ("kanan") hanya jika disimpan.
- **Foto:** Ambil: grid per tanggal dengan label pose, banner privasi, tombol tambah. Wajib: teks banner → "Foto privat. Hanya bisa dibuka setelah login dan tidak punya tautan publik." (hapus "Lokal Enkripsi", "tersimpan di perangkatmu tanpa diunggah"); **mulai dari empty state** (foto contoh buatan AI dari CDN dihapus); alur unggah (kamera/galeri, tanggal, pose Depan/Samping/Belakang/Lainnya, pratinjau, progres, error ukuran/tipe); grup per tanggal diberi "Minggu N" hanya bila `taken_at ≥ start_date`, selain itu "Sebelum program"; perbandingan memilih dua foto pose sama (bukan "Minggu 1 vs Baseline"), berat diambil dari pengukuran terdekat (±3 hari) bila ada, selisih ditulis netral; hapus "Definisi pinggang membaik", baseline "75,2 kg"; semua `<img>` punya `alt` bermakna; viewer layar penuh dengan hapus (konfirmasi); gambar dilayani lewat route terotorisasi.

### 10.10 Rencana (`rencana_20_minggu.html`, `rencana_kalender_desktop.html`, `detail_sesi_lari_ditunda.html`)
- Ambil: pemilih minggu 1–20 horizontal, kartu hari dengan target vs aktual, catatan minggu, tombol "Ulangi minggu ini", kalender bulan + detail minggu (laptop), sheet detail sesi dengan 5 status dan tanggal pengganti.
- Wajib diubah: seluruh data dari template dan catatan aktual: Rabu = Legs/Abs, Kamis = Upper, Jumat = Easy run + gym ringan, Sabtu = Istirahat penuh, Minggu = Long run {km dari §6.1}; kartu Selasa menampilkan gym dan lari terpisah; **8 sesi** (bukan 5, bukan "3 Gym · 2 Lari"); minggu ringan 4/8/12/16/19 berlabel "Minggu ringan" (bukan "Deload"); tanggal dihitung dari `start_date` (kalender tidak boleh menampilkan Minggu 2 Nov sebagai awal minggu; Senin = awal); hapus "Fase Adaptasi & Defisit", "Kemajuan Program Total 5%", "Target Pace 5'30"", "Lari Tempo", "Ambang Laktat", "Zona 2", "Barbell Back Squat 3×8 @ 75kg", "RPE Target 8.0", "Target Cut −400 kcal", "Mindset Fase 1", "Catatan Arahan…", "Proyeksi Beban 4 Minggu", "Ekspor Jadwal Pekan", "Kepatuhan Defisit 100% On-track", "Tonnage", "Target Tidur 8,5 jam", "Hidrasi 3,5 L", "Refeed", daftar minggu karangan ("Intensifikasi 1", "Hipertrofi Tahap 2"); "Catatan Pekan Ini" adalah **input yang bisa diedit** (`program_weeks.notes`), bukan kutipan; tombol "Ulangi minggu ini" membuka dialog konfirmasi §8.10; "Pekan" → "Minggu"; sesi terlewat/ditunda tetap terlihat; legenda warna kalender memakai ikon + teks, titik pada tanggal punya `aria-label`.
- Sheet detail sesi: judul + target dari data, instruksi dari seed, 5 status (ikon + teks), tanggal pengganti hanya muncul bila "Ditunda", catatan sesi, tombol **Simpan** (tanpa label "Tersimpan otomatis" yang menyesatkan), hapus "Fase Fondasi Aerobik", "Zona 2", "Est. Pace 6'15", "RPE 4–5", "Jaga intensitas… Jika denyut naik, perlambat", "tanpa pengurangan nilai disiplin", "Kamis, 5 Nov 2025" (tanggal dihitung; pemilih tanggal asli `<input type="date">` atau kalender mini), "Istirahat optimal • 2 hari lagi"; target sentuh chevron 44 px; "Anda" → "kamu".

### 10.11 Pengaturan & Data (`pengaturan_data.html`)
- Ambil: bagian bertitel, kartu glass, modal pratinjau impor.
- Wajib diubah: field **bisa diedit** (bukan tile statis): berat awal, tinggi, target berat jangka panjang (catatan "Target jangka panjang; tidak harus tercapai dalam 20 minggu."), langkah, kalori, protein, karbohidrat, lemak, tanggal mulai (validasi Senin di server + pesan "Tanggal mulai harus hari Senin."); hapus "Konfigurasi Mandiri / Lokal, privat, presisi tanpa langganan cloud / Aktif", "NEAT Harian" → "Target langkah harian", "Energi Harian" → "Target kalori", "Defisit moderat yang aman: −0,7 kg/pekan", "2,3 g/kg BB ideal", "Bahan bakar lari 10K", "Keseimbangan hormon", "Fase Cutting, Hipertrofi Terukur…", segmented lbs/mil/kJ (D18; tampilkan "Satuan: kg dan km" statis), "100% On-Device", "tersimpan sepenuhnya di memori peramban" (R11); "Ekspor JSON" → "Ekspor cadangan (ZIP)"; modal impor memakai data manifest nyata (nama file, tanggal ekspor, jumlah per tabel) dan alur §8.13 dengan checkbox persetujuan; tombol Keluar dan ganti kata sandi; "Mg 20" → "Minggu 20".

### 10.12 Papan status (`papan_status_umpan_balik.html`) → komponen
- Toast: "Sesi tersimpan." (tanpa "Hybrid Split • Push & 5K Pace", tanpa titik berdenyut). Error inline: field RPE bulat 1–10, pesan "RPE harus 1–10." (contoh 11,5 dihapus). Banner gagal simpan: "Belum tersimpan, data masih di form." + tombol "Coba lagi" (hapus "disimpan lokal", "cloud sync"). Offline: "Kamu offline. Menyimpan butuh koneksi." (hapus "IndexedDB", "diantrekan"). Kartu nyeri: hapus lencana "Fisioterapi AI" dan teks karangan "Sendi lutut kanan terasa kaku…" serta tombol "Mulai Warm-up", "Tandai Pulih"; ganti dengan `info-card` §8.12. Dialog hapus: judul "Hapus sesi ini?", isi memakai nama dan jumlah set nyata, "dari riwayat" (bukan "riwayat lokal perangkat"), tombol bahaya memakai `error`. Skeleton: tanpa animasi saat `prefers-reduced-motion`.
- Halaman `/_dev/components` hanya di environment `local`.

---

## 11. Kebijakan konten

**Dilarang tampil** (kecuali berasal dari data pengguna/seed): streak, skor, lencana, "100%", "tuntas", "on-track", "kepatuhan", defisit kalori, "kondisi kebugaran/kesiapan", hidrasi, tidur, denyut jantung, zona denyut, "Zona 2", target pace, estimasi kalori terbakar, "fase" apa pun selain §D25, target laju penurunan berat, target 10K waktu tertentu, "Sub-50", nama pengguna/berat/%BF contoh, lokasi gym, foto atau teknik gerakan, "Fisioterapi AI", "Apple Health", "GPS", "sinkronisasi cloud", "on-device", "terenkripsi", perisai/verified.

**Nada:** tenang, mendukung, tidak menghakimi. Slogan opsional satu kali saja (mis. di halaman Hari Ini): "Tenang, konsisten, selangkah demi selangkah." Jangan diulang di setiap layar.

---

## 12. Keamanan, privasi, PWA

- HTTPS di production; cookie sesi `secure`, `same_site=lax`; throttle login bawaan; CSRF aktif; password hashing bawaan; header keamanan dasar.
- Satu akun: registrasi publik dimatikan. Artisan: `hybrid:create-user {name} {email}` (meminta sandi interaktif, membuat `user_settings`, dan menawarkan membuat program dengan tanggal mulai Senin), `hybrid:reset-password {email}`.
- Kepemilikan: Policy untuk semua model berdata pribadi; tes lintas pengguna (akses = 404/403).
- Upload: validasi `finfo`, ukuran, re-encode (strip EXIF) lewat GD/Imagick, simpan di `storage/app/private/progress-photos/{user_id}/{uuid}.jpg`; tidak ada `storage:link` untuk folder ini.
- Git: jangan commit `.env`, `database/*.sqlite`, `storage/app/private`, backup.
- **PWA:** `manifest.webmanifest` (name "HYBRID 20", short_name, `start_url=/`, `scope=/`, `display=standalone`, theme_color `#613dde`, background_color `#fdf8ff`, ikon 192/512 + maskable, monogram sederhana "H20"); `sw.js` hanya precache aset build statis (`/build/assets/*`), font, ikon, `offline.html`; navigasi dan semua respons lain **network-only**; tidak ada cache halaman terotentikasi, JSON, atau gambar foto; banner offline lewat event `online/offline` (§10.12); strategi pembaruan versi SW terdokumentasi; uji installability dan pembaruan.
- Backup: §8.13.

---

## 13. Fase implementasi

Setiap fase: kerjakan di branch `phase/N-...`, tes lulus, commit, lalu minta review. Jangan menyentuh fase lain.

### Fase 0: Prasyarat dan repository
- Cek `php -v`, `composer -V`, `node -v`, `git --version`; catat versi di README.
- Pastikan `PLAN.md`, `AGENTS.md`, dan `docs/design/` (DESIGN.md + `reference/mobile` 18 file + `reference/desktop` 4 file) sudah ada; **jangan mengubah** isi `reference/`.
- `.gitignore` sesuai §12.
- **DoD:** repo bersih; semua file referensi terdaftar; versi tercatat.

### Fase 1: Setup Laravel, auth, shell, design system
- `laravel new`/composer create (versi stabil kompatibel), Breeze Blade, hapus route registrasi, artisan `hybrid:create-user` dan `hybrid:reset-password`, Vite + Tailwind (token §9, tanpa `docs/`), font self-host, `x-icon`, `app-shell` (HP + laptop), halaman login (§10.1), semua komponen §9.6, `/_dev/components` (hanya local), locale `id`.
- Tes: akses tanpa login redirect; registrasi tidak ada; login/logout; kontras token.
- **DoD:** login berfungsi, DB bersih bisa dibuat ulang (`migrate:fresh`), shell responsif HP dan laptop, tidak ada aset CDN (periksa Network).

### Fase 2: Migration, model, factory, seeder
- Semua tabel §7, model + relasi + casts, factory, `DatabaseSeeder`. Service `ProgramGenerator` membuat program dari `start_date`. `RunningPlanSeeder` (20 minggu × 3 sesi lari), `GymTemplateSeeder` (sesi gym + `scheduled_workout_exercises`, Sabtu `rest`), `DefaultExerciseSeeder`.
- Tes: total km per minggu = tabel §6.1; set per hari = §6.1/§6.2 (15, 13, 15, 14, 12); 8 sesi per minggu; `phase_label` tepat; label sesi; instruksi minggu 20.
- **DoD:** `migrate:fresh --seed` sukses; tes seed lulus; constraint unique bekerja.

### Fase 3: Program, Rencana, Pengaturan dasar
- Service tanggal (§8.1), validasi Senin, halaman Rencana HP + laptop (§10.10), sheet detail sesi + 5 status + tanggal pengganti, "Lewati", ulangi/batalkan ulangan (§8.10), catatan minggu, Pengaturan dasar (target + tanggal mulai, §10.11 tanpa backup).
- Tes: Minggu 1–20 → tanggal benar untuk `2026-11-02`; ISO weekday; sesi terlewat tidak hilang; ulangi minggu menggeser sisa dan tidak mengubah catatan; semua status; `rescheduled_to`; validasi bukan-Senin ditolak.
- **DoD:** program bisa dimulai pada tanggal pilihan dan jadwal tampil pada tanggal yang benar.

### Fase 4: Workout, langkah, Hari Ini
- Form gym + lari + latihan lepas, detail, riwayat server-side, hapus, catat langkah dan berat dari sheet, kartu info nyeri, Hari Ini (HP + laptop, tanpa grafik dulu), pembaruan status sesi dari workout (D23), draf localStorage.
- Tes: RPE 1–10; pace nol/kosong; durasi ≥ 60 menit; D24; pasangan Selasa gym+lari; langkah tidak ganda; satu baris langkah per hari; zona waktu; policy lintas pengguna; hapus workout → status kembali `planned`; format angka (§8.11).
- **DoD:** buat/ubah/lihat/hapus catatan milik sendiri; dua sesi di hari yang sama bekerja.

### Fase 5: Nutrisi dan Tubuh
- Pustaka makanan, tambah makanan (halaman penuh/modal), jurnal harian + total, snapshot; pengukuran tubuh (upsert per tanggal); foto privat (upload, thumbnail, EXIF dihapus, route terotorisasi, viewer, banding, hapus).
- Tes: total nutrisi × porsi; snapshot tidak berubah saat master diedit; hapus master → `food_id` null; header waktu makan = Σ item; timezone; unique pengukuran; foto tidak bisa diakses publik/pengguna lain; EXIF hilang; batas ukuran/tipe.
- **DoD:** histori tersimpan, total harian konsisten, foto privat terverifikasi.

### Fase 6: Dashboard dan grafik
- Endpoint JSON ter-scope, grafik berat/pinggang (§9.8), jarak lari mingguan, riwayat latihan terpilih, tren berat (D13), ringkasan minggu, melengkapi Hari Ini laptop.
- Tes: tren dengan data kurang → "Belum cukup data"; rata-rata 7 hari; grafik hanya data nyata; tidak ada nilai karangan saat DB kosong.
- **DoD:** dashboard hanya menampilkan data aktual; empty state jelas.

### Fase 7: PWA
- Manifest, ikon, `sw.js`, `offline.html`, banner offline, pengujian install/update, pengujian layar/browsers.
- Tes/pemeriksaan: halaman privat tidak masuk cache; foto tidak di-cache; update SW berjalan.
- **DoD:** bisa diakses dari laptop dan dipasang ke layar utama HP.

### Fase 8: Backup, pengerasan, deployment
- Export/Preview/Import (§8.13), audit aksesibilitas (target 44 px, kontras, fokus, `prefers-reduced-motion`, zoom), audit penghapusan konten terlarang (§11, grep), tes lengkap §14, `docs/DEPLOY.md` (PHP, `.env` production, `APP_DEBUG=false`, `optimize`, HTTPS, cookie secure, trusted proxy, backup DB dan foto berkala, restore diuji), deploy.
- **DoD:** dua perangkat memakai data yang sama; backup dapat dipulihkan di DB kosong; semua tes hijau.

---

## 14. Daftar tes (ringkasan)

Auth (tanpa login ditolak; registrasi tidak ada) · policy lintas pengguna untuk semua model · RPE 1–10 · validasi jarak/durasi/porsi/berat/ukuran · pace nol/kosong · volume (§8.5) · total nutrisi dan snapshot · timezone 23:30/00:30 · satu langkah per hari dan tidak ganda · seed lari (total tabel §6.1) · seed gym (set §6.2) · 8 sesi per minggu · tanggal program untuk `2026-11-02` · Senin wajib · status sesi dan `rescheduled_to` · ulangi minggu + batalkan · sesi terlewat tidak hilang · hapus workout → `planned` · tren berat · pengukuran unik per tanggal · foto privat dan EXIF · format angka · backup round-trip + file rusak · SW tidak cache halaman privat · `grep` tidak ada string terlarang §11 di `resources/`.

---

## 15. Risiko dan hal terbuka

| Isu | Penanganan |
|---|---|
| Versi PHP/Laravel dan hosting belum dipilih | Cek `php -v`; pilih hosting di Fase 8 (butuh HTTPS, storage privat, backup). |
| Tiga default belum dikonfirmasi pemilik (D10 RPE bulat, D11 tanpa timer, D12 format angka) | Dipakai sebagai default; ubah hanya bila diminta. |
| Layar tanpa referensi (§5.2) | Rancang dengan komponen yang ada, minta review sebelum lanjut. |
| Kinerja blur di HP lama | Aturan §9.5; ukur di perangkat nyata. |
| Offline write | Ditunda (R10). |

---

## Lampiran: definisi MVP satu kalimat

HYBRID 20 adalah aplikasi Laravel single-user berbasis web responsif/PWA yang menyimpan data fitness di satu database pusat, mencatat gym, lari, nutrisi, langkah, dan pengukuran tubuh, serta memantau template program 20 minggu dengan grafik, backup, dan kontrol privasi.
