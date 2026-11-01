<x-app-shell title="Hari Ini">
    <div class="flex flex-col gap-6 max-w-4xl mx-auto">
        <!-- Header Salam & Slogan Tunggal -->
        <div class="flex flex-col gap-1">
            <span class="font-headline text-xs font-semibold text-primary uppercase tracking-wider">
                Selamat Datang, {{ auth()->user()->name }}
            </span>
            <h2 class="font-headline text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">
                Hari Ini
            </h2>
            <p class="font-sans text-xs sm:text-sm text-on-surface-variant">
                Tenang, konsisten, selangkah demi selangkah.
            </p>
        </div>

        <!-- Empty State Program / Sesi -->
        <x-card tier="1" padding="lg">
            <x-empty-state 
                icon="calendar_today"
                title="Sesi Latihan Belum Dimulai"
                message="Template program 20 minggu, pencatatan latihan, dan nutrisi harian akan aktif mulai Fase berikutnya."
            />
        </x-card>
    </div>
</x-app-shell>
