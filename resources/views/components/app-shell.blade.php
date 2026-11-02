@props([
    'title' => 'Hari Ini',
])

@php
    $userName = auth()->user()->name ?? 'Pengguna';
    $currentRoute = request()->route() ? request()->route()->getName() : '';
@endphp

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — HYBRID 20</title>

    <style>[x-cloak]{display:none!important}</style>

    <!-- Preload Variable Fonts to eliminate FOUT / font flicker -->
    <link rel="preload" href="/fonts/plus-jakarta-sans-latin-variable.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/space-grotesk-latin-variable.woff2" as="font" type="font/woff2" crossorigin>

    <!-- Scripts & Styles via Vite (Self-hosted fonts, Zero CDN) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-bg font-sans text-on-surface min-h-screen flex flex-col antialiased selection:bg-primary-fixed" 
      x-data="{ 
          quickActionOpen: false, 
          weightModalOpen: false, 
          stepsModalOpen: false, 
          freeWorkoutModalOpen: false 
      }"
      @pagehide.window="quickActionOpen = false; weightModalOpen = false; stepsModalOpen = false; freeWorkoutModalOpen = false">

    <!-- DESKTOP LAYOUT (Hidden on mobile < 1024px) -->
    <div class="hidden lg:flex min-h-screen">
        <!-- Sidebar Kiri -->
        <aside id="app-desktop-sidebar" class="w-64 glass-2 border-r border-outline-variant/30 flex flex-col justify-between p-6 fixed inset-y-0 left-0 z-30">
            <div class="flex flex-col gap-8">
                <!-- Brand Title -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-pill bg-primary flex items-center justify-center text-on-primary shadow-md shadow-primary/30">
                        <x-icon name="fitness_center" class="w-5 h-5" />
                    </div>
                    <div class="flex flex-col">
                        <span class="font-headline text-lg font-bold tracking-tight text-on-surface">HYBRID 20</span>
                        <span class="font-sans text-[11px] font-medium text-on-surface-variant">Program Kebugaran 20 Minggu</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="flex flex-col gap-1.5" aria-label="Menu Utama">
                    <a href="{{ route('today') }}" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-pill font-headline text-sm font-semibold transition-[transform,colors] duration-150 ease-out select-none active:scale-[0.97] {{ $currentRoute === 'today' ? 'bg-primary text-on-primary shadow-md shadow-primary/20' : 'text-on-surface hover:bg-surface-container-high/60' }}"
                       @if($currentRoute === 'today') aria-current="page" @endif>
                        <x-icon name="calendar_today" class="w-5 h-5" />
                        <span>Hari Ini</span>
                    </a>

                    <a href="{{ route('workouts.index') }}" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-pill font-headline text-sm font-semibold transition-[transform,colors] duration-150 ease-out select-none active:scale-[0.97] {{ str_starts_with($currentRoute, 'workouts.') ? 'bg-primary text-on-primary shadow-md shadow-primary/20' : 'text-on-surface hover:bg-surface-container-high/60' }}"
                       @if(str_starts_with($currentRoute, 'workouts.')) aria-current="page" @endif>
                        <x-icon name="fitness_center" class="w-5 h-5" />
                        <span>Latihan</span>
                    </a>

                    <a href="{{ route('food-logs.index') }}" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-pill font-headline text-sm font-semibold transition-[transform,colors] duration-150 ease-out select-none active:scale-[0.97] {{ str_starts_with($currentRoute, 'food-logs.') || str_starts_with($currentRoute, 'foods.') ? 'bg-primary text-on-primary shadow-md shadow-primary/20' : 'text-on-surface hover:bg-surface-container-high/60' }}"
                       @if(str_starts_with($currentRoute, 'food-logs.') || str_starts_with($currentRoute, 'foods.')) aria-current="page" @endif>
                        <x-icon name="restaurant" class="w-5 h-5" />
                        <span>Makan</span>
                    </a>

                    <a href="{{ route('body.index') }}" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-pill font-headline text-sm font-semibold transition-[transform,colors] duration-150 ease-out select-none active:scale-[0.97] {{ str_starts_with($currentRoute, 'body.') || str_starts_with($currentRoute, 'progress-photos.') ? 'bg-primary text-on-primary shadow-md shadow-primary/20' : 'text-on-surface hover:bg-surface-container-high/60' }}"
                       @if(str_starts_with($currentRoute, 'body.') || str_starts_with($currentRoute, 'progress-photos.')) aria-current="page" @endif>
                        <x-icon name="straighten" class="w-5 h-5" />
                        <span>Tubuh</span>
                    </a>

                    <a href="{{ route('plan.show') }}" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-pill font-headline text-sm font-semibold transition-[transform,colors] duration-150 ease-out select-none active:scale-[0.97] {{ str_starts_with($currentRoute, 'plan.') ? 'bg-primary text-on-primary shadow-md shadow-primary/20' : 'text-on-surface hover:bg-surface-container-high/60' }}"
                       @if(str_starts_with($currentRoute, 'plan.')) aria-current="page" @endif>
                        <x-icon name="schedule" class="w-5 h-5" />
                        <span>Rencana</span>
                    </a>

                    <a href="{{ route('settings.edit') }}" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-pill font-headline text-sm font-semibold transition-[transform,colors] duration-150 ease-out select-none active:scale-[0.97] {{ str_starts_with($currentRoute, 'settings.') ? 'bg-primary text-on-primary shadow-md shadow-primary/20' : 'text-on-surface hover:bg-surface-container-high/60' }}"
                       @if(str_starts_with($currentRoute, 'settings.')) aria-current="page" @endif>
                        <x-icon name="settings" class="w-5 h-5" />
                        <span>Pengaturan</span>
                    </a>

                    @if(app()->environment('local'))
                        <a href="{{ route('dev.components') }}" 
                           class="flex items-center gap-3.5 px-4 py-2.5 rounded-pill font-headline text-xs font-semibold transition-[transform,colors] duration-150 ease-out select-none active:scale-[0.97] text-primary bg-primary-fixed/50 hover:bg-primary-fixed mt-4">
                            <x-icon name="tune" class="w-4 h-4" />
                            <span>Preview Komponen</span>
                        </a>
                    @endif
                </nav>
            </div>

            <!-- User Info & Logout Button -->
            <div class="pt-6 border-t border-outline-variant/30 flex flex-col gap-3">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-9 h-9 rounded-full bg-surface-container-high text-on-surface flex items-center justify-center shrink-0">
                        <x-icon name="person" class="w-5 h-5" />
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-headline text-sm font-bold text-on-surface truncate">{{ $userName }}</span>
                        <span class="font-sans text-[11px] text-on-surface-variant truncate">{{ auth()->user()->email ?? '' }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-pill text-xs font-headline font-semibold text-error hover:bg-error-container/40 active:scale-[0.97] transition-[transform,colors] duration-150 ease-out">
                        <x-icon name="logout" class="w-4 h-4" />
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Wrapper Desktop -->
        <div class="flex-1 flex flex-col ml-64 min-w-0">
            <!-- Top Bar Desktop -->
            <header class="h-20 px-12 glass-2 border-b border-outline-variant/30 flex items-center justify-between sticky top-0 z-20">
                <div class="flex items-center gap-4">
                    <h1 class="font-headline text-xl font-bold tracking-tight text-on-surface">{{ $title }}</h1>
                </div>

                <div class="flex items-center gap-3">
                    <x-button variant="primary" size="md" @click="quickActionOpen = true">
                        <x-icon name="add" class="w-4 h-4" />
                        <span>Catat</span>
                    </x-button>
                </div>
            </header>

            <!-- Page Content Desktop -->
            <main id="app-main-content-desktop" class="flex-1 p-12 max-w-[1360px] w-full">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- MOBILE LAYOUT (Default, hidden on lg screens) -->
    <div class="lg:hidden flex flex-col min-h-screen">
        <!-- Top App Bar Mobile -->
        <header id="app-mobile-topbar" class="h-16 px-4 glass-2 border-b border-outline-variant/30 fixed top-0 inset-x-0 z-30 flex items-center justify-between pt-[env(safe-area-inset-top,0px)]">
            <div class="flex flex-col">
                <span class="font-headline text-[11px] font-semibold tracking-wider text-primary uppercase">HYBRID 20</span>
                <h1 class="font-headline text-lg font-bold text-on-surface tracking-tight leading-tight">{{ $title }}</h1>
            </div>

            <div class="flex items-center gap-1">
                <a href="{{ route('plan.show') }}" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center active:scale-[0.97] transition-[transform,colors] duration-150 ease-out {{ str_starts_with($currentRoute, 'plan.') ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface' }}" aria-label="Rencana">
                    <x-icon name="schedule" class="w-5 h-5" />
                </a>
                <a href="{{ route('settings.edit') }}" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center active:scale-[0.97] transition-[transform,colors] duration-150 ease-out {{ str_starts_with($currentRoute, 'settings.') ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface' }}" aria-label="Pengaturan">
                    <x-icon name="settings" class="w-5 h-5" />
                </a>
            </div>
        </header>

        <!-- Main Content Mobile -->
        <main id="app-main-content-mobile" class="flex-1 w-full px-4 pt-20 pb-28">
            {{ $slot }}
        </main>

        <!-- Floating Bottom Tab Bar Mobile (5 slots: Hari Ini, Latihan, [+], Makan, Tubuh) -->
        <div id="app-mobile-tabbar" class="fixed inset-x-0 bottom-3 z-30 flex justify-center px-4 pointer-events-none pb-[env(safe-area-inset-bottom,0px)]">
            <nav class="w-full max-w-[420px] h-[68px] glass-2 rounded-pill shadow-2xl border border-white/80 p-1.5 flex items-center justify-between pointer-events-auto" aria-label="Navigasi Bawah">
                <!-- 1. Hari Ini -->
                <a href="{{ route('today') }}" 
                   class="flex-1 flex flex-col items-center justify-center gap-0.5 py-1 rounded-pill transition-[transform,colors] duration-150 ease-out select-none min-h-[48px] active:scale-[0.97] {{ $currentRoute === 'today' ? 'text-primary font-semibold' : 'text-on-surface-variant hover:text-on-surface font-medium' }}"
                   @if($currentRoute === 'today') aria-current="page" @endif>
                    <x-icon name="calendar_today" class="w-5 h-5" />
                    <span class="font-headline text-[12px] leading-none">Hari Ini</span>
                </a>

                <!-- 2. Latihan -->
                <a href="{{ route('workouts.index') }}" 
                   class="flex-1 flex flex-col items-center justify-center gap-0.5 py-1 rounded-pill transition-[transform,colors] duration-150 ease-out select-none min-h-[48px] active:scale-[0.97] {{ str_starts_with($currentRoute, 'workouts.') ? 'text-primary font-semibold' : 'text-on-surface-variant hover:text-on-surface font-medium' }}"
                   @if(str_starts_with($currentRoute, 'workouts.')) aria-current="page" @endif>
                    <x-icon name="fitness_center" class="w-5 h-5" />
                    <span class="font-headline text-[12px] leading-none">Latihan</span>
                </a>

                <!-- 3. [+] Quick Action Button -->
                <div class="flex-1 flex items-center justify-center">
                    <button 
                        type="button" 
                        @click="quickActionOpen = true"
                        class="w-12 h-12 rounded-full bg-gradient-to-tr from-primary to-primary-container text-on-primary flex items-center justify-center shadow-lg shadow-primary/30 active:scale-[0.95] transition-transform duration-150 ease-out"
                        aria-label="Catat cepat"
                    >
                        <x-icon name="add" class="w-6 h-6" />
                    </button>
                </div>

                <!-- 4. Makan -->
                <a href="{{ route('food-logs.index') }}" 
                   class="flex-1 flex flex-col items-center justify-center gap-0.5 py-1 rounded-pill transition-[transform,colors] duration-150 ease-out select-none min-h-[48px] active:scale-[0.97] {{ str_starts_with($currentRoute, 'food-logs.') || str_starts_with($currentRoute, 'foods.') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-on-surface font-medium' }}"
                   @if(str_starts_with($currentRoute, 'food-logs.') || str_starts_with($currentRoute, 'foods.')) aria-current="page" @endif>
                    <x-icon name="restaurant" class="w-5 h-5" />
                    <span class="font-headline text-[12px] leading-none">Makan</span>
                </a>

                <!-- 5. Tubuh -->
                <a href="{{ route('body.index') }}" 
                   class="flex-1 flex flex-col items-center justify-center gap-0.5 py-1 rounded-pill transition-[transform,colors] duration-150 ease-out select-none min-h-[48px] active:scale-[0.97] {{ str_starts_with($currentRoute, 'body.') || str_starts_with($currentRoute, 'progress-photos.') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-on-surface font-medium' }}"
                   @if(str_starts_with($currentRoute, 'body.') || str_starts_with($currentRoute, 'progress-photos.')) aria-current="page" @endif>
                    <x-icon name="straighten" class="w-5 h-5" />
                    <span class="font-headline text-[12px] leading-none">Tubuh</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- QUICK ACTION MODAL / SHEET (Terkoneksi ke tombol Catat / [+]) -->
    <div 
        x-show="quickActionOpen" 
        x-cloak 
        @keydown.escape.window="quickActionOpen = false" 
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
        aria-labelledby="quick-action-title"
    >
        <!-- Overlay Semi-Transparan (Solid tanpa blur demi 60fps) -->
        <div 
            x-show="quickActionOpen"
            x-transition:enter="ease-out duration-250"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-out duration-180"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="quickActionOpen = false"
            class="fixed inset-0 bg-on-background/40"
            aria-hidden="true"
        ></div>

        <!-- Sheet Modal Content -->
        <div class="fixed inset-x-0 bottom-0 lg:inset-0 lg:flex lg:items-center lg:justify-center p-0 lg:p-4 pointer-events-none">
            <div 
                x-show="quickActionOpen"
                x-transition:enter="ease-out duration-250"
                x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
                x-transition:leave="ease-out duration-180"
                x-transition:leave-start="translate-y-0 lg:scale-100 lg:opacity-100"
                x-transition:leave-end="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                class="w-full lg:max-w-md glass-3 rounded-t-sheet lg:rounded-card p-5 lg:p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4 overflow-y-auto"
            >
                <!-- Drag Handle Mobile -->
                <div class="flex lg:hidden items-center justify-center pt-1 pb-1">
                    <div class="w-12 h-1.5 rounded-full bg-outline-variant/60"></div>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                    <div class="flex flex-col">
                        <h3 id="quick-action-title" class="font-headline text-lg font-bold text-on-surface">Catat Cepat</h3>
                        <p class="font-sans text-xs text-on-surface-variant">Pilih aktivitas untuk dicatat hari ini</p>
                    </div>

                    <button 
                        type="button" 
                        @click="quickActionOpen = false"
                        class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high/60 active:scale-[0.97] transition-[transform,colors] duration-150 ease-out"
                        aria-label="Tutup menu"
                    >
                        <x-icon name="close" class="w-5 h-5" />
                    </button>
                </div>

                <div class="flex flex-col gap-2.5 pt-1">
                    <!-- 1. Catat Makanan -->
                    <a href="{{ route('food-logs.create') }}" class="w-full flex items-center justify-between p-3.5 rounded-field glass-2 hover:bg-surface-container-low active:scale-[0.98] transition-[transform,colors] duration-150 ease-out border border-outline-variant/30 shadow-xs text-left">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-full bg-tertiary-fixed text-on-tertiary-fixed flex items-center justify-center shrink-0">
                                <x-icon name="restaurant" class="w-5 h-5" />
                            </div>
                            <div class="flex flex-col">
                                <span class="font-headline text-sm font-semibold text-on-surface">Catat makanan</span>
                                <span class="font-sans text-xs text-on-surface-variant">Input kalori & makro harian</span>
                            </div>
                        </div>
                        <x-icon name="chevron_right" class="w-4 h-4 text-on-surface-variant" />
                    </a>

                    <!-- 2. Catat Berat -->
                    <button type="button" @click="quickActionOpen = false; weightModalOpen = true" class="w-full flex items-center justify-between p-3.5 rounded-field glass-2 hover:bg-surface-container-low active:scale-[0.98] transition-[transform,colors] duration-150 ease-out border border-outline-variant/30 shadow-xs text-left">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-full bg-secondary-fixed text-on-secondary-fixed flex items-center justify-center shrink-0">
                                <x-icon name="straighten" class="w-5 h-5" />
                            </div>
                            <div class="flex flex-col">
                                <span class="font-headline text-sm font-semibold text-on-surface">Catat berat</span>
                                <span class="font-sans text-xs text-on-surface-variant">Perbarui timbangan hari ini</span>
                            </div>
                        </div>
                        <x-icon name="chevron_right" class="w-4 h-4 text-on-surface-variant" />
                    </button>

                    <!-- 3. Catat Langkah -->
                    <button type="button" @click="quickActionOpen = false; stepsModalOpen = true" class="w-full flex items-center justify-between p-3.5 rounded-field glass-2 hover:bg-surface-container-low active:scale-[0.98] transition-[transform,colors] duration-150 ease-out border border-outline-variant/30 shadow-xs text-left">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-full bg-primary-fixed text-primary flex items-center justify-center shrink-0">
                                <x-icon name="directions_run" class="w-5 h-5" />
                            </div>
                            <div class="flex flex-col">
                                <span class="font-headline text-sm font-semibold text-on-surface">Catat langkah</span>
                                <span class="font-sans text-xs text-on-surface-variant">Isi total langkah hari ini</span>
                            </div>
                        </div>
                        <x-icon name="chevron_right" class="w-4 h-4 text-on-surface-variant" />
                    </button>

                    <!-- 4. Latihan Lepas -->
                    <button type="button" @click="quickActionOpen = false; freeWorkoutModalOpen = true" class="w-full flex items-center justify-between p-3.5 rounded-field glass-2 hover:bg-surface-container-low active:scale-[0.98] transition-[transform,colors] duration-150 ease-out border border-outline-variant/30 shadow-xs text-left">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-full bg-surface-container-high text-on-surface flex items-center justify-center shrink-0">
                                <x-icon name="fitness_center" class="w-5 h-5" />
                            </div>
                            <div class="flex flex-col">
                                <span class="font-headline text-sm font-semibold text-on-surface">Latihan lepas</span>
                                <span class="font-sans text-xs text-on-surface-variant">Gym atau lari tanpa jadwal</span>
                            </div>
                        </div>
                        <x-icon name="chevron_right" class="w-4 h-4 text-on-surface-variant" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CATAT BERAT BADAN -->
    <div x-show="weightModalOpen" x-cloak @keydown.escape.window="weightModalOpen = false" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="weight-modal-title">
        <div class="fixed inset-0 bg-on-background/40" @click="weightModalOpen = false"></div>
        <div class="fixed inset-x-0 bottom-0 lg:inset-0 lg:flex lg:items-center lg:justify-center p-0 lg:p-4 pointer-events-none">
            <div 
                x-show="weightModalOpen"
                x-transition:enter="ease-out duration-250"
                x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
                x-transition:leave="ease-out duration-180"
                x-transition:leave-start="translate-y-0 lg:scale-100 lg:opacity-100"
                x-transition:leave-end="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                class="w-full lg:max-w-md glass-3 rounded-t-sheet lg:rounded-card p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4"
            >
                <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                    <div>
                        <h3 id="weight-modal-title" class="font-headline text-lg font-bold text-on-surface">Catat Berat Badan</h3>
                        <p class="font-sans text-xs text-on-surface-variant">Masukkan hasil timbangan badan</p>
                    </div>
                    <button type="button" @click="weightModalOpen = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-on-surface-variant hover:text-on-surface active:scale-[0.97] transition-[transform,colors]" aria-label="Tutup">
                        <x-icon name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form 
                    x-data="{ 
                        weightDate: '{{ $shellToday }}', 
                        weightKg: '',
                        submitting: false,
                        async submitWeight(e) {
                            if (!this.weightKg) return;
                            this.submitting = true;
                            try {
                                const response = await fetch('/body/' + this.weightDate, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        _method: 'PUT',
                                        weight_kg: this.weightKg
                                    })
                                });
                                if (response.ok) {
                                    weightModalOpen = false;
                                    window.dispatchEvent(new CustomEvent('show-toast', {
                                        detail: { message: 'Tersimpan' }
                                    }));
                                    window.dispatchEvent(new CustomEvent('weight-updated', {
                                        detail: { date: this.weightDate, weight: this.weightKg }
                                    }));
                                    this.weightKg = '';
                                } else {
                                    e.target.submit();
                                }
                            } catch (err) {
                                e.target.submit();
                            } finally {
                                this.submitting = false;
                            }
                        }
                    }" 
                    :action="'/body/' + weightDate" 
                    method="POST" 
                    @submit.prevent="submitWeight($event)"
                    class="flex flex-col gap-4"
                >
                    @csrf
                    @method('PUT')

                    <x-field label="Tanggal Timbangan" name="measured_at">
                        <input type="date" x-model="weightDate" class="w-full h-11 px-4 rounded-field glass-3 border border-outline-variant/50 text-sm font-sans focus:outline-primary" required />
                    </x-field>

                    <x-field label="Berat Badan (kg)" name="weight_kg" hint="{{ $shellLatestWeight ? 'Terakhir: ' . \App\Support\Num::format($shellLatestWeight, 1) . ' kg' : 'Belum ada data' }}">
                        <input type="text" x-model="weightKg" name="weight_kg" inputmode="decimal" placeholder="Contoh: 74,5" class="w-full h-12 px-4 rounded-field glass-3 border border-outline-variant/50 font-metric text-lg font-bold text-on-surface focus:outline-primary tabular-nums" required autofocus />
                    </x-field>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <x-button type="button" variant="ghost" @click="weightModalOpen = false">Batal</x-button>
                        <x-button type="submit" variant="primary" ::disabled="submitting">Simpan Berat</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CATAT TOTAL LANGKAH -->
    <div x-show="stepsModalOpen" x-cloak @keydown.escape.window="stepsModalOpen = false" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="steps-modal-title">
        <div class="fixed inset-0 bg-on-background/40" @click="stepsModalOpen = false"></div>
        <div class="fixed inset-x-0 bottom-0 lg:inset-0 lg:flex lg:items-center lg:justify-center p-0 lg:p-4 pointer-events-none">
            <div 
                x-show="stepsModalOpen"
                x-transition:enter="ease-out duration-250"
                x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
                x-transition:leave="ease-out duration-180"
                x-transition:leave-start="translate-y-0 lg:scale-100 lg:opacity-100"
                x-transition:leave-end="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                class="w-full lg:max-w-md glass-3 rounded-t-sheet lg:rounded-card p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4"
            >
                <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                    <div>
                        <h3 id="steps-modal-title" class="font-headline text-lg font-bold text-on-surface">Catat Total Langkah</h3>
                        <p class="font-sans text-xs text-on-surface-variant">Isi total langkah harian</p>
                    </div>
                    <button type="button" @click="stepsModalOpen = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-on-surface-variant hover:text-on-surface active:scale-[0.97] transition-[transform,colors]" aria-label="Tutup">
                        <x-icon name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form 
                    x-data="{ 
                        stepsDate: '{{ $shellToday }}', 
                        stepsCount: '{{ $shellTodaySteps ?? '' }}',
                        submitting: false,
                        async submitSteps(e) {
                            if (!this.stepsCount) return;
                            this.submitting = true;
                            try {
                                const response = await fetch('/daily-activities/' + this.stepsDate, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        _method: 'PUT',
                                        steps: this.stepsCount
                                    })
                                });
                                if (response.ok) {
                                    stepsModalOpen = false;
                                    window.dispatchEvent(new CustomEvent('show-toast', {
                                        detail: { message: 'Tersimpan' }
                                    }));
                                    window.dispatchEvent(new CustomEvent('steps-updated', {
                                        detail: { date: this.stepsDate, steps: this.stepsCount }
                                    }));
                                } else {
                                    e.target.submit();
                                }
                            } catch (err) {
                                e.target.submit();
                            } finally {
                                this.submitting = false;
                            }
                        }
                    }" 
                    :action="'/daily-activities/' + stepsDate" 
                    method="POST" 
                    @submit.prevent="submitSteps($event)"
                    class="flex flex-col gap-4"
                >
                    @csrf
                    @method('PUT')

                    <x-field label="Tanggal Aktivitas" name="activity_date">
                        <input type="date" x-model="stepsDate" class="w-full h-11 px-4 rounded-field glass-3 border border-outline-variant/50 text-sm font-sans focus:outline-primary" required />
                    </x-field>

                    <x-field label="Total Langkah" name="steps" hint="Total termasuk langkah saat lari">
                        <input type="text" x-model="stepsCount" name="steps" inputmode="numeric" placeholder="Contoh: 10.000" class="w-full h-12 px-4 rounded-field glass-3 border border-outline-variant/50 font-metric text-lg font-bold text-on-surface focus:outline-primary tabular-nums" required autofocus />
                    </x-field>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <x-button type="button" variant="ghost" @click="stepsModalOpen = false">Batal</x-button>
                        <x-button type="submit" variant="primary" ::disabled="submitting">Simpan Langkah</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL LATIHAN LEPAS -->
    <div x-show="freeWorkoutModalOpen" x-cloak @keydown.escape.window="freeWorkoutModalOpen = false" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="free-workout-modal-title">
        <div class="fixed inset-0 bg-on-background/40" @click="freeWorkoutModalOpen = false"></div>
        <div class="fixed inset-x-0 bottom-0 lg:inset-0 lg:flex lg:items-center lg:justify-center p-0 lg:p-4 pointer-events-none">
            <div 
                x-show="freeWorkoutModalOpen"
                x-transition:enter="ease-out duration-250"
                x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
                x-transition:leave="ease-out duration-180"
                x-transition:leave-start="translate-y-0 lg:scale-100 lg:opacity-100"
                x-transition:leave-end="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                class="w-full lg:max-w-md glass-3 rounded-t-sheet lg:rounded-card p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4"
            >
                <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                    <div>
                        <h3 id="free-workout-modal-title" class="font-headline text-lg font-bold text-on-surface">Pilih Latihan Lepas</h3>
                        <p class="font-sans text-xs text-on-surface-variant">Latihan di luar jadwal 20 minggu</p>
                    </div>
                    <button type="button" @click="freeWorkoutModalOpen = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-on-surface-variant hover:text-on-surface active:scale-[0.97] transition-[transform,colors]" aria-label="Tutup">
                        <x-icon name="close" class="w-5 h-5" />
                    </button>
                </div>

                <div class="flex flex-col gap-3 py-2">
                    <a href="{{ route('workouts.create', ['type' => 'gym']) }}" class="flex items-center gap-4 p-4 rounded-field glass-2 hover:bg-surface-container border border-outline-variant/30 active:scale-[0.98] transition-[transform,colors] duration-150 ease-out">
                        <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <x-icon name="fitness_center" class="w-6 h-6" />
                        </div>
                        <div class="flex flex-col">
                            <span class="font-headline text-base font-bold text-on-surface">Latihan Gym Lepas</span>
                            <span class="font-sans text-xs text-on-surface-variant">Catat repetisi, beban, dan set mandiri</span>
                        </div>
                    </a>

                    <a href="{{ route('workouts.create', ['type' => 'run']) }}" class="flex items-center gap-4 p-4 rounded-field glass-2 hover:bg-surface-container border border-outline-variant/30 active:scale-[0.98] transition-[transform,colors] duration-150 ease-out">
                        <div class="w-12 h-12 rounded-full bg-secondary/10 text-secondary flex items-center justify-center shrink-0">
                            <x-icon name="directions_run" class="w-6 h-6" />
                        </div>
                        <div class="flex flex-col">
                            <span class="font-headline text-base font-bold text-on-surface">Sesi Lari Lepas</span>
                            <span class="font-sans text-xs text-on-surface-variant">Catat jarak km, durasi, dan pace mandiri</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <x-toast />
    @if(session('toast'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: @json(is_array(session('toast')) ? session('toast')['message'] : session('toast')) }
                }));
            });
        </script>
    @endif
</body>
</html>
