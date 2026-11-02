<x-app-shell :title="'Rencana Program'">
    <div x-data="{
        selectedWorkout: null,
        detailOpen: {{ ($errors->has('status') || $errors->has('rescheduled_to')) ? 'true' : 'false' }},
        repeatConfirmOpen: false,
        cancelRepeatConfirmOpen: false,
        skipConfirmOpen: false,
        statusVal: '{{ old('status', 'planned') }}',
        rescheduledToVal: '{{ old('rescheduled_to', '') }}',
        notesVal: '',
        submitting: false,
        workoutStatuses: {
            @if($hasProgram && isset($scheduledDays))
                @foreach($scheduledDays as $day)
                    @foreach($day['workouts'] as $w)
                        {{ $w->id }}: { status: @js($w->status), rescheduled_to: @js($w->rescheduled_to?->toDateString()) },
                    @endforeach
                @endforeach
            @endif
        },
        getStatusBadge(status) {
            const map = {
                completed: { text: 'Selesai', classes: 'bg-secondary-container text-on-secondary-container border-secondary/20' },
                partial: { text: 'Parsial', classes: 'bg-primary-fixed text-on-primary-fixed-variant border-primary/20' },
                skipped: { text: 'Terlewat', classes: 'bg-surface-container-highest text-on-surface-variant border-outline-variant/50' },
                rescheduled: { text: 'Ditunda', classes: 'bg-tertiary-fixed text-on-tertiary-fixed-variant border-tertiary/20' },
                planned: { text: 'Direncanakan', classes: 'bg-surface-container-high text-on-surface-variant border-outline-variant/40' },
            };
            return map[status] || map.planned;
        },
        openDetail(workout) {
            this.selectedWorkout = workout;
            this.statusVal = this.workoutStatuses[workout.id]?.status || workout.status;
            this.rescheduledToVal = this.workoutStatuses[workout.id]?.rescheduled_to || workout.rescheduled_to || '';
            this.notesVal = workout.instructions || '';
            this.detailOpen = true;
        },
        async saveStatus(e) {
            if (!this.selectedWorkout) return;
            this.submitting = true;
            try {
                const response = await fetch('/scheduled-workouts/' + this.selectedWorkout.id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        _method: 'PATCH',
                        status: this.statusVal,
                        rescheduled_to: this.statusVal === 'rescheduled' ? this.rescheduledToVal : null
                    })
                });
                if (response.ok) {
                    const data = await response.json();
                    const id = this.selectedWorkout.id;
                    if (this.workoutStatuses[id]) {
                        this.workoutStatuses[id].status = data.scheduled_workout.status;
                        this.workoutStatuses[id].rescheduled_to = data.scheduled_workout.rescheduled_to;
                    }
                    this.selectedWorkout.status = data.scheduled_workout.status;
                    this.selectedWorkout.rescheduled_to = data.scheduled_workout.rescheduled_to;
                    this.detailOpen = false;
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: 'Tersimpan' }
                    }));
                    const badgeEl = document.getElementById('workout-badge-' + id);
                    if (badgeEl) {
                        badgeEl.classList.remove('animate-badge-pop');
                        void badgeEl.offsetWidth;
                        badgeEl.classList.add('animate-badge-pop');
                    }
                } else {
                    e.target.submit();
                }
            } catch (err) {
                e.target.submit();
            } finally {
                this.submitting = false;
            }
        },
        async skipWorkout(e) {
            if (!this.selectedWorkout) return;
            this.submitting = true;
            try {
                const response = await fetch('/scheduled-workouts/' + this.selectedWorkout.id + '/skip', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        _token: '{{ csrf_token() }}'
                    })
                });
                if (response.ok) {
                    const id = this.selectedWorkout.id;
                    if (this.workoutStatuses[id]) {
                        this.workoutStatuses[id].status = 'skipped';
                    }
                    this.selectedWorkout.status = 'skipped';
                    this.skipConfirmOpen = false;
                    this.detailOpen = false;
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: 'Tersimpan' }
                    }));
                    const badgeEl = document.getElementById('workout-badge-' + id);
                    if (badgeEl) {
                        badgeEl.classList.remove('animate-badge-pop');
                        void badgeEl.offsetWidth;
                        badgeEl.classList.add('animate-badge-pop');
                    }
                } else {
                    e.target.submit();
                }
            } catch (err) {
                e.target.submit();
            } finally {
                this.submitting = false;
            }
        }
    }" class="flex flex-col gap-6 w-full max-w-[1360px] mx-auto pb-12">

        <!-- Flash Alert Messages -->
        @if(session('success'))
            <x-banner type="info" :message="session('success')" class="mb-2" />
        @endif
        @if($errors->any())
            <x-banner type="error" :message="$errors->first()" class="mb-2" />
        @endif

        @if(! $hasProgram)
            <!-- State: Belum Ada Program -->
            <x-card level="1" class="p-8 text-center flex flex-col items-center justify-center gap-4">
                <div class="w-16 h-16 rounded-full bg-primary-fixed flex items-center justify-center text-primary">
                    <x-icon name="event_note" class="w-8 h-8" />
                </div>
                <div class="flex flex-col gap-1 max-w-md">
                    <h2 class="font-headline text-xl font-bold text-on-surface">Pilih tanggal mulai (Senin)</h2>
                    <p class="font-sans text-sm text-on-surface-variant">
                        Kamu belum memiliki program aktif. Silakan atur tanggal mulai program di halaman Pengaturan untuk memulai jadwal latihan.
                    </p>
                </div>
                <x-button :href="route('settings.edit')" variant="primary" size="md">
                    <x-icon name="settings" class="w-4 h-4" />
                    <span>Buka Pengaturan</span>
                </x-button>
            </x-card>
        @else
            <!-- Header Program Status & Info -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-pill bg-primary/10 text-primary font-headline text-xs font-semibold">
                            Minggu {{ $currentWeek->week_number }} dari {{ $statusInfo['total_weeks'] }}
                        </span>

                        @if($currentWeek->is_repeat)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-pill bg-tertiary-fixed text-on-tertiary-fixed-variant font-headline text-xs font-bold">
                                Ulangan
                            </span>
                        @endif

                        @if($currentWeek->phase_label === 'deload')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-pill bg-secondary-container text-on-secondary-container font-headline text-xs font-semibold">
                                Minggu ringan
                            </span>
                        @elseif($currentWeek->phase_label === 'taper')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-pill bg-primary-fixed text-on-primary-fixed-variant font-headline text-xs font-semibold">
                                Taper
                            </span>
                        @endif
                    </div>

                    <h1 class="font-headline text-2xl lg:text-3xl font-bold tracking-tight text-on-surface">
                        Jadwal Latihan & Beban Kerja
                    </h1>

                    @if($statusInfo['status'] === 'upcoming')
                        <p class="font-sans text-sm font-medium text-primary">
                            Program dimulai {{ $statusInfo['days_until_start'] }} hari lagi ({{ \Carbon\Carbon::parse($program->start_date)->translatedFormat('d M Y') }}).
                        </p>
                    @elseif($statusInfo['status'] === 'completed')
                        <p class="font-sans text-sm font-medium text-secondary">
                            Program selesai.
                        </p>
                    @endif
                </div>

                <!-- Tombol Tindakan Minggu (Repeat / Cancel Repeat) -->
                <div class="flex items-center gap-2 flex-wrap">
                    @if($currentWeek->is_repeat && $summary['completed_sessions'] === 0 && $summary['partial_sessions'] === 0)
                        <x-button variant="danger" size="sm" @click="cancelRepeatConfirmOpen = true">
                            <x-icon name="delete" class="w-4 h-4" />
                            <span>Batalkan Ulangan</span>
                        </x-button>
                    @endif

                    <x-button variant="secondary" size="sm" @click="repeatConfirmOpen = true">
                        <x-icon name="replay" class="w-4 h-4" />
                        <span>Ulangi Minggu Ini</span>
                    </x-button>
                </div>
            </div>

            <!-- Horizontal Week Selector Strip (Weeks 1..N) -->
            <x-card level="1" class="p-3">
                <div class="flex items-center justify-between mb-2 px-1">
                    <span class="font-headline text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        Pilih Minggu
                    </span>
                    <span class="font-sans text-xs text-outline">
                        Geser horizontal untuk melihat jadwal
                    </span>
                </div>

                <div id="plan-week-selector" data-scroll-keep class="flex overflow-x-auto gap-2 pb-1 pt-0.5 no-scrollbar scroll-smooth">
                    @foreach($allWeeks as $w)
                        @php
                            $isSelected = $w->calendar_position === $currentWeek->calendar_position;
                            $isActiveNow = $w->calendar_position === $statusInfo['active_position'] && $statusInfo['status'] === 'active';
                        @endphp
                        <a href="{{ route('plan.show', ['calendar_position' => $w->calendar_position]) }}"
                           class="shrink-0 flex flex-col items-center justify-center min-w-[76px] py-2 px-3 rounded-2xl transition-[transform,colors] duration-150 ease-out text-center select-none {{ $isSelected ? 'bg-primary text-on-primary shadow-md shadow-primary/25 scale-[1.02]' : 'bg-surface-container-low hover:bg-surface-container text-on-surface' }}">
                            <span class="font-sans text-[11px] leading-tight {{ $isSelected ? 'text-on-primary/80' : 'text-outline' }}">
                                M{{ $w->week_number }}
                            </span>
                            <span class="font-headline text-lg font-bold tabular-nums leading-tight">
                                {{ sprintf('%02d', $w->calendar_position) }}
                            </span>
                            <div class="h-4 flex items-center justify-center mt-0.5">
                                @if($w->is_repeat)
                                    <span class="text-[9px] font-bold uppercase tracking-wider px-1 rounded {{ $isSelected ? 'bg-white/20 text-white' : 'bg-tertiary-fixed text-on-tertiary-fixed-variant' }}">Ulang</span>
                                @elseif($w->phase_label === 'deload')
                                    <span class="text-[9px] font-semibold px-1 rounded {{ $isSelected ? 'bg-white/20 text-white' : 'bg-secondary-container text-on-secondary-container' }}">Ringan</span>
                                @elseif($isActiveNow)
                                    <span class="text-[9px] font-bold px-1 rounded {{ $isSelected ? 'bg-white/20 text-white' : 'bg-primary-fixed text-primary' }}">Aktif</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </x-card>

            <!-- Ringkasan Target Minggu Ini & Catatan -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Metrik Target Minggu -->
                <x-card level="1" class="p-4 flex flex-col justify-between gap-3">
                    <div class="flex items-center justify-between">
                        <span class="font-headline text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Target Minggu Ini
                        </span>
                        <span class="font-sans text-xs text-outline">
                            Posisi Kalender #{{ $currentWeek->calendar_position }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="p-2.5 rounded-xl bg-surface-container-low">
                            <span class="block font-sans text-xs text-on-surface-variant">Lari</span>
                            <span class="font-headline text-lg font-bold text-secondary tabular-nums">
                                {{ number_format($summary['total_run_target_km'], 1, ',', '.') }}
                            </span>
                            <span class="block font-sans text-[11px] text-outline">km</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-surface-container-low">
                            <span class="block font-sans text-xs text-on-surface-variant">Gym</span>
                            <span class="font-headline text-lg font-bold text-primary tabular-nums">
                                {{ $summary['total_gym_sets'] }}
                            </span>
                            <span class="block font-sans text-[11px] text-outline">set</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-surface-container-low">
                            <span class="block font-sans text-xs text-on-surface-variant">Sesi</span>
                            <span class="font-headline text-lg font-bold text-on-surface tabular-nums">
                                {{ $summary['completed_sessions'] }}/{{ $summary['total_sessions'] }}
                            </span>
                            <span class="block font-sans text-[11px] text-outline">selesai</span>
                        </div>
                    </div>

                    <p class="font-sans text-xs text-on-surface-variant">
                        Minggu ini: {{ $summary['completed_sessions'] }} dari {{ $summary['total_sessions'] }} sesi selesai
                        @if($summary['partial_sessions'] > 0)
                            · {{ $summary['partial_sessions'] }} parsial
                        @endif
                    </p>
                </x-card>

                <!-- Input Catatan Minggu Ini -->
                <x-card level="1" class="lg:col-span-2 p-4 flex flex-col justify-between gap-2">
                    <form action="{{ route('program-weeks.update-notes', $currentWeek->id) }}" method="POST" class="flex flex-col gap-2 h-full justify-between">
                        @csrf
                        @method('PATCH')
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="week-notes" class="font-headline text-xs font-bold text-on-surface-variant uppercase tracking-wider flex items-center gap-1.5">
                                    <x-icon name="edit_note" class="w-4 h-4 text-primary" />
                                    <span>Catatan Minggu Ini</span>
                                </label>
                                <span class="font-sans text-[11px] text-outline">Tersimpan di program</span>
                            </div>
                            <textarea 
                                id="week-notes" 
                                name="notes" 
                                rows="2" 
                                placeholder="Tuliskan fokus, adaptasi, atau catatan pemulihan untuk minggu ini..." 
                                class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest/80 p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition-[transform,colors] duration-150 ease-out resize-none"
                            >{{ old('notes', $currentWeek->notes) }}</textarea>
                        </div>
                        <div class="flex justify-end">
                            <x-button type="submit" variant="ghost" size="sm">
                                <x-icon name="save" class="w-3.5 h-3.5" />
                                <span>Simpan Catatan</span>
                            </x-button>
                        </div>
                    </form>
                </x-card>
            </div>

            <!-- Grid 7 Hari Jadwal Latihan Mingguan -->
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between px-1">
                    <h2 class="font-headline text-lg font-bold text-on-surface tracking-tight">
                        Rincian Sesi Harian
                    </h2>
                    <span class="font-sans text-xs text-on-surface-variant">
                        8 sesi terjadwal per minggu
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-3">
                    @foreach($scheduledDays as $day)
                        <div class="flex flex-col gap-2 p-3 rounded-2xl border transition-[transform,colors] duration-150 ease-out animate-entrance {{ $day['is_today'] ? 'glass-2 border-primary ring-2 ring-primary/20 shadow-md' : 'glass-1 border-outline-variant/40' }}">
                            <!-- Day Header -->
                            <div class="flex items-center justify-between pb-1.5 border-b border-outline-variant/30">
                                <div>
                                    <span class="font-headline text-xs font-bold uppercase tracking-wider {{ $day['is_today'] ? 'text-primary' : 'text-on-surface-variant' }}">
                                        {{ $day['day_name'] }}
                                    </span>
                                    <span class="block font-sans text-[11px] text-outline">
                                        {{ $day['date_formatted'] }}
                                    </span>
                                </div>
                                @if($day['is_today'])
                                    <span class="px-2 py-0.5 rounded-full bg-primary text-on-primary font-headline text-[10px] font-bold">
                                        Hari Ini
                                    </span>
                                @endif
                            </div>

                            <!-- Workout Cards for this day -->
                            <div class="flex flex-col gap-2">
                                @forelse($day['workouts'] as $workout)
                                    @php
                                        $isGym = $workout->workout_type === 'gym';
                                        $isRun = $workout->workout_type === 'run';
                                        $isRest = $workout->workout_type === 'rest';
                                    @endphp

                                    <div 
                                        @if(! $isRest)
                                            @click="openDetail({{ json_encode($workout) }})"
                                            class="cursor-pointer p-2.5 rounded-xl flex flex-col gap-1.5 transition-[transform,colors] duration-150 ease-out active:scale-[0.98] border {{ $isGym ? 'bg-primary-fixed/30 border-primary/20 hover:border-primary/40' : ($isRun ? 'bg-secondary-container/30 border-secondary/20 hover:border-secondary/40' : 'bg-surface-container border-outline-variant/30') }}"
                                        @else
                                            class="p-2.5 rounded-xl flex flex-col gap-1.5 bg-surface-container/60 border border-outline-variant/30 select-none text-center"
                                        @endif
                                    >
                                        <div class="flex items-start justify-between gap-1">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <x-icon 
                                                    :name="$isGym ? 'fitness_center' : ($isRun ? 'directions_run' : 'bed')" 
                                                    class="w-4 h-4 shrink-0 {{ $isGym ? 'text-primary' : ($isRun ? 'text-secondary' : 'text-outline') }}" 
                                                />
                                                <span class="font-headline text-xs font-bold text-on-surface truncate">
                                                    {{ $workout->title }}
                                                </span>
                                            </div>

                                            @if(! $isRest)
                                                <div id="workout-badge-{{ $workout->id }}" class="shrink-0 transition-transform">
                                                    <span 
                                                        :class="'inline-flex items-center gap-1.5 px-3 py-1 rounded-pill font-headline text-xs font-semibold border shadow-xs select-none scale-90 origin-top-right ' + getStatusBadge(workoutStatuses[{{ $workout->id }}]?.status || '{{ $workout->status }}').classes"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-pill font-headline text-xs font-semibold border shadow-xs select-none scale-90 origin-top-right"
                                                    >
                                                        <span x-text="getStatusBadge(workoutStatuses[{{ $workout->id }}]?.status || '{{ $workout->status }}').text">
                                                            {{ $workout->status === 'completed' ? 'Selesai' : ($workout->status === 'partial' ? 'Parsial' : ($workout->status === 'skipped' ? 'Terlewat' : ($workout->status === 'rescheduled' ? 'Ditunda' : 'Direncanakan'))) }}
                                                        </span>
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        @if($isRun)
                                            <div class="flex items-center justify-between text-[11px] font-sans text-on-surface-variant">
                                                <span>Target: <strong class="font-headline text-on-surface">{{ number_format($workout->planned_distance_km, 1, ',', '.') }} km</strong></span>
                                                <span class="text-outline">{{ $workout->intensity_label === 'long_run' ? 'Long Run' : ($workout->intensity_label === 'taper_or_test' ? 'Percobaan 10K' : 'Easy') }}</span>
                                            </div>
                                        @elseif($isGym)
                                            <div class="flex items-center justify-between text-[11px] font-sans text-on-surface-variant">
                                                <span>{{ $workout->exercises->count() }} latihan</span>
                                                <span><strong class="font-headline text-on-surface">{{ $workout->exercises->sum('target_sets') }}</strong> set</span>
                                            </div>
                                        @else
                                            <p class="font-sans text-[11px] text-outline">Istirahat penuh</p>
                                        @endif

                                        <template x-if="workoutStatuses[{{ $workout->id }}]?.rescheduled_to">
                                            <div class="flex items-center gap-1 text-[10px] text-tertiary font-medium">
                                                <x-icon name="update" class="w-3 h-3" />
                                                <span>Dipindahkan ke <span x-text="workoutStatuses[{{ $workout->id }}]?.rescheduled_to"></span></span>
                                            </div>
                                        </template>
                                        @if($workout->rescheduled_to)
                                            <template x-if="!workoutStatuses[{{ $workout->id }}]?.rescheduled_to">
                                                <div class="flex items-center gap-1 text-[10px] text-tertiary font-medium">
                                                    <x-icon name="update" class="w-3 h-3" />
                                                    <span>Dipindahkan ke {{ \Carbon\Carbon::parse($workout->rescheduled_to)->translatedFormat('d M') }}</span>
                                                </div>
                                            </template>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-3 text-center text-xs text-outline font-sans">
                                        Tidak ada jadwal
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- SHEET / MODAL DETAIL SESI (5 STATUS + RESCHEDULED_TO) -->
        <div 
            x-show="detailOpen" 
            x-cloak 
            @keydown.escape.window="detailOpen = false" 
            class="fixed inset-0 z-50 overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <!-- Overlay Semi-Transparan Solid tanpa blur -->
            <div 
                x-show="detailOpen"
                x-cloak
                x-transition:enter="ease-out duration-250"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-out duration-180"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="detailOpen = false"
                class="fixed inset-0 bg-on-background/40"
            ></div>

            <!-- Sheet Content -->
            <div class="fixed inset-x-0 bottom-0 lg:inset-0 lg:flex lg:items-center lg:justify-center p-0 lg:p-4 pointer-events-none">
                <div 
                    x-show="detailOpen" 
                    x-cloak
                    x-transition:enter="ease-out duration-250"
                    x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                    x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
                    x-transition:leave="ease-out duration-180"
                    x-transition:leave-start="translate-y-0 lg:scale-100 lg:opacity-100"
                    x-transition:leave-end="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                    class="w-full lg:max-w-md glass-3 rounded-t-sheet lg:rounded-card p-5 lg:p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4 overflow-y-auto max-h-[90vh]"
                >
                    <!-- Drag Handle Mobile -->
                    <div class="flex lg:hidden items-center justify-center pt-1 pb-1">
                        <div class="w-12 h-1.5 rounded-full bg-outline-variant/60"></div>
                    </div>

                    <!-- Header Sesi -->
                    <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                        <div class="flex flex-col">
                            <span class="font-headline text-xs font-semibold text-primary uppercase" x-text="selectedWorkout?.workout_type === 'gym' ? 'Sesi Gym' : 'Sesi Lari'"></span>
                            <h3 class="font-headline text-lg font-bold text-on-surface" x-text="selectedWorkout?.title"></h3>
                        </div>
                        <button type="button" @click="detailOpen = false" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors active:scale-[0.97]">
                            <x-icon name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Instruksi Seed Latihan -->
                    <template x-if="selectedWorkout?.instructions">
                        <div class="p-3 rounded-xl bg-surface-container-low text-xs font-sans text-on-surface-variant leading-relaxed">
                            <div class="flex items-start gap-2">
                                <x-icon name="info" class="w-4 h-4 text-primary shrink-0 mt-0.5" />
                                <span x-text="selectedWorkout?.instructions"></span>
                            </div>
                        </div>
                    </template>

                    <!-- Form Pembaruan Status Sesi -->
                    <form 
                        :action="'/scheduled-workouts/' + (selectedWorkout?.id || '')" 
                        method="POST" 
                        @submit.prevent="saveStatus($event)"
                        class="flex flex-col gap-4"
                    >
                        @csrf
                        @method('PATCH')

                        <!-- 5 Pilihan Status -->
                        <div class="flex flex-col gap-2">
                            <label class="font-headline text-xs font-bold text-on-surface">Pilih Status Sesi</label>
                            <div class="grid grid-cols-5 gap-1.5 p-1 rounded-xl bg-surface-container-high/60">
                                <!-- planned -->
                                <button 
                                    type="button" 
                                    @click="statusVal = 'planned'" 
                                    :class="statusVal === 'planned' ? 'bg-surface-container-lowest shadow-sm text-on-surface font-bold' : 'text-on-surface-variant hover:text-on-surface'" 
                                    class="flex flex-col items-center justify-center py-2 px-1 rounded-lg text-xs transition-[transform,colors] duration-150 ease-out active:scale-[0.97]"
                                >
                                    <x-icon name="schedule" class="w-4 h-4 mb-0.5" />
                                    <span class="text-[10px] leading-tight">Rencana</span>
                                </button>

                                <!-- completed -->
                                <button 
                                    type="button" 
                                    @click="statusVal = 'completed'" 
                                    :class="statusVal === 'completed' ? 'bg-secondary-container text-on-secondary-container shadow-sm font-bold' : 'text-on-surface-variant hover:text-on-surface'" 
                                    class="flex flex-col items-center justify-center py-2 px-1 rounded-lg text-xs transition-[transform,colors] duration-150 ease-out active:scale-[0.97]"
                                >
                                    <x-icon name="check_circle" class="w-4 h-4 mb-0.5" />
                                    <span class="text-[10px] leading-tight">Selesai</span>
                                </button>

                                <!-- partial -->
                                <button 
                                    type="button" 
                                    @click="statusVal = 'partial'" 
                                    :class="statusVal === 'partial' ? 'bg-primary-fixed text-on-primary-fixed-variant shadow-sm font-bold' : 'text-on-surface-variant hover:text-on-surface'" 
                                    class="flex flex-col items-center justify-center py-2 px-1 rounded-lg text-xs transition-[transform,colors] duration-150 ease-out active:scale-[0.97]"
                                >
                                    <x-icon name="timelapse" class="w-4 h-4 mb-0.5" />
                                    <span class="text-[10px] leading-tight">Parsial</span>
                                </button>

                                <!-- skipped -->
                                <button 
                                    type="button" 
                                    @click="statusVal = 'skipped'" 
                                    :class="statusVal === 'skipped' ? 'bg-surface-container-highest text-on-surface font-bold shadow-sm' : 'text-on-surface-variant hover:text-on-surface'" 
                                    class="flex flex-col items-center justify-center py-2 px-1 rounded-lg text-xs transition-[transform,colors] duration-150 ease-out active:scale-[0.97]"
                                >
                                    <x-icon name="event_busy" class="w-4 h-4 mb-0.5" />
                                    <span class="text-[10px] leading-tight">Lewati</span>
                                </button>

                                <!-- rescheduled -->
                                <button 
                                    type="button" 
                                    @click="statusVal = 'rescheduled'" 
                                    :class="statusVal === 'rescheduled' ? 'bg-tertiary-fixed text-on-tertiary-fixed-variant shadow-sm font-bold' : 'text-on-surface-variant hover:text-on-surface'" 
                                    class="flex flex-col items-center justify-center py-2 px-1 rounded-lg text-xs transition-[transform,colors] duration-150 ease-out active:scale-[0.97]"
                                >
                                    <x-icon name="update" class="w-4 h-4 mb-0.5" />
                                    <span class="text-[10px] leading-tight">Ditunda</span>
                                </button>
                            </div>
                            <input type="hidden" name="status" :value="statusVal">
                            @error('status')
                                <p class="text-xs text-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Datepicker Rescheduled To (Hanya muncul jika Ditunda) -->
                        <div x-show="statusVal === 'rescheduled'" x-cloak class="flex flex-col gap-1.5 p-3 rounded-xl bg-tertiary-fixed/20 border border-tertiary/20">
                            <label for="rescheduled_to" class="font-headline text-xs font-bold text-tertiary">
                                Pindahkan ke Tanggal:
                            </label>
                            <input 
                                type="date" 
                                id="rescheduled_to" 
                                name="rescheduled_to" 
                                x-model="rescheduledToVal" 
                                aria-describedby="rescheduled_to_error"
                                class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-tertiary/40"
                            >
                            @error('rescheduled_to')
                                <p id="rescheduled_to_error" class="text-xs text-error mt-1">{{ $message }}</p>
                            @enderror
                            <span class="text-[11px] font-sans text-on-surface-variant">Sesi tetap dihitung dalam minggu aslinya.</span>
                        </div>

                        <!-- Tombol Aksi Simpan & Pintasan Lewati -->
                        <div class="flex items-center justify-between gap-3 pt-2">
                            <button 
                                type="button" 
                                @click="skipConfirmOpen = true" 
                                class="text-xs font-headline font-semibold text-outline hover:text-on-surface py-2.5 px-3 rounded-pill transition-colors min-h-[44px] active:scale-[0.97]"
                            >
                                Lewati Sesi
                            </button>

                            <x-button type="submit" variant="primary" size="md" ::disabled="submitting">
                                <x-icon name="check" class="w-4 h-4" />
                                <span>Simpan Status</span>
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- DIALOG KONFIRMASI: ULANGI MINGGU (PLAN.md §8.10) -->
        <div 
            x-show="repeatConfirmOpen" 
            x-cloak 
            @keydown.escape.window="repeatConfirmOpen = false" 
            class="fixed inset-0 z-50 overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-on-background/40" @click="repeatConfirmOpen = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="w-full max-w-md glass-3 rounded-card p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4 text-center sm:text-left">
                    <div class="flex flex-col gap-1.5">
                        <h3 class="font-headline text-lg font-bold text-on-surface">Ulangi Minggu Ini?</h3>
                        <p class="font-sans text-sm text-on-surface-variant leading-relaxed">
                            Sisa program bergeser 1 minggu. Catatan yang sudah ada tidak berubah.
                        </p>
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 pt-2">
                        <x-button variant="ghost" size="md" @click="repeatConfirmOpen = false">
                            Batal
                        </x-button>
                        <form action="{{ $currentWeek ? route('program-weeks.repeat', $currentWeek->id) : '#' }}" method="POST">
                            @csrf
                            <x-button type="submit" variant="secondary" size="md">
                                Ulangi Minggu Ini
                            </x-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- DIALOG KONFIRMASI: BATALKAN ULANGAN (PLAN.md §8.10) -->
        <div 
            x-show="cancelRepeatConfirmOpen" 
            x-cloak 
            @keydown.escape.window="cancelRepeatConfirmOpen = false" 
            class="fixed inset-0 z-50 overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-on-background/40" @click="cancelRepeatConfirmOpen = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="w-full max-w-md glass-3 rounded-card p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4 text-center sm:text-left">
                    <div class="flex flex-col gap-1.5">
                        <h3 class="font-headline text-lg font-bold text-error">Batalkan Ulangan Minggu Ini?</h3>
                        <p class="font-sans text-sm text-on-surface-variant leading-relaxed">
                            Minggu ulangan ini akan dihapus dan sisa program berikutnya akan bergeser maju 1 minggu.
                        </p>
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 pt-2">
                        <x-button variant="ghost" size="md" @click="cancelRepeatConfirmOpen = false">
                            Batal
                        </x-button>
                        <form action="{{ $currentWeek ? route('program-weeks.cancel-repeat', $currentWeek->id) : '#' }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" size="md">
                                Batalkan Ulangan
                            </x-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- DIALOG KONFIRMASI: LEWATI SESI -->
        <div 
            x-show="skipConfirmOpen" 
            x-cloak 
            @keydown.escape.window="skipConfirmOpen = false" 
            class="fixed inset-0 z-50 overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-on-background/40" @click="skipConfirmOpen = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="w-full max-w-md glass-3 rounded-card p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4 text-center sm:text-left">
                    <div class="flex flex-col gap-1.5">
                        <h3 class="font-headline text-lg font-bold text-on-surface">Lewati sesi latihan ini?</h3>
                        <p class="font-sans text-sm text-on-surface-variant leading-relaxed">
                            Sesi ini akan ditandai sebagai dilewati. Kamu tetap dapat membukanya kembali sewaktu-waktu.
                        </p>
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 pt-2">
                        <x-button variant="ghost" size="md" @click="skipConfirmOpen = false">
                            Batal
                        </x-button>
                        <form :action="'/scheduled-workouts/' + (selectedWorkout?.id || '') + '/skip'" method="POST" @submit.prevent="skipWorkout($event)">
                            @csrf
                            <x-button type="submit" variant="danger" size="md" ::disabled="submitting">
                                Lewati
                            </x-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-shell>
