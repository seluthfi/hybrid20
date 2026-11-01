<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Preview Komponen — HYBRID 20 Dev</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-bg font-sans text-on-surface min-h-screen p-4 sm:p-12 antialiased selection:bg-primary-fixed">

    <div class="max-w-4xl mx-auto flex flex-col gap-10">
        <!-- Header -->
        <div class="flex flex-col gap-2 border-b border-outline-variant/40 pb-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-pill bg-primary-fixed text-primary w-fit">
                <x-icon name="tune" class="w-4 h-4" />
                <span class="font-headline text-xs font-semibold uppercase tracking-wider">Design System Preview</span>
            </div>
            <h1 class="font-headline text-3xl font-bold tracking-tight text-on-surface">
                Komponen Liquid Glass Athletic
            </h1>
            <p class="font-sans text-sm text-on-surface-variant">
                Koleksi 24 komponen Blade, token warna, status, dan interaksi sesuai PLAN.md §9 & §10.12.
            </p>
        </div>

        <!-- 1. Tombol & Target Sentuh -->
        <section class="flex flex-col gap-4">
            <h2 class="font-headline text-lg font-bold text-on-surface">1. Tombol (Min. 44px)</h2>
            <x-card tier="1" class="flex flex-wrap items-center gap-3">
                <x-button variant="primary" size="md">Primary Violet</x-button>
                <x-button variant="secondary" size="md">Secondary Teal</x-button>
                <x-button variant="ghost" size="md">Ghost</x-button>
                <x-button variant="danger" size="md">Danger</x-button>
                <x-button variant="outline" size="md">Outline</x-button>
                <x-icon-button name="settings" ariaLabel="Pengaturan" variant="glass" />
                <x-icon-button name="add" ariaLabel="Tambah" variant="primary" />
            </x-card>
        </section>

        <!-- 2. Status Badges -->
        <section class="flex flex-col gap-4">
            <h2 class="font-headline text-lg font-bold text-on-surface">2. Badge Status Sesi (Teks + Ikon, Netral untuk Terlewat)</h2>
            <x-card tier="2" class="flex flex-wrap items-center gap-3">
                <x-badge-status status="planned" />
                <x-badge-status status="completed" />
                <x-badge-status status="partial" />
                <x-badge-status status="skipped" />
                <x-badge-status status="rescheduled" />
            </x-card>
        </section>

        <!-- 3. Form Field & Input Number -->
        <section class="flex flex-col gap-4">
            <h2 class="font-headline text-lg font-bold text-on-surface">3. Form Fields & Numeric Stepper</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-card tier="1">
                    <x-field label="Target Jarak (km)" hint="Boleh dikurangi bila belum pulih" required>
                        <x-input-number :value="5.0" :step="0.5" unit="km" decimal />
                    </x-field>
                </x-card>

                <x-card tier="1">
                    <x-field label="Contoh Validasi Inline" error="RPE harus 1–10.">
                        <input type="text" value="12" class="w-full min-h-[48px] px-4 rounded-field glass-3 border border-error text-error font-metric font-bold focus:outline-none" />
                    </x-field>
                </x-card>
            </div>
        </section>

        <!-- 4. RPE Picker (1–10) -->
        <section class="flex flex-col gap-4">
            <h2 class="font-headline text-lg font-bold text-on-surface">4. Pemilih RPE (Grid 5×2, Tombol ≥ 44px)</h2>
            <x-card tier="1">
                <x-rpe-picker :selected="7" />
            </x-card>
        </section>

        <!-- 5. Segmented Switch & Chips -->
        <section class="flex flex-col gap-4">
            <h2 class="font-headline text-lg font-bold text-on-surface">5. Segmented Switch & Chips (Teks ≥ 12px)</h2>
            <x-card tier="1" class="flex flex-col gap-4">
                <x-segmented :options="['all' => 'Semua Sesi', 'gym' => 'Gym Saja', 'run' => 'Lari Saja']" selected="all" />
                
                <div class="flex flex-wrap gap-2">
                    <x-chip active :interactive="true">Semua</x-chip>
                    <x-chip dotColor="#006b5c" :interactive="true">Lari</x-chip>
                    <x-chip dotColor="#613dde" :interactive="true">Gym</x-chip>
                    <x-chip dotColor="#9d3b60" :interactive="true">Nutrisi</x-chip>
                </div>
            </x-card>
        </section>

        <!-- 6. Metrics & Progress Bar -->
        <section class="flex flex-col gap-4">
            <h2 class="font-headline text-lg font-bold text-on-surface">6. Metrik & Progress Bar</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-card tier="2">
                    <x-metric value="74,2" unit="kg" label="Berat Terakhir" trend="Tren 7 hari: stabil" />
                </x-card>
                <x-card tier="2">
                    <x-metric value="8.420" label="Langkah Hari Ini" trend="Target 10.000 langkah" />
                    <div class="mt-3">
                        <x-progress-bar :value="8420" :max="10000" color="primary" />
                    </div>
                </x-card>
                <x-card tier="2">
                    <x-metric value="2.350" unit="kcal" label="Total Kalori" trend="Melebihi target 150 kcal" />
                    <div class="mt-3">
                        <x-progress-bar :value="2350" :max="2200" color="tertiary" />
                    </div>
                </x-card>
            </div>
        </section>

        <!-- 7. Set Row Gym Logging -->
        <section class="flex flex-col gap-4">
            <h2 class="font-headline text-lg font-bold text-on-surface">7. Baris Set Latihan Gym</h2>
            <x-card tier="1" class="flex flex-col gap-3">
                <x-set-row :setNumber="1" :weight="15" :reps="10" :rpe="7" :completed="true" />
                <x-set-row :setNumber="2" :weight="15" :reps="10" :rpe="8" :completed="false" />
            </x-card>
        </section>

        <!-- 8. Notifikasi, Kartu Info Nyeri, & Banner -->
        <section class="flex flex-col gap-4">
            <h2 class="font-headline text-lg font-bold text-on-surface">8. Notifikasi & Pengingat Nyeri Netral (§8.12)</h2>
            <div class="flex flex-col gap-3">
                <!-- Pain Info Card -->
                <x-info-card />
                <x-info-card :recurring="true" />

                <!-- Banner States -->
                <x-banner type="error" title="Belum tersimpan" actionText="Coba lagi">
                    Belum tersimpan, data masih di form.
                </x-banner>

                <x-banner type="offline" title="Koneksi terputus">
                    Kamu offline. Menyimpan butuh koneksi.
                </x-banner>
            </div>
        </section>

        <!-- 9. Week Strip -->
        <section class="flex flex-col gap-4">
            <h2 class="font-headline text-lg font-bold text-on-surface">9. Week Strip Kalender 7 Hari</h2>
            <x-week-strip />
        </section>

        <!-- 10. Skeleton Loading -->
        <section class="flex flex-col gap-4">
            <h2 class="font-headline text-lg font-bold text-on-surface">10. Skeleton Shimmer</h2>
            <x-card tier="1" class="flex flex-col gap-3">
                <x-skeleton class="w-1/3 h-5" />
                <x-skeleton class="w-full h-10" />
                <x-skeleton class="w-2/3 h-4" />
            </x-card>
        </section>
    </div>

</body>
</html>
