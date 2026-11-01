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

    <!-- Scripts & Styles via Vite (Self-hosted fonts, Zero CDN) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-bg font-sans text-on-surface min-h-screen flex flex-col antialiased selection:bg-primary-fixed" x-data="{ quickActionOpen: false }">

    <!-- DESKTOP LAYOUT (Hidden on mobile < 1024px) -->
    <div class="hidden lg:flex min-h-screen">
        <!-- Sidebar Kiri -->
        <aside class="w-64 glass-2 border-r border-outline-variant/30 flex flex-col justify-between p-6 fixed inset-y-0 left-0 z-30">
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
                       class="flex items-center gap-3.5 px-4 py-3 rounded-pill font-headline text-sm font-semibold transition-all select-none {{ $currentRoute === 'today' ? 'bg-primary text-on-primary shadow-md shadow-primary/20' : 'text-on-surface hover:bg-surface-container-high/60' }}"
                       @if($currentRoute === 'today') aria-current="page" @endif>
                        <x-icon name="calendar_today" class="w-5 h-5" />
                        <span>Hari Ini</span>
                    </a>

                    <a href="#" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-pill font-headline text-sm font-semibold transition-all select-none text-on-surface hover:bg-surface-container-high/60">
                        <x-icon name="fitness_center" class="w-5 h-5" />
                        <span>Latihan</span>
                    </a>

                    <a href="#" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-pill font-headline text-sm font-semibold transition-all select-none text-on-surface hover:bg-surface-container-high/60">
                        <x-icon name="restaurant" class="w-5 h-5" />
                        <span>Makan</span>
                    </a>

                    <a href="#" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-pill font-headline text-sm font-semibold transition-all select-none text-on-surface hover:bg-surface-container-high/60">
                        <x-icon name="straighten" class="w-5 h-5" />
                        <span>Tubuh</span>
                    </a>

                    <a href="#" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-pill font-headline text-sm font-semibold transition-all select-none text-on-surface hover:bg-surface-container-high/60">
                        <x-icon name="schedule" class="w-5 h-5" />
                        <span>Rencana</span>
                    </a>

                    <a href="#" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-pill font-headline text-sm font-semibold transition-all select-none text-on-surface hover:bg-surface-container-high/60">
                        <x-icon name="settings" class="w-5 h-5" />
                        <span>Pengaturan</span>
                    </a>

                    @if(app()->environment('local'))
                        <a href="{{ route('dev.components') }}" 
                           class="flex items-center gap-3.5 px-4 py-2.5 rounded-pill font-headline text-xs font-semibold transition-all select-none text-primary bg-primary-fixed/50 hover:bg-primary-fixed mt-4">
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
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-pill text-xs font-headline font-semibold text-error hover:bg-error-container/40 active:scale-95 transition-all">
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
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-pill bg-surface-container-high/80 font-headline text-xs font-semibold text-on-surface-variant border border-outline-variant/30">
                        Minggu 1 dari 20
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <x-button variant="primary" size="md" @click="quickActionOpen = true">
                        <x-icon name="add" class="w-4 h-4" />
                        <span>Catat</span>
                    </x-button>
                </div>
            </header>

            <!-- Page Content Desktop -->
            <main class="flex-1 p-12 max-w-[1360px] w-full">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- MOBILE LAYOUT (Default, hidden on lg screens) -->
    <div class="lg:hidden flex flex-col min-h-screen">
        <!-- Top App Bar Mobile -->
        <header class="h-16 px-4 glass-2 border-b border-outline-variant/30 fixed top-0 inset-x-0 z-30 flex items-center justify-between pt-[env(safe-area-inset-top,0px)]">
            <div class="flex flex-col">
                <span class="font-headline text-[11px] font-semibold tracking-wider text-primary uppercase">HYBRID 20</span>
                <h1 class="font-headline text-lg font-bold text-on-surface tracking-tight leading-tight">{{ $title }}</h1>
            </div>

            <div class="flex items-center gap-1">
                <button type="button" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-on-surface-variant hover:text-on-surface" aria-label="Rencana">
                    <x-icon name="schedule" class="w-5 h-5" />
                </button>
                <button type="button" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-on-surface-variant hover:text-on-surface" aria-label="Pengaturan">
                    <x-icon name="settings" class="w-5 h-5" />
                </button>
            </div>
        </header>

        <!-- Main Content Mobile -->
        <main class="flex-1 w-full px-4 pt-20 pb-28">
            {{ $slot }}
        </main>

        <!-- Floating Bottom Tab Bar Mobile (5 slots: Hari Ini, Latihan, [+], Makan, Tubuh) -->
        <div class="fixed inset-x-0 bottom-3 z-30 flex justify-center px-4 pointer-events-none pb-[env(safe-area-inset-bottom,0px)]">
            <nav class="w-full max-w-[420px] h-[68px] glass-2 rounded-pill shadow-2xl border border-white/80 p-1.5 flex items-center justify-between pointer-events-auto" aria-label="Navigasi Bawah">
                <!-- 1. Hari Ini -->
                <a href="{{ route('today') }}" 
                   class="flex-1 flex flex-col items-center justify-center gap-0.5 py-1 rounded-pill transition-all select-none min-h-[48px] {{ $currentRoute === 'today' ? 'text-primary font-semibold' : 'text-on-surface-variant hover:text-on-surface font-medium' }}"
                   @if($currentRoute === 'today') aria-current="page" @endif>
                    <x-icon name="calendar_today" class="w-5 h-5" />
                    <span class="font-headline text-[12px] leading-none">Hari Ini</span>
                </a>

                <!-- 2. Latihan -->
                <a href="#" 
                   class="flex-1 flex flex-col items-center justify-center gap-0.5 py-1 rounded-pill transition-all select-none min-h-[48px] text-on-surface-variant hover:text-on-surface font-medium">
                    <x-icon name="fitness_center" class="w-5 h-5" />
                    <span class="font-headline text-[12px] leading-none">Latihan</span>
                </a>

                <!-- 3. [+] Quick Action Button -->
                <div class="flex-1 flex items-center justify-center">
                    <button 
                        type="button" 
                        @click="quickActionOpen = true"
                        class="w-12 h-12 rounded-full bg-gradient-to-tr from-primary to-primary-container text-on-primary flex items-center justify-center shadow-lg shadow-primary/30 active:scale-95 transition-transform"
                        aria-label="Catat cepat"
                    >
                        <x-icon name="add" class="w-6 h-6" />
                    </button>
                </div>

                <!-- 4. Makan -->
                <a href="#" 
                   class="flex-1 flex flex-col items-center justify-center gap-0.5 py-1 rounded-pill transition-all select-none min-h-[48px] text-on-surface-variant hover:text-on-surface font-medium">
                    <x-icon name="restaurant" class="w-5 h-5" />
                    <span class="font-headline text-[12px] leading-none">Makan</span>
                </a>

                <!-- 5. Tubuh -->
                <a href="#" 
                   class="flex-1 flex flex-col items-center justify-center gap-0.5 py-1 rounded-pill transition-all select-none min-h-[48px] text-on-surface-variant hover:text-on-surface font-medium">
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
        <!-- Overlay Blur -->
        <div 
            x-show="quickActionOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="quickActionOpen = false"
            class="fixed inset-0 bg-on-background/25 backdrop-blur-md"
            aria-hidden="true"
        ></div>

        <!-- Sheet Modal Content -->
        <div class="fixed inset-x-0 bottom-0 lg:inset-0 lg:flex lg:items-center lg:justify-center p-0 lg:p-4 pointer-events-none">
            <div 
                x-show="quickActionOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
                x-transition:leave="ease-in duration-200"
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
                        class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high/60 active:scale-95 transition-all"
                        aria-label="Tutup menu"
                    >
                        <x-icon name="close" class="w-5 h-5" />
                    </button>
                </div>

                <div class="flex flex-col gap-2.5 pt-1">
                    <!-- 1. Catat Makanan -->
                    <a href="#" class="flex items-center justify-between p-3.5 rounded-field glass-2 hover:bg-surface-container-low active:scale-[0.98] transition-all border border-outline-variant/30 shadow-xs">
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
                    <a href="#" class="flex items-center justify-between p-3.5 rounded-field glass-2 hover:bg-surface-container-low active:scale-[0.98] transition-all border border-outline-variant/30 shadow-xs">
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
                    </a>

                    <!-- 3. Catat Langkah -->
                    <a href="#" class="flex items-center justify-between p-3.5 rounded-field glass-2 hover:bg-surface-container-low active:scale-[0.98] transition-all border border-outline-variant/30 shadow-xs">
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
                    </a>

                    <!-- 4. Latihan Lepas -->
                    <a href="#" class="flex items-center justify-between p-3.5 rounded-field glass-2 hover:bg-surface-container-low active:scale-[0.98] transition-all border border-outline-variant/30 shadow-xs">
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
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
