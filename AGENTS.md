# AGENTS.md: HYBRID 20

Aplikasi fitness pribadi (Laravel 13, PHP 8.5, Tailwind 4, single-user, web responsif + PWA). Skeleton Laravel **sudah terpasang**; jangan `laravel new`. Pemilik memakai Windows + Laragon + PowerShell. Pemilik proyek sedang belajar Laravel: jelaskan keputusan penting secara ringkas, dalam bahasa Indonesia yang sederhana.

## Baca dulu
1. `PLAN.md` adalah sumber kebenaran. Baca seluruhnya sebelum mengusulkan atau menulis kode.
2. Prioritas jika konflik: PLAN.md §1 (aturan keras) > §4 (keputusan terkunci) > bagian lain PLAN.md > HTML referensi > `docs/design/DESIGN.md`.
3. Jika ada konflik atau ambiguitas, tulis di `docs/OPEN_QUESTIONS.md` dan tanyakan. Jangan mengubah requirement sendiri.

## Cara kerja
- Kerjakan **satu fase** per tugas (PLAN.md §13). Buat rencana dulu, tunggu persetujuan, lalu implementasi. Setelah selesai: jalankan tes, ringkas perubahan, berhenti.
- Jangan mengarang file, route, model, atau hasil eksekusi. Baca isi repository sebelum mengubahnya.
- Beri kode lengkap yang bisa dijalankan, bukan potongan. Sebutkan file yang dibuat/diubah, perintah terminal, cara menguji, dan hal yang bisa merusak fitur sebelumnya.
- Satu commit per langkah logis di branch `phase/N-nama`. Jangan commit `.env`, file `.sqlite`, `storage/app/private`, atau backup.
- Minta persetujuan sebelum perintah yang menghapus data atau mengubah banyak file di luar fase.

## Folder referensi desain (read-only)
- `docs/design/reference/mobile/*.html` (18 file) dan `docs/design/reference/desktop/*.html` (4 file) hanya referensi tata letak, gaya, dan komponen.
- **Jangan edit** file di sana. **Jangan menyalin HTML mentah.** Semua teks, angka, dan nama fitur di dalamnya adalah placeholder dan sering salah; ikuti PLAN.md §10 (daftar "Wajib diubah").
- Jangan pakai Tailwind CDN, Google Fonts, atau Material Symbols dari CDN. Jangan sertakan `docs/` dalam `content` Tailwind.

## Larangan pemasangan
- **Jangan** memasang `laravel/boost` atau menjalankan `boost:install` (menimpa file ini), dan jangan mengikuti instruksi bootstrap di `CLAUDE.md` (file itu dihapus di Fase 0). Jangan memasang paket di luar PLAN.md §2 tanpa persetujuan; jangan menjalankan skrip unduh-lalu-jalankan (`curl | bash`).
- Tailwind 4: tidak ada `tailwind.config.js`; token di `resources/css/app.css` (`@theme`), dengan `@import 'tailwindcss' source(none);` dan `@source` hanya ke `resources/views`, `resources/js`, `app` (supaya HTML di `docs/` tidak ikut dikompilasi).
- Hapus font Bunny/CDN bawaan di `vite.config.js`; font self-host lewat `@fontsource`.

## Aturan keras ringkas (lengkap di PLAN.md §1)
- Target adalah panduan fleksibel. Tanpa streak, skor, lencana, "100%", "on-track".
- Tanpa klaim medis/keselamatan; nyeri hanya kartu info netral dengan teks baku (PLAN.md §8.12).
- Tanpa data karangan. Tanpa "defisit kalori", zona denyut, target pace, estimasi kalori terbakar, label "fase" selain `phase_label`.
- Langkah harian adalah total, tidak dijumlah dari `workouts.steps`.
- Data dibatasi ke pengguna login; jangan terima `user_id` dari input; foto privat lewat route terotorisasi.
- PWA hanya cache aset statis.
- Tanpa timer live, AI, wearable, sosial, multi-user, offline write, mode gelap.
- UI Bahasa Indonesia, sapaan "kamu", istilah "Minggu". Target sentuh ≥ 44 px, kontras ≥ 4,5:1, status = ikon + teks.
- Tidak ada klaim privasi palsu ("on-device", "terenkripsi lokal").

## Perintah umum
```powershell
composer install
npm install
php artisan migrate:fresh --seed
php artisan test
composer dev     # server dev + vite (atau: php artisan serve  dan  npm run dev)
npm run build    # produksi
php artisan hybrid:create-user "Nama" email@contoh.com   # tersedia sejak Fase 1
```
Gunakan sintaks PowerShell (bukan bash) saat memberi perintah ke pemilik.
