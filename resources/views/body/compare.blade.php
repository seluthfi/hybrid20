<x-app-shell :title="'Komparasi Foto Progres'">
    <div class="flex flex-col gap-6 w-full max-w-4xl mx-auto pb-12">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a href="{{ route('progress-photos.index') }}" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface hover:bg-surface-container active:scale-[0.97] transition-[transform,colors] duration-150 ease-out">
                    <x-icon name="arrow_back" class="w-5 h-5" />
                </a>
                <h1 class="font-headline text-2xl font-bold tracking-tight text-on-surface">Komparasi Langsung</h1>
            </div>

            <a href="{{ route('progress-photos.index') }}">
                <x-button variant="ghost" size="sm">
                    Kembali ke Galeri
                </x-button>
            </a>
        </div>

        @if(! $photoA || ! $photoB)
            <x-card level="1" class="p-8 text-center flex flex-col items-center justify-center gap-3">
                <x-icon name="compare" class="w-10 h-10 text-outline" />
                <span class="font-headline text-base font-bold text-on-surface">Belum Cukup Foto untuk Komparasi</span>
                <p class="font-sans text-xs text-on-surface-variant max-w-sm">
                    Kamu membutuhkan minimal 2 foto dengan sudut pose yang sama untuk melihat perbandingan fisik.
                </p>
                <a href="{{ route('progress-photos.index') }}">
                    <x-button variant="primary" size="sm">
                        Buka Galeri Foto
                    </x-button>
                </a>
            </x-card>
        @else
            <!-- Side-by-Side Dual Frame (PLAN.md §10.9) -->
            <x-card level="1" class="p-6 flex flex-col gap-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-outline-variant/30">
                    <div>
                        <span class="text-xs font-sans text-primary font-semibold uppercase tracking-wider">Perbandingan Pose Identik</span>
                        <h2 class="font-headline text-lg font-bold text-on-surface">
                            Pose {{ ucfirst($photoA->pose_label) }}
                        </h2>
                    </div>

                    @if($deltaWeight !== null)
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-surface-container-high text-xs font-sans">
                            <span class="font-headline font-bold text-on-surface">Selisih Berat:</span>
                            <span class="font-headline font-bold tabular-nums {{ $deltaWeight <= 0 ? 'text-secondary' : 'text-primary' }}">
                                {{ $deltaWeight > 0 ? '+' : '' }}{{ number_format($deltaWeight, 1, ',', '.') }} kg
                            </span>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Photo A (Sebelum / Lebih Awal) -->
                    <div class="flex flex-col gap-2">
                        <div class="relative w-full aspect-[3/4] rounded-2xl overflow-hidden bg-surface-container-high border border-outline-variant/30 shadow-md">
                            <img src="{{ route('progress-photos.image', $photoA->id) }}" alt="Foto komparasi awal" class="w-full h-full object-cover">
                            <div class="absolute bottom-0 inset-x-0 p-3 bg-gradient-to-t from-black/80 to-transparent flex items-baseline justify-between text-white">
                                <span class="font-headline text-xs font-bold">{{ $photoA->taken_at->translatedFormat('d M Y') }}</span>
                                @if($measurementA && $measurementA->weight_kg)
                                    <span class="font-headline text-sm font-bold tabular-nums">{{ number_format($measurementA->weight_kg, 1, ',', '.') }} kg</span>
                                @endif
                            </div>
                        </div>
                        <span class="text-center font-sans text-xs text-outline font-medium">Foto Awal</span>
                    </div>

                    <!-- Photo B (Terkini / Lebih Akhir) -->
                    <div class="flex flex-col gap-2">
                        <div class="relative w-full aspect-[3/4] rounded-2xl overflow-hidden bg-surface-container-high border border-outline-variant/30 shadow-md">
                            <img src="{{ route('progress-photos.image', $photoB->id) }}" alt="Foto komparasi akhir" class="w-full h-full object-cover">
                            <div class="absolute bottom-0 inset-x-0 p-3 bg-gradient-to-t from-black/80 to-transparent flex items-baseline justify-between text-white">
                                <span class="font-headline text-xs font-bold">{{ $photoB->taken_at->translatedFormat('d M Y') }}</span>
                                @if($measurementB && $measurementB->weight_kg)
                                    <span class="font-headline text-sm font-bold tabular-nums">{{ number_format($measurementB->weight_kg, 1, ',', '.') }} kg</span>
                                @endif
                            </div>
                        </div>
                        <span class="text-center font-sans text-xs text-primary font-bold">Foto Terkini</span>
                    </div>
                </div>
            </x-card>
        @endif

    </div>
</x-app-shell>
