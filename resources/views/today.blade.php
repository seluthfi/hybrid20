@php
    $settings = auth()->user()->settings;
    $stepsTarget = $settings?->daily_steps_target ?? 10000;
    $calorieTarget = $settings?->daily_calorie_target ?? 2200;
    $proteinTarget = $settings?->protein_target_g ?? 140;

    $dayName = $todayLocal->locale('id')->isoFormat('dddd, D MMM');
    $weekPosLabel = $currentWeek ? "Minggu {$currentWeek->week_number} dari " . ($program->duration_weeks ?? 20) : null;
    $weekLabel = $currentWeek ? "Minggu {$currentWeek->week_number}" : "Minggu Ini";
@endphp

<x-app-shell title="Hari Ini">
    <div class="flex flex-col gap-6 max-w-5xl mx-auto pb-16">
        <!-- TOP GREETING & STATUS MINGGU -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex flex-col">
                @if($weekPosLabel)
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="w-2 h-2 rounded-full bg-secondary"></span>
                        <span class="font-headline text-xs font-semibold text-primary uppercase tracking-wider">
                            {{ $weekPosLabel }}
                        </span>
                        @if($currentWeek->is_repeat)
                            <span class="px-2 py-0.5 rounded-full bg-tertiary-fixed text-on-tertiary-fixed font-headline text-[10px] font-bold">
                                Ulangan
                            </span>
                        @endif
                    </div>
                @endif
                <h1 class="font-headline text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">
                    {{ $dayName }}
                </h1>
                <p class="font-sans text-xs sm:text-sm text-on-surface-variant mt-0.5">
                    Tenang, konsisten, selangkah demi selangkah.
                </p>
            </div>
        </div>

        <!-- KARTU PENGINGAT NYERI NETRAL (§8.12) -->
        @if($painNotice)
            <div class="p-4 rounded-card glass-2 border border-outline-variant/60 flex items-start gap-3.5 bg-surface-container-high/40 shadow-xs">
                <x-icon name="info" class="w-5 h-5 text-on-surface-variant shrink-0 mt-0.5" />
                <div class="flex flex-col gap-1.5 flex-1 min-w-0">
                    <span class="font-headline text-xs font-bold uppercase tracking-wider text-on-surface">Pemberitahuan Kondisi Tubuh</span>
                    <p class="font-sans text-xs text-on-surface leading-relaxed">
                        {{ $painNotice['message'] }}
                    </p>
                    <a href="{{ route('workouts.show', $painNotice['latest_workout_id']) }}" class="inline-flex items-center gap-1 font-headline text-xs font-semibold text-primary hover:underline">
                        <span>Lihat sesi dengan catatan nyeri: {{ $painNotice['latest_workout_title'] }}</span>
                        <x-icon name="arrow_forward" class="w-3.5 h-3.5" />
                    </a>
                </div>
            </div>
        @endif

        <!-- STATE PROGRAM: BELUM ADA PROGRAM / SEBELUM MULAI / PROGRAM SELESAI -->
        @if($programState === 'no_program')
            <x-card tier="1" padding="lg">
                <x-empty-state 
                    icon="calendar_today"
                    title="Belum Ada Program Aktif"
                    message="Pilih tanggal mulai program pada hari Senin untuk mengaktifkan jadwal latihan 20 minggu."
                >
                    <div class="mt-4">
                        <a href="{{ route('settings.edit') }}" class="px-5 py-2.5 rounded-pill bg-primary text-on-primary font-headline text-xs font-semibold shadow-md">
                            Atur Tanggal Mulai di Pengaturan
                        </a>
                    </div>
                </x-empty-state>
            </x-card>
        @elseif($programState === 'before_start')
            <x-card tier="1" padding="lg">
                <x-empty-state 
                    icon="schedule"
                    title="Program Dimulai {{ $daysUntilStart }} Hari Lagi"
                    message="Program 20 minggu dijadwalkan mulai pada hari Senin, {{ \Carbon\Carbon::parse($program->start_date)->locale('id')->isoFormat('D MMMM Y') }}."
                >
                    <div class="mt-4">
                        <a href="{{ route('plan.show') }}" class="px-5 py-2.5 rounded-pill glass-2 border border-outline-variant/30 font-headline text-xs font-semibold text-on-surface hover:bg-surface-container-high">
                            Lihat Rencana Latihan
                        </a>
                    </div>
                </x-empty-state>
            </x-card>
        @elseif($programState === 'completed')
            <x-card tier="1" padding="lg">
                <x-empty-state 
                    icon="check_circle"
                    title="Program 20 Minggu Telah Selesai"
                    message="Selamat, kamu telah menyelesaikan seluruh jadwal program 20 minggu!"
                >
                    <div class="mt-4">
                        <a href="{{ route('workouts.index') }}" class="px-5 py-2.5 rounded-pill bg-primary text-on-primary font-headline text-xs font-semibold shadow-md">
                            Lihat Seluruh Riwayat Latihan
                        </a>
                    </div>
                </x-empty-state>
            </x-card>
        @else
            <!-- PROGRAM AKTIF: LAYOUT UTAMA (2 Kolom Responsif) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- KOLOM KIRI (7/12 Desktop): Sesi Hari Ini -->
                <div class="lg:col-span-7 flex flex-col gap-5">
                    <div class="flex items-center justify-between">
                        <h2 class="font-headline text-lg font-bold text-on-surface">Sesi Hari Ini</h2>
                        <span class="font-sans text-xs text-on-surface-variant">
                            {{ $scheduledWorkouts->count() }} Jadwal
                        </span>
                    </div>

                    @if($scheduledWorkouts->isEmpty())
                        <x-card tier="1" padding="lg">
                            <x-empty-state 
                                icon="hotel"
                                title="Tidak Ada Jadwal Latihan"
                                message="Tidak ada sesi terjadwal untuk hari ini. Kamu bisa beristirahat atau mencatat latihan lepas jika berolahraga."
                            />
                        </x-card>
                    @else
                        <div class="flex flex-col gap-4">
                            @foreach($scheduledWorkouts as $sched)
                                @php
                                    $isGym = $sched->workout_type === 'gym';
                                    $isRun = $sched->workout_type === 'run';
                                    $isRest = $sched->workout_type === 'rest';
                                    $latestWorkout = $sched->workouts->first();
                                    $isCompleted = in_array($sched->status, ['completed', 'partial']);
                                @endphp

                                @if($isRest)
                                    <!-- KARTU SABTU / HARI ISTIRAHAT (§10.2: tanpa tombol aksi) -->
                                    <x-card tier="1" padding="lg" class="flex flex-col gap-3 border-outline-variant/30 bg-surface-container-low/50">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-12 h-12 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center shrink-0">
                                                <x-icon name="hotel" class="w-6 h-6" />
                                            </div>
                                            <div class="flex flex-col">
                                                <h3 class="font-headline text-lg font-bold text-on-surface">Istirahat Penuh</h3>
                                                <p class="font-sans text-xs text-on-surface-variant mt-0.5">
                                                    Hari pemulihan. Luangkan waktu untuk regenerasi otot dan istirahat yang cukup.
                                                </p>
                                            </div>
                                        </div>
                                    </x-card>
                                @else
                                    <!-- KARTU SESI GYM ATAU LARI -->
                                    <x-card tier="1" padding="lg" class="flex flex-col gap-4 border-outline-variant/30 transition-all shadow-xs">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-start gap-3.5 min-w-0">
                                                <div class="w-12 h-12 rounded-full {{ $isGym ? 'bg-primary/10 text-primary' : 'bg-secondary/10 text-secondary' }} flex items-center justify-center shrink-0 shadow-2xs">
                                                    <x-icon :name="$isGym ? 'fitness_center' : 'directions_run'" class="w-6 h-6" />
                                                </div>
                                                <div class="flex flex-col min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <h3 class="font-headline text-lg font-bold text-on-surface truncate">
                                                            {{ $sched->title }}
                                                        </h3>
                                                    </div>
                                                    <div class="flex items-center gap-2 font-sans text-xs text-on-surface-variant mt-0.5">
                                                        @if($isGym)
                                                            <span>{{ $sched->exercises->count() }} Latihan</span>
                                                            <span class="text-outline">•</span>
                                                            <span>{{ $sched->exercises->sum('target_sets') }} Set Target</span>
                                                        @elseif($isRun)
                                                            <span class="font-semibold text-secondary">Target {{ \App\Support\Num::format($sched->planned_distance_km, 1) }} km</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <x-badge-status :status="$sched->status" />
                                        </div>

                                        <!-- Instruksi Arahan Seed (Bila ada) -->
                                        @if($sched->instructions)
                                            <p class="font-sans text-xs text-on-surface-variant leading-relaxed p-3 rounded-field bg-surface-container-high/40">
                                                {{ $sched->instructions }}
                                            </p>
                                        @endif

                                        <!-- SESI SUDAH DICATAT (Completed / Partial) -->
                                        @if($isCompleted && $latestWorkout)
                                            <div class="flex items-center justify-between p-3 rounded-field glass-3 border border-outline-variant/30">
                                                <div class="flex flex-col font-sans text-xs">
                                                    <span class="text-on-surface-variant font-medium">Hasil Catatan Sesi:</span>
                                                    <div class="flex items-center gap-2 font-headline font-bold text-on-surface mt-0.5">
                                                        @if($isGym)
                                                            <span>{{ \App\Support\Num::format($latestWorkout->calculateVolume()) }} kg</span>
                                                            <span class="text-outline font-normal">•</span>
                                                            <span>RPE {{ $latestWorkout->rpe ?? '-' }}</span>
                                                        @else
                                                            <span class="text-secondary">{{ \App\Support\Num::format($latestWorkout->distance_km, 1) }} km</span>
                                                            <span class="text-outline font-normal">•</span>
                                                            <span>{{ \App\Support\PaceCalculator::formatPace($latestWorkout->pace_seconds_per_km) }}</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <a href="{{ route('workouts.show', $latestWorkout->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-pill glass-2 font-headline text-xs font-semibold text-primary hover:bg-surface-container-high transition-all">
                                                    <span>Lihat detail</span>
                                                    <x-icon name="chevron_right" class="w-4 h-4" />
                                                </a>
                                            </div>
                                        @else
                                            <!-- SESI BELUM DICATAT (Planned / Skipped): Aksi Mulai / Lewati -->
                                            <div class="flex items-center justify-between gap-3 pt-1">
                                                <form method="POST" action="{{ route('scheduled-workouts.skip', $sched->id) }}">
                                                    @csrf
                                                    <button type="submit" class="px-4 py-2.5 rounded-pill text-xs font-headline font-semibold text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition-all">
                                                        Lewati
                                                    </button>
                                                </form>

                                                @if($isGym)
                                                    <a href="{{ route('workouts.create', ['scheduled' => $sched->id]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-pill bg-primary text-on-primary font-headline text-xs sm:text-sm font-semibold shadow-md active:scale-95 transition-all">
                                                        <span>Mulai Latihan</span>
                                                        <x-icon name="arrow_forward" class="w-4 h-4" />
                                                    </a>
                                                @else
                                                    <a href="{{ route('workouts.create', ['type' => 'run', 'scheduled' => $sched->id]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-pill bg-secondary text-white font-headline text-xs sm:text-sm font-semibold shadow-md active:scale-95 transition-all">
                                                        <span>Catat Lari</span>
                                                        <x-icon name="arrow_forward" class="w-4 h-4" />
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </x-card>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- KOLOM KANAN (5/12 Desktop): Metrik Progres Harian & Tren Berat -->
                <div class="lg:col-span-5 flex flex-col gap-5">
                    <!-- Kartu Progres Harian (Langkah & Nutrisi) -->
                    <div class="flex items-center justify-between">
                        <h2 class="font-headline text-lg font-bold text-on-surface">Progres Harian</h2>
                        <span class="font-sans text-xs text-on-surface-variant">Panduan Fleksibel</span>
                    </div>

                    <x-card tier="1" padding="md" class="flex flex-col gap-4">
                        <!-- Langkah Harian -->
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <x-icon name="directions_walk" class="w-4 h-4 text-primary" />
                                    <span class="font-headline text-xs font-bold text-on-surface uppercase tracking-wider">Langkah</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-metric text-sm font-bold text-on-surface tabular-nums">
                                        {{ \App\Support\Num::format($todaySteps) }} <span class="text-xs font-normal text-on-surface-variant">/ {{ \App\Support\Num::format($stepsTarget) }}</span>
                                    </span>
                                    <button type="button" @click="stepsModalOpen = true" class="w-7 h-7 rounded-full flex items-center justify-center text-primary hover:bg-primary-fixed/40 transition-colors" aria-label="Catat langkah">
                                        <x-icon name="add" class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                            <x-progress-bar :value="$todaySteps" :max="$stepsTarget" color="primary" />
                            <span class="font-sans text-[11px] text-on-surface-variant">Total termasuk langkah saat lari</span>
                        </div>

                        <div class="border-t border-outline-variant/20 pt-3 flex flex-col gap-3">
                            <!-- Kalori -->
                            <div class="flex flex-col gap-1.5">
                                <div class="flex items-center justify-between text-xs font-sans">
                                    <span class="text-on-surface-variant font-medium">Kalori Harian</span>
                                    <span class="font-metric font-semibold text-on-surface tabular-nums">
                                        {{ \App\Support\Num::format($todayCalories) }} / {{ \App\Support\Num::format($calorieTarget) }} kcal
                                    </span>
                                </div>
                                <x-progress-bar :value="$todayCalories" :max="$calorieTarget" color="tertiary" />
                                <div class="flex items-center justify-between text-[11px] font-sans text-on-surface-variant">
                                    @if($todayCalories > $calorieTarget)
                                        <span>Melebihi target {{ \App\Support\Num::format($todayCalories - $calorieTarget) }} kcal</span>
                                    @else
                                        <span>Sisa {{ \App\Support\Num::format($calorieTarget - $todayCalories) }} kcal</span>
                                    @endif
                                    <span>Target panduan fleksibel</span>
                                </div>
                            </div>

                            <!-- Protein -->
                            <div class="flex flex-col gap-1.5">
                                <div class="flex items-center justify-between text-xs font-sans">
                                    <span class="text-on-surface-variant font-medium">Protein</span>
                                    <span class="font-metric font-semibold text-on-surface tabular-nums">
                                        {{ \App\Support\Num::format($todayProtein) }} / {{ $proteinTarget }} g
                                    </span>
                                </div>
                                <x-progress-bar :value="$todayProtein" :max="$proteinTarget" color="secondary" />
                                <div class="flex items-center justify-between text-[11px] font-sans text-on-surface-variant">
                                    @if($todayProtein < $proteinTarget)
                                        <span>Sisa {{ \App\Support\Num::format($proteinTarget - $todayProtein) }} g</span>
                                    @else
                                        <span>{{ \App\Support\Num::format($todayProtein) }} g tercatat</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-card>

                    <!-- Kartu Berat Badan & Tren 7 Hari (D13, §8.8) -->
                    <x-card tier="1" padding="md" class="flex flex-col gap-3" x-data="miniWeightChart()">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-icon name="scale" class="w-4 h-4 text-secondary" />
                                <span class="font-headline text-xs font-bold text-on-surface uppercase tracking-wider">Berat Badan</span>
                            </div>
                            <button type="button" @click="weightModalOpen = true" class="inline-flex items-center gap-1 font-headline text-xs font-semibold text-secondary hover:underline">
                                <x-icon name="add" class="w-3.5 h-3.5" />
                                <span>Catat</span>
                            </button>
                        </div>

                        <div class="flex items-baseline justify-between pt-1">
                            <div class="flex items-baseline gap-1.5">
                                <span class="font-metric text-3xl font-bold text-on-surface tabular-nums">
                                    {{ $latestMeasurement ? \App\Support\Num::format($latestMeasurement->weight_kg, 1) : '-' }}
                                </span>
                                <span class="font-sans text-xs text-on-surface-variant">kg</span>
                            </div>
                            <span class="font-sans text-xs text-on-surface-variant">
                                @if($latestMeasurement)
                                    {{ \Carbon\Carbon::parse($latestMeasurement->measured_at)->locale('id')->diffForHumans() }}
                                @else
                                    Belum ada data
                                @endif
                            </span>
                        </div>

                        <!-- Mini Chart Tren (Desktop) -->
                        <div class="hidden sm:block pt-1">
                            <div x-show="!isEmpty" class="h-24 w-full relative">
                                <canvas x-ref="miniCanvas"></canvas>
                            </div>
                            <div x-show="isEmpty" class="py-3 text-center text-xs font-sans text-outline">
                                Belum cukup data untuk grafik tren
                            </div>
                        </div>

                        <!-- Baris Tren Berat Netral (§8.8) -->
                        <div class="pt-2 border-t border-outline-variant/20 flex items-center justify-between font-sans text-xs text-on-surface-variant">
                            <span>{{ $weightTrend['label'] }}</span>
                        </div>
                    </x-card>
                </div>
            </div>

            <!-- STRIP RINGKASAN 7 HARI MINGGU BERJALAN (§8.3, §10.2) -->
            @if($weekSummary->isNotEmpty())
                <div class="flex flex-col gap-3 pt-2">
                    <div class="flex items-center justify-between px-1">
                        <h3 class="font-headline text-sm font-bold uppercase tracking-wider text-on-surface">
                            Ringkasan {{ $weekLabel }}
                        </h3>
                        <a href="{{ route('plan.show', $currentPosition) }}" class="font-headline text-xs font-semibold text-primary hover:underline">
                            Buka Rencana Minggu Ini
                        </a>
                    </div>

                    <div class="grid grid-cols-7 gap-2">
                        @foreach($weekSummary as $day)
                            <div class="flex flex-col items-center justify-center p-2.5 rounded-field glass-2 border {{ $day['is_today'] ? 'border-primary shadow-xs' : 'border-outline-variant/30' }}">
                                <span class="font-headline text-[11px] uppercase tracking-wider {{ $day['is_today'] ? 'text-primary font-bold' : 'text-on-surface-variant' }}">
                                    {{ $day['day_name'] }}
                                </span>
                                <span class="font-metric text-sm font-bold {{ $day['is_today'] ? 'text-primary' : 'text-on-surface' }} mt-0.5 tabular-nums">
                                    {{ $day['day_num'] }}
                                </span>
                                <div class="mt-1.5">
                                    @if($day['status'] === 'completed')
                                        <x-icon name="check_circle" class="w-4 h-4 text-secondary" />
                                    @elseif($day['status'] === 'partial')
                                        <x-icon name="timelapse" class="w-4 h-4 text-primary" />
                                    @elseif($day['status'] === 'skipped')
                                        <x-icon name="event_busy" class="w-4 h-4 text-outline" />
                                    @elseif($day['status'] === 'planned')
                                        <x-icon name="schedule" class="w-4 h-4 text-outline/60" />
                                    @else
                                        <span class="w-1.5 h-1.5 rounded-full bg-outline/40 my-1"></span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- GRAFIK 20 MINGGU: JARAK LARI TARGET VS AKTUAL (PLAN.md §6.1, §9.8, §10.2) -->
            <div class="pt-4" x-data="runWeeklyChart()">
                <x-card tier="1" padding="lg" class="flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-outline-variant/20">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <x-icon name="bar_chart" class="w-5 h-5 text-primary" />
                                <h3 class="font-headline text-base sm:text-lg font-bold text-on-surface">
                                    Jarak Lari per Minggu (Target vs Aktual)
                                </h3>
                            </div>
                            <p class="font-sans text-xs text-on-surface-variant mt-0.5">
                                Target template 20 minggu vs total jarak aktual yang dicatat per minggu.
                            </p>
                        </div>

                        <!-- Legend -->
                        <div class="flex items-center flex-wrap gap-3 text-xs font-sans text-on-surface-variant">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-sm bg-primary-container"></span>
                                <span>Target</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-sm bg-secondary"></span>
                                <span>Aktual</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-sm bg-tertiary"></span>
                                <span>Deload</span>
                            </div>
                        </div>
                    </div>

                    <!-- Canvas Area -->
                    <div x-show="!isEmpty" class="relative w-full h-64 sm:h-72">
                        <canvas x-ref="canvas"></canvas>
                    </div>

                    <!-- Empty State -->
                    <div x-show="isEmpty" class="py-10 flex flex-col items-center justify-center text-center">
                        <x-icon name="directions_run" class="w-10 h-10 text-outline mb-2" />
                        <span class="font-headline text-sm font-semibold text-on-surface">Belum Ada Program Aktif</span>
                        <span class="font-sans text-xs text-on-surface-variant max-w-sm mt-0.5">
                            Grafik perbandingan lari mingguan akan otomatis tampil setelah program 20 minggu berjalan.
                        </span>
                    </div>

                    <!-- Screen Reader Accessible Table (PLAN.md §9.8) -->
                    <table class="sr-only" aria-label="Tabel ringkasan jarak lari target versus aktual 20 minggu">
                        <caption class="sr-only">Tabel Data Jarak Lari Mingguan</caption>
                        <thead>
                            <tr>
                                <th>Minggu</th>
                                <th>Target Jarak (km)</th>
                                <th>Aktual Selesai (km)</th>
                                <th>Fase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="w in weeks" :key="w.calendar_position">
                                <tr>
                                    <td x-text="w.label"></td>
                                    <td x-text="w.target_km + ' km'"></td>
                                    <td x-text="w.actual_km + ' km'"></td>
                                    <td x-text="w.phase_label || '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </x-card>
            </div>
        @endif
    </div>

    <!-- Chart.js Alpine Logic -->
    <script>
        function miniWeightChart() {
            return {
                isEmpty: false,
                isLoading: false,
                chartInstance: null,

                init() {
                    this.fetchData();
                },

                async fetchData() {
                    this.isLoading = true;
                    try {
                        const res = await fetch('/charts/weight?range=4w');
                        const data = await res.json();
                        this.isEmpty = data.empty || !data.values || data.values.length < 2;

                        if (!this.isEmpty && this.$refs.miniCanvas) {
                            this.$nextTick(() => {
                                this.renderChart(data);
                            });
                        }
                    } catch (e) {
                        this.isEmpty = true;
                    } finally {
                        this.isLoading = false;
                    }
                },

                renderChart(data) {
                    if (this.chartInstance) {
                        this.chartInstance.destroy();
                        this.chartInstance = null;
                    }
                    if (!this.$refs.miniCanvas) return;

                    const ctx = this.$refs.miniCanvas.getContext('2d');
                    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    this.chartInstance = new window.Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Berat (kg)',
                                    data: data.values,
                                    borderColor: '#613dde',
                                    backgroundColor: 'rgba(97, 61, 222, 0.08)',
                                    borderWidth: 2,
                                    pointRadius: 3,
                                    pointBackgroundColor: '#613dde',
                                    fill: true,
                                    tension: 0.2
                                },
                                {
                                    label: 'Rata-rata 7 Hari',
                                    data: data.moving_averages,
                                    borderColor: '#006b5c',
                                    borderWidth: 1.5,
                                    borderDash: [4, 4],
                                    pointRadius: 0,
                                    fill: false,
                                    tension: 0.3,
                                    spanGaps: false
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: prefersReducedMotion ? false : { duration: 400 },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(28, 25, 41, 0.9)',
                                    padding: 8,
                                    cornerRadius: 6,
                                    callbacks: {
                                        label: (c) => `${c.dataset.label}: ${c.parsed.y.toLocaleString('id-ID')} kg`
                                    }
                                }
                            },
                            scales: {
                                x: { display: false },
                                y: {
                                    display: true,
                                    grid: { color: 'rgba(202, 196, 215, 0.15)', drawBorder: false },
                                    ticks: { font: { family: 'Space Grotesk', size: 10 }, color: '#797586' }
                                }
                            }
                        }
                    });
                }
            }
        }

        function runWeeklyChart() {
            return {
                isEmpty: false,
                isLoading: false,
                weeks: [],
                currentPosition: null,
                chartInstance: null,

                init() {
                    this.fetchData();
                },

                async fetchData() {
                    this.isLoading = true;
                    try {
                        const res = await fetch('/charts/run-weekly');
                        const data = await res.json();
                        this.isEmpty = data.empty || !data.weeks || data.weeks.length === 0;
                        this.weeks = data.weeks || [];
                        this.currentPosition = data.current_position;

                        if (!this.isEmpty && this.$refs.canvas) {
                            this.$nextTick(() => {
                                this.renderChart(data);
                            });
                        }
                    } catch (e) {
                        this.isEmpty = true;
                    } finally {
                        this.isLoading = false;
                    }
                },

                renderChart(data) {
                    if (this.chartInstance) {
                        this.chartInstance.destroy();
                        this.chartInstance = null;
                    }
                    if (!this.$refs.canvas) return;

                    const ctx = this.$refs.canvas.getContext('2d');
                    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    const labels = data.weeks.map(w => w.label);
                    const targets = data.weeks.map(w => w.target_km);
                    const actuals = data.weeks.map(w => w.actual_km);

                    const targetColors = data.weeks.map(w => {
                        if (w.phase_label === 'deload') return 'rgba(157, 59, 96, 0.55)';
                        if (w.phase_label === 'taper') return 'rgba(97, 61, 222, 0.55)';
                        return 'rgba(122, 90, 248, 0.55)';
                    });

                    const actualColors = data.weeks.map(w => {
                        if (w.is_current) return '#006b5c';
                        return 'rgba(0, 107, 92, 0.85)';
                    });

                    this.chartInstance = new window.Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Target Jarak (km)',
                                    data: targets,
                                    backgroundColor: targetColors,
                                    borderColor: targetColors.map(c => c.replace('0.55', '1.0')),
                                    borderWidth: 1.5,
                                    borderRadius: 4,
                                    barPercentage: 0.8,
                                    categoryPercentage: 0.7
                                },
                                {
                                    label: 'Aktual Selesai (km)',
                                    data: actuals,
                                    backgroundColor: actualColors,
                                    borderColor: '#006b5c',
                                    borderWidth: 1.5,
                                    borderRadius: 4,
                                    barPercentage: 0.8,
                                    categoryPercentage: 0.7
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: prefersReducedMotion ? false : { duration: 500 },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(28, 25, 41, 0.9)',
                                    titleFont: { family: 'Space Grotesk', size: 13, weight: 700 },
                                    bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                                    padding: 10,
                                    cornerRadius: 8,
                                    callbacks: {
                                        afterTitle: function(items) {
                                            const idx = items[0].dataIndex;
                                            const w = data.weeks[idx];
                                            let note = '';
                                            if (w.phase_label === 'deload') note += ' [Minggu Ringan / Deload]';
                                            if (w.phase_label === 'taper') note += ' [Taper / Tes 10K]';
                                            if (w.is_current) note += ' (Minggu Berjalan)';
                                            return note;
                                        },
                                        label: function(context) {
                                            return `${context.dataset.label}: ${context.parsed.y.toLocaleString('id-ID')} km`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        color: (ctx) => {
                                            const w = data.weeks[ctx.index];
                                            if (w && w.is_current) return '#613dde';
                                            return '#484555';
                                        },
                                        font: (ctx) => {
                                            const w = data.weeks[ctx.index];
                                            return {
                                                family: 'Space Grotesk',
                                                size: 11,
                                                weight: (w && w.is_current) ? '700' : '500'
                                            };
                                        }
                                    }
                                },
                                y: {
                                    grid: { color: 'rgba(202, 196, 215, 0.25)', drawBorder: false },
                                    ticks: {
                                        color: '#484555',
                                        font: { family: 'Space Grotesk', size: 12 },
                                        callback: function(val) { return val + ' km'; }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        }
    </script>
</x-app-shell>
