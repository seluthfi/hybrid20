<x-app-shell title="Riwayat Latihan">
    <div class="flex flex-col gap-6 max-w-4xl mx-auto pb-16">
        <!-- Header Riwayat Latihan -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col">
                <span class="font-headline text-xs font-semibold text-primary uppercase tracking-wider">Aktivitas Latihan</span>
                <h1 class="font-headline text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">Riwayat Sesi</h1>
            </div>

            <div class="flex items-center gap-2">
                <x-button variant="primary" size="md" @click="freeWorkoutModalOpen = true">
                    <x-icon name="add" class="w-4 h-4" />
                    <span>Catat Latihan</span>
                </x-button>
            </div>
        </div>

        <!-- Segmented Filter Server-side (Semua / Gym / Lari) -->
        <div class="w-full p-1 rounded-pill glass-2 border border-outline-variant/30 flex items-center justify-between shadow-xs">
            <a href="{{ route('workouts.index', ['type' => 'all']) }}" 
               class="flex-1 py-2 px-3 rounded-pill text-center font-headline text-xs sm:text-sm font-semibold transition-all select-none {{ $type === 'all' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface' }}">
                Semua
            </a>
            <a href="{{ route('workouts.index', ['type' => 'gym']) }}" 
               class="flex-1 py-2 px-3 rounded-pill text-center font-headline text-xs sm:text-sm font-semibold transition-all select-none {{ $type === 'gym' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface' }}">
                Gym
            </a>
            <a href="{{ route('workouts.index', ['type' => 'run']) }}" 
               class="flex-1 py-2 px-3 rounded-pill text-center font-headline text-xs sm:text-sm font-semibold transition-all select-none {{ $type === 'run' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface' }}">
                Lari
            </a>
        </div>

        <!-- Banner Ringkasan Mingguan (§8.3 & §8.5) -->
        <x-card tier="2" padding="md" class="relative overflow-hidden border border-outline-variant/30">
            <div class="grid grid-cols-3 gap-2 divide-x divide-outline-variant/30 text-center">
                <!-- Volume Gym -->
                <div class="flex flex-col items-center justify-center p-1">
                    <span class="font-sans text-[11px] text-on-surface-variant uppercase tracking-wider">Volume {{ $weekLabel }}</span>
                    <span class="font-metric text-lg sm:text-xl font-bold text-on-surface mt-0.5 tabular-nums">
                        {{ \App\Support\Num::format($weeklyVolume) }} <span class="font-sans text-xs font-normal text-on-surface-variant">kg</span>
                    </span>
                    <span class="font-sans text-[10px] text-on-surface-variant">beban × rep</span>
                </div>

                <!-- Jarak Lari -->
                <div class="flex flex-col items-center justify-center p-1">
                    <span class="font-sans text-[11px] text-on-surface-variant uppercase tracking-wider">Jarak Lari</span>
                    <span class="font-metric text-lg sm:text-xl font-bold text-secondary mt-0.5 tabular-nums">
                        {{ \App\Support\Num::format($weeklyDistance, 1) }} <span class="font-sans text-xs font-normal text-on-surface-variant">km</span>
                    </span>
                    <span class="font-sans text-[10px] text-on-surface-variant">total lari</span>
                </div>

                <!-- Status Sesi Mingguan -->
                <div class="flex flex-col items-center justify-center p-1">
                    <span class="font-sans text-[11px] text-on-surface-variant uppercase tracking-wider">Sesi Terjadwal</span>
                    <span class="font-metric text-lg sm:text-xl font-bold text-primary mt-0.5 tabular-nums">
                        {{ $completedSessions }} <span class="font-sans text-xs font-normal text-on-surface-variant">/ {{ $totalScheduled }}</span>
                    </span>
                    <span class="font-sans text-[10px] text-on-surface-variant">
                        @if($partialCount > 0)
                            {{ $partialCount }} parsial
                        @else
                            sesi selesai
                        @endif
                    </span>
                </div>
            </div>
        </x-card>

        <!-- DAFTAR KARTU SESI LATIHAN -->
        @if($workouts->isEmpty())
            <x-card tier="1" padding="lg">
                <x-empty-state 
                    :icon="$type === 'run' ? 'directions_run' : ($type === 'gym' ? 'fitness_center' : 'history')"
                    :title="$type === 'run' ? 'Belum Ada Catatan Lari' : ($type === 'gym' ? 'Belum Ada Catatan Gym' : 'Belum Ada Catatan Latihan')"
                    :message="$type === 'run' ? 'Catat sesi pertamamu dari jadwal program atau latihan lepas.' : ($type === 'gym' ? 'Catat sesi gym pertamamu untuk mulai memantau volume latihan.' : 'Mulai catat aktivitas harianmu untuk melacak perkembangan program.')"
                />
            </x-card>
        @else
            <div class="flex flex-col gap-3">
                @foreach($workouts as $workout)
                    @php
                        $isGym = $workout->workout_type === 'gym';
                        $dateFormatted = \Carbon\Carbon::parse($workout->performed_on)->locale('id')->isoFormat('dddd, D MMM Y');
                        $durationFormatted = \App\Support\PaceCalculator::formatDuration($workout->duration_seconds);
                    @endphp

                    <a href="{{ route('workouts.show', $workout->id) }}" 
                       class="group relative flex flex-col p-4 rounded-card glass-2 hover:bg-surface-container-low active:scale-[0.99] border border-outline-variant/30 transition-all shadow-xs"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3.5 min-w-0">
                                <!-- Ikon Tipe -->
                                <div class="w-12 h-12 rounded-full {{ $isGym ? 'bg-primary/10 text-primary' : 'bg-secondary/10 text-secondary' }} flex items-center justify-center shrink-0 shadow-2xs">
                                    <x-icon :name="$isGym ? 'fitness_center' : 'directions_run'" class="w-6 h-6" />
                                </div>

                                <div class="flex flex-col min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h2 class="font-headline text-base font-bold text-on-surface truncate group-hover:text-primary transition-colors">
                                            {{ $workout->title }}
                                        </h2>
                                        @if($workout->pain_flag)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-error-container text-on-error-container text-[11px] font-sans font-medium" title="Catatan nyeri dilaporkan">
                                                <x-icon name="info" class="w-3 h-3 text-error" />
                                                <span>Nyeri</span>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 font-sans text-xs text-on-surface-variant mt-0.5">
                                        <span>{{ $dateFormatted }}</span>
                                        @if($workout->duration_seconds)
                                            <span class="text-outline">•</span>
                                            <span>{{ $durationFormatted }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <x-badge-status :status="$workout->status" />
                        </div>

                        <!-- Metrik Ringkas -->
                        <div class="mt-3 pt-2.5 border-t border-outline-variant/20 flex flex-wrap items-center gap-2 text-xs font-sans text-on-surface-variant">
                            @if($isGym)
                                @php
                                    $exerciseCount = $workout->exercises->count();
                                    $setCount = $workout->exercises->sum(fn($e) => $e->sets->count());
                                    $volume = $workout->calculateVolume();
                                @endphp
                                <span class="bg-surface-container-high/60 px-2.5 py-1 rounded-pill font-medium text-on-surface">
                                    {{ $exerciseCount }} Gerakan
                                </span>
                                <span class="bg-surface-container-high/60 px-2.5 py-1 rounded-pill font-medium text-on-surface">
                                    {{ $setCount }} Set
                                </span>
                                @if($volume > 0)
                                    <span class="bg-surface-container-high/60 px-2.5 py-1 rounded-pill font-metric font-bold text-primary tabular-nums">
                                        {{ \App\Support\Num::format($volume) }} kg
                                    </span>
                                @endif
                            @else
                                @if($workout->distance_km)
                                    <span class="bg-surface-container-high/60 px-2.5 py-1 rounded-pill font-metric font-bold text-secondary tabular-nums">
                                        {{ \App\Support\Num::format($workout->distance_km, 1) }} km
                                    </span>
                                @endif
                                @if($workout->pace_seconds_per_km)
                                    <span class="bg-surface-container-high/60 px-2.5 py-1 rounded-pill font-metric font-semibold text-on-surface tabular-nums">
                                        {{ \App\Support\PaceCalculator::formatPace($workout->pace_seconds_per_km) }}
                                    </span>
                                @endif
                            @endif

                            @if($workout->rpe)
                                <span class="bg-primary-fixed/40 text-primary px-2.5 py-1 rounded-pill font-headline text-xs font-semibold">
                                    RPE {{ $workout->rpe }}
                                </span>
                            @endif

                            @if($workout->body_condition)
                                <span class="capitalize bg-surface-container-high/40 px-2.5 py-1 rounded-pill text-[11px]">
                                    {{ $workout->body_condition }}
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Paginasi -->
            <div class="pt-4">
                {{ $workouts->links() }}
            </div>
        @endif
    </div>
</x-app-shell>
