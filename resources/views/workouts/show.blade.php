@php
    $tz = auth()->user()->settings?->timezone ?? 'Asia/Jakarta';
    $isGym = $workout->workout_type === 'gym';
    $performedAtLocal = \Carbon\Carbon::parse($workout->performed_at)->setTimezone($tz);
    $dateFormatted = \Carbon\Carbon::parse($workout->performed_on)->locale('id')->isoFormat('dddd, D MMMM Y');
    $timeFormatted = $performedAtLocal->format('H:i');
    $durationFormatted = \App\Support\PaceCalculator::formatDuration($workout->duration_seconds);
    $totalSets = $isGym ? $workout->exercises->sum(fn($e) => $e->sets->count()) : 0;
    $totalVolume = $isGym ? $workout->calculateVolume() : 0.0;
@endphp

<x-app-shell title="Detail Latihan">
    <div x-data="{ deleteModalOpen: false }" class="flex flex-col gap-6 max-w-4xl mx-auto pb-16">
        <!-- Header Aksi & Navigasi -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('workouts.index') }}" 
                   class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full glass-2 flex items-center justify-center text-on-surface hover:bg-surface-container-high transition-all"
                   aria-label="Kembali ke Riwayat"
                >
                    <x-icon name="arrow_back" class="w-5 h-5" />
                </a>
                <div class="flex flex-col">
                    <span class="font-headline text-xs font-semibold {{ $isGym ? 'text-primary' : 'text-secondary' }} uppercase tracking-wider">
                        {{ $isGym ? 'Latihan Gym' : 'Sesi Lari' }}
                    </span>
                    <h1 class="font-headline text-2xl sm:text-3xl font-bold text-on-surface tracking-tight">Detail Sesi</h1>
                </div>
            </div>

            <!-- Tombol Ubah & Hapus -->
            <div class="flex items-center gap-2">
                <a href="{{ route('workouts.edit', $workout->id) }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-pill glass-2 border border-outline-variant/40 font-headline text-xs sm:text-sm font-semibold text-on-surface hover:bg-surface-container-high active:scale-95 transition-all shadow-xs"
                >
                    <x-icon name="edit" class="w-4 h-4" />
                    <span>Ubah</span>
                </a>

                <button 
                    type="button" 
                    @click="deleteModalOpen = true"
                    class="w-10 h-10 min-w-[40px] min-h-[40px] rounded-full flex items-center justify-center text-error hover:bg-error-container/40 active:scale-95 transition-all"
                    aria-label="Hapus sesi"
                >
                    <x-icon name="delete" class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Kartu Ringkasan Sesi (Liquid Glass Tier 1) -->
        <x-card tier="1" padding="lg" class="flex flex-col gap-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full {{ $isGym ? 'bg-primary/10 text-primary' : 'bg-secondary/10 text-secondary' }} flex items-center justify-center shrink-0 shadow-sm">
                        <x-icon :name="$isGym ? 'fitness_center' : 'directions_run'" class="w-6 h-6" />
                    </div>
                    <div class="flex flex-col">
                        <h2 class="font-headline text-xl sm:text-2xl font-bold text-on-surface leading-tight">
                            {{ $workout->title }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-2 font-sans text-xs sm:text-sm text-on-surface-variant mt-1">
                            <span class="flex items-center gap-1">
                                <x-icon name="calendar_today" class="w-4 h-4 text-outline" />
                                <span>{{ $dateFormatted }}</span>
                            </span>
                            <span class="text-outline">•</span>
                            <span class="flex items-center gap-1">
                                <x-icon name="schedule" class="w-4 h-4 text-outline" />
                                <span>Pukul {{ $timeFormatted }}</span>
                            </span>
                            @if($workout->duration_seconds)
                                <span class="text-outline">•</span>
                                <span>{{ $durationFormatted }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <x-badge-status :status="$workout->status" />
            </div>

            <!-- Grid Metrik Kunci -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 border-t border-outline-variant/20">
                @if($isGym)
                    <div class="p-3 rounded-field glass-3 border border-outline-variant/30 flex flex-col">
                        <span class="font-sans text-[11px] text-on-surface-variant uppercase tracking-wider">Volume</span>
                        <span class="font-metric text-lg sm:text-xl font-bold text-on-surface mt-0.5 tabular-nums">
                            {{ \App\Support\Num::format($totalVolume) }} <span class="text-xs font-normal text-on-surface-variant">kg</span>
                        </span>
                        <span class="font-sans text-[10px] text-on-surface-variant">beban × rep</span>
                    </div>

                    <div class="p-3 rounded-field glass-3 border border-outline-variant/30 flex flex-col">
                        <span class="font-sans text-[11px] text-on-surface-variant uppercase tracking-wider">Gerakan</span>
                        <span class="font-metric text-lg sm:text-xl font-bold text-on-surface mt-0.5 tabular-nums">
                            {{ $workout->exercises->count() }} <span class="text-xs font-normal text-on-surface-variant">Latihan</span>
                        </span>
                    </div>

                    <div class="p-3 rounded-field glass-3 border border-outline-variant/30 flex flex-col">
                        <span class="font-sans text-[11px] text-on-surface-variant uppercase tracking-wider">Total Set</span>
                        <span class="font-metric text-lg sm:text-xl font-bold text-on-surface mt-0.5 tabular-nums">
                            {{ $totalSets }} <span class="text-xs font-normal text-on-surface-variant">Set</span>
                        </span>
                    </div>
                @else
                    <div class="p-3 rounded-field glass-3 border border-outline-variant/30 flex flex-col">
                        <span class="font-sans text-[11px] text-on-surface-variant uppercase tracking-wider">Jarak</span>
                        <span class="font-metric text-lg sm:text-xl font-bold text-secondary mt-0.5 tabular-nums">
                            {{ \App\Support\Num::format($workout->distance_km, 1) }} <span class="text-xs font-normal text-on-surface-variant">km</span>
                        </span>
                    </div>

                    <div class="p-3 rounded-field glass-3 border border-outline-variant/30 flex flex-col">
                        <span class="font-sans text-[11px] text-on-surface-variant uppercase tracking-wider">Pace Rata-rata</span>
                        <span class="font-metric text-lg sm:text-xl font-bold text-on-surface mt-0.5 tabular-nums">
                            {{ \App\Support\PaceCalculator::formatPace($workout->pace_seconds_per_km) }}
                        </span>
                    </div>

                    <div class="p-3 rounded-field glass-3 border border-outline-variant/30 flex flex-col">
                        <span class="font-sans text-[11px] text-on-surface-variant uppercase tracking-wider">Langkah Lari</span>
                        <span class="font-metric text-lg sm:text-xl font-bold text-on-surface mt-0.5 tabular-nums">
                            {{ $workout->steps ? \App\Support\Num::format($workout->steps) : '-' }}
                        </span>
                    </div>
                @endif

                <div class="p-3 rounded-field glass-3 border border-outline-variant/30 flex flex-col">
                    <span class="font-sans text-[11px] text-on-surface-variant uppercase tracking-wider">RPE Sesi</span>
                    <span class="font-metric text-lg sm:text-xl font-bold text-primary mt-0.5 tabular-nums">
                        {{ $workout->rpe ? $workout->rpe . ' / 10' : '-' }}
                    </span>
                    <span class="font-sans text-[10px] text-on-surface-variant capitalize">
                        Kondisi: {{ $workout->body_condition ?: 'normal' }}
                    </span>
                </div>
            </div>
        </x-card>

        <!-- KARTU PERINGATAN NYERI (§8.12) -->
        @if($workout->pain_flag)
            <div class="p-4 rounded-card glass-2 border border-outline-variant/50 flex items-start gap-3.5 bg-surface-container-high/40">
                <x-icon name="info" class="w-6 h-6 text-on-surface-variant shrink-0 mt-0.5" />
                <div class="flex flex-col gap-1.5">
                    <span class="font-headline text-xs font-bold uppercase tracking-wider text-on-surface">Catatan Nyeri Dilaporkan</span>
                    <p class="font-sans text-xs text-on-surface leading-relaxed">
                        Pertimbangkan menghentikan progresi dan berkonsultasi dengan tenaga kesehatan.
                    </p>
                    @if($workout->pain_notes)
                        <div class="p-2.5 rounded-field glass-3 border border-outline-variant/30 font-sans text-xs text-on-surface-variant">
                            {{ $workout->pain_notes }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- RINCIAN GERAKAN GYM (Jika Tipe Gym) -->
        @if($isGym)
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-headline text-lg font-bold text-on-surface">Rincian Latihan & Set</h3>
                    <span class="font-sans text-xs text-on-surface-variant">{{ $workout->exercises->count() }} Gerakan</span>
                </div>

                <div class="flex flex-col gap-4">
                    @foreach($workout->exercises as $idx => $exercise)
                        @php
                            $exVolume = $exercise->calculateVolume();
                        @endphp
                        <x-card tier="2" padding="md" class="flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-full bg-primary/10 text-primary font-metric text-xs font-bold flex items-center justify-center">
                                        {{ $idx + 1 }}
                                    </div>
                                    <h4 class="font-headline text-base font-bold text-on-surface">
                                        {{ $exercise->exercise_name }}
                                    </h4>
                                </div>

                                @if($exVolume > 0)
                                    <span class="font-metric text-xs font-bold text-primary tabular-nums">
                                        {{ \App\Support\Num::format($exVolume) }} kg
                                    </span>
                                @endif
                            </div>

                            <!-- Tabel Baris Set -->
                            <div class="rounded-field glass-3 overflow-hidden border border-outline-variant/30">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="border-b border-outline-variant/30 text-on-surface-variant font-headline text-[11px] uppercase tracking-wider bg-surface-container-high/30">
                                            <th class="py-2 px-3">Set</th>
                                            <th class="py-2 px-3">Beban</th>
                                            <th class="py-2 px-3">Rep / Detik</th>
                                            <th class="py-2 px-3 text-right">RPE</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-outline-variant/20 font-sans">
                                        @foreach($exercise->sets as $set)
                                            <tr class="hover:bg-surface-container-high/30 transition-colors">
                                                <td class="py-2.5 px-3 font-headline font-bold text-on-surface">
                                                    {{ $set->set_number }}
                                                </td>
                                                <td class="py-2.5 px-3 font-metric font-semibold text-on-surface tabular-nums">
                                                    @if($set->weight_kg > 0)
                                                        {{ \App\Support\Num::format($set->weight_kg, 1) }} kg
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 font-metric font-semibold text-on-surface tabular-nums">
                                                    @if($set->duration_seconds > 0)
                                                        {{ $set->duration_seconds }} detik
                                                    @else
                                                        {{ $set->reps }} rep
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 text-right font-metric font-bold text-primary tabular-nums">
                                                    {{ $set->rpe ? '@' . $set->rpe : '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Catatan Umum -->
        @if($workout->general_notes)
            <x-card tier="2" padding="md" class="flex flex-col gap-1.5">
                <span class="font-headline text-xs font-bold uppercase tracking-wider text-on-surface-variant">Catatan Tambahan</span>
                <p class="font-sans text-xs sm:text-sm text-on-surface leading-relaxed">
                    {{ $workout->general_notes }}
                </p>
            </x-card>
        @endif

        <!-- MODAL KONFIRMASI HAPUS SESI (D23) -->
        <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-on-background/25 backdrop-blur-md" @click="deleteModalOpen = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="w-full max-w-md glass-3 rounded-card p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4">
                    <div class="flex items-center gap-3 text-error">
                        <div class="w-10 h-10 rounded-full bg-error-container flex items-center justify-center text-on-error-container shrink-0">
                            <x-icon name="delete" class="w-5 h-5" />
                        </div>
                        <h3 class="font-headline text-lg font-bold text-on-surface">Hapus Sesi Latihan?</h3>
                    </div>

                    <p class="font-sans text-xs text-on-surface-variant leading-relaxed">
                        Catatan sesi <strong class="text-on-surface">{{ $workout->title }}</strong> 
                        @if($isGym)
                            dengan {{ $workout->exercises->count() }} gerakan dan {{ $totalSets }} set
                        @else
                            dengan jarak {{ \App\Support\Num::format($workout->distance_km, 1) }} km
                        @endif
                        akan dihapus dari riwayat.
                    </p>

                    @if($workout->scheduled_workout_id)
                        <div class="p-3 rounded-field bg-surface-container-high/60 border border-outline-variant/30 text-xs font-sans text-on-surface-variant flex items-start gap-2">
                            <x-icon name="info" class="w-4 h-4 text-primary shrink-0 mt-0.5" />
                            <span>Karena sesi ini tertaut ke jadwal program, status sesi terjadwal akan otomatis kembali ke <strong>Direncanakan</strong>.</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('workouts.destroy', $workout->id) }}" class="flex items-center justify-end gap-2 pt-2">
                        @csrf
                        @method('DELETE')
                        <x-button type="button" variant="ghost" @click="deleteModalOpen = false">Batal</x-button>
                        <x-button type="submit" variant="danger">Ya, Hapus Sesi</x-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-shell>
