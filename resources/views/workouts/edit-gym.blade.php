<x-app-shell title="Ubah Sesi Gym">
    <div 
        x-data="gymForm({
            scheduledId: {{ $workout->scheduled_workout_id ?? 'null' }},
            initialTitle: '{{ addslashes($workout->title) }}',
            initialPerformedAt: '{{ $performedAtLocal->format('Y-m-d\TH:i') }}',
            initialDuration: '{{ \App\Support\PaceCalculator::formatDuration($workout->duration_seconds) }}',
            initialStatus: '{{ $workout->status }}',
            initialRpe: '{{ $workout->rpe ?? '' }}',
            initialBodyCondition: '{{ $workout->body_condition ?? 'normal' }}',
            initialPainFlag: {{ $workout->pain_flag ? 'true' : 'false' }},
            initialPainNotes: '{{ addslashes($workout->pain_notes ?? '') }}',
            initialGeneralNotes: '{{ addslashes($workout->general_notes ?? '') }}',
            initialExercises: {{ Js::from($workout->exercises->map(function($e) {
                $isTimed = $e->sets->contains(fn($s) => $s->duration_seconds > 0);
                return [
                    'exercise_id' => $e->exercise_id,
                    'exercise_name' => $e->exercise_name,
                    'is_timed' => $isTimed,
                    'target_text' => 'Tersimpan',
                    'last_set_text' => null,
                    'notes' => $e->notes ?? '',
                    'is_open' => true,
                    'sets' => $e->sets->map(fn($s, $idx) => [
                        'set_number' => $idx + 1,
                        'weight_kg' => (float) $s->weight_kg,
                        'reps' => (int) $s->reps,
                        'duration_seconds' => (int) $s->duration_seconds,
                        'rpe' => $s->rpe ?? '',
                        'checked' => true,
                    ]),
                ];
            })) }},
            allAvailableExercises: {{ Js::from($allExercises->map(fn($ex) => ['id' => $ex->id, 'name' => $ex->name, 'muscle_group' => $ex->muscle_group])) }}
        })"
        class="flex flex-col gap-6 max-w-5xl mx-auto pb-16"
    >
        <!-- Header Halaman & Tombol Kembali -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('workouts.show', $workout->id) }}" 
                   class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full glass-2 flex items-center justify-center text-on-surface hover:bg-surface-container-high transition-all"
                   aria-label="Kembali"
                >
                    <x-icon name="arrow_back" class="w-5 h-5" />
                </a>
                <div class="flex flex-col">
                    <span class="font-headline text-xs font-semibold text-primary uppercase tracking-wider">Mode Ubah</span>
                    <h1 class="font-headline text-2xl sm:text-3xl font-bold text-on-surface tracking-tight" x-text="title"></h1>
                </div>
            </div>

            <!-- Total Volume Indicator -->
            <div class="hidden sm:flex flex-col items-end px-4 py-2 rounded-field glass-2 border border-outline-variant/30">
                <span class="font-sans text-xs text-on-surface-variant font-medium">Estimasi Volume Sesi</span>
                <span class="font-metric text-lg font-bold text-primary tabular-nums" x-text="formatVolume() + ' kg'"></span>
            </div>
        </div>

        @if($errors->any())
            <x-banner variant="error" title="Gagal memperbarui latihan">
                <ul class="list-disc pl-4 text-xs font-sans">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </x-banner>
        @endif

        <!-- FORM UTAMA -->
        <form method="POST" action="{{ route('workouts.update', $workout->id) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="workout_type" value="gym">

            <!-- Card Parameter Sesi -->
            <x-card tier="1" padding="md" class="flex flex-col gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-field label="Nama Sesi Latihan" name="title" required>
                        <input type="text" name="title" x-model="title" class="w-full h-11 px-3.5 rounded-field glass-3 border border-outline-variant/50 font-sans text-sm focus:outline-primary" required />
                    </x-field>

                    <x-field label="Waktu Mulai" name="performed_at" required>
                        <input type="datetime-local" name="performed_at" x-model="performedAt" class="w-full h-11 px-3.5 rounded-field glass-3 border border-outline-variant/50 font-sans text-sm focus:outline-primary" required />
                    </x-field>

                    <x-field label="Durasi (Menit:Detik)" name="duration">
                        <input type="text" name="duration" x-model="duration" class="w-full h-11 px-3.5 rounded-field glass-3 border border-outline-variant/50 font-metric text-sm font-bold text-on-surface focus:outline-primary tabular-nums" />
                    </x-field>
                </div>
            </x-card>

            <!-- DAFTAR GERAKAN LATIHAN -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-headline text-lg font-bold text-on-surface">Daftar Gerakan Latihan</h2>
                    <x-button type="button" variant="ghost" size="sm" @click="showAddExerciseModal = true">
                        <x-icon name="add" class="w-4 h-4" />
                        <span>Tambah Latihan</span>
                    </x-button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <template x-for="(ex, exIdx) in exercises" :key="exIdx">
                        <div class="rounded-card glass-2 border border-outline-variant/30 overflow-hidden flex flex-col shadow-xs transition-all">
                            <!-- Accordion Header -->
                            <div class="p-4 flex items-center justify-between bg-surface-container-high/30 cursor-pointer select-none" @click="toggleAccordion(exIdx)">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary font-metric text-xs font-bold flex items-center justify-center shrink-0" x-text="exIdx + 1"></div>
                                    <h3 class="font-headline text-base font-bold text-on-surface truncate" x-text="ex.exercise_name"></h3>
                                </div>

                                <div class="flex items-center gap-1">
                                    <button type="button" @click.stop="removeExercise(exIdx)" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant hover:text-error transition-colors" aria-label="Hapus gerakan">
                                        <x-icon name="delete" class="w-4 h-4" />
                                    </button>
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant transition-transform" :class="ex.is_open ? 'rotate-180' : ''">
                                        <x-icon name="expand_more" class="w-5 h-5" />
                                    </div>
                                </div>
                            </div>

                            <!-- Accordion Body -->
                            <div x-show="ex.is_open" class="p-4 flex flex-col gap-3 border-t border-outline-variant/20">
                                <input type="hidden" :name="'exercises[' + exIdx + '][exercise_id]'" :value="ex.exercise_id">
                                <input type="hidden" :name="'exercises[' + exIdx + '][exercise_name]'" :value="ex.exercise_name">

                                <div class="flex flex-col gap-2.5">
                                    <template x-for="(set, sIdx) in ex.sets" :key="sIdx">
                                        <div class="flex flex-col gap-2 p-3 rounded-field glass-3 border border-outline-variant/30 shadow-2xs" :class="set.checked ? 'border-secondary/40' : ''">
                                            <input type="hidden" :name="'exercises[' + exIdx + '][sets][' + sIdx + '][set_number]'" :value="sIdx + 1">
                                            <input type="hidden" :name="'exercises[' + exIdx + '][sets][' + sIdx + '][checked]'" :value="set.checked ? '1' : ''">

                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-headline text-xs font-bold text-on-surface uppercase tracking-wider" x-text="'Set ' + (sIdx + 1)"></span>
                                                    <button type="button" x-show="ex.sets.length > 1" @click="removeSet(exIdx, sIdx)" class="text-[11px] font-sans text-error hover:underline" aria-label="Hapus set">Hapus</button>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <div class="flex items-center gap-1 bg-surface-container-high/60 px-2 py-1 rounded-pill">
                                                        <span class="font-headline text-[11px] font-semibold text-on-surface-variant">RPE</span>
                                                        <input 
                                                            type="text" 
                                                            inputmode="numeric" 
                                                            placeholder="-" 
                                                            :name="'exercises[' + exIdx + '][sets][' + sIdx + '][rpe]'"
                                                            x-model="set.rpe" 
                                                            class="w-7 text-center font-metric text-xs font-bold text-on-surface bg-transparent focus:outline-none tabular-nums" 
                                                        />
                                                    </div>

                                                    <label class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center cursor-pointer active:scale-95 transition-transform" aria-label="Tandai set selesai">
                                                        <input type="checkbox" x-model="set.checked" @change="onSetToggled()" class="sr-only peer" />
                                                        <div class="w-8 h-8 rounded-full border-2 border-outline/50 flex items-center justify-center transition-all peer-checked:bg-secondary peer-checked:border-secondary text-transparent peer-checked:text-white shadow-xs">
                                                            <x-icon name="check" class="w-4 h-4" />
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2 pt-1 border-t border-outline-variant/20">
                                                <!-- Stepper Beban -->
                                                <div class="flex items-center justify-between p-1 rounded-pill bg-surface-container-high/40 border border-outline-variant/30">
                                                    <button 
                                                        type="button" 
                                                        @click="set.weight_kg = Math.max(0, Math.round((parseFloat(set.weight_kg || 0) - 0.5) * 10) / 10)"
                                                        class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center bg-surface-container-lowest text-on-surface active:scale-90 shadow-2xs"
                                                        aria-label="Kurangi beban"
                                                    >
                                                        <x-icon name="remove" class="w-4 h-4" />
                                                    </button>
                                                    <div class="flex items-center gap-1">
                                                        <input 
                                                            type="text" 
                                                            inputmode="decimal" 
                                                            :name="'exercises[' + exIdx + '][sets][' + sIdx + '][weight_kg]'"
                                                            x-model="set.weight_kg"
                                                            class="w-12 text-center font-metric text-sm font-bold text-on-surface bg-transparent focus:outline-primary tabular-nums"
                                                        />
                                                        <span class="font-headline text-[10px] text-on-surface-variant font-semibold">KG</span>
                                                    </div>
                                                    <button 
                                                        type="button" 
                                                        @click="set.weight_kg = Math.round((parseFloat(set.weight_kg || 0) + 0.5) * 10) / 10"
                                                        class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center bg-surface-container-lowest text-on-surface active:scale-90 shadow-2xs"
                                                        aria-label="Tambah beban"
                                                    >
                                                        <x-icon name="add" class="w-4 h-4" />
                                                    </button>
                                                </div>

                                                <!-- Stepper Reps / Detik -->
                                                <div class="flex items-center justify-between p-1 rounded-pill bg-surface-container-high/40 border border-outline-variant/30">
                                                    <button 
                                                        type="button" 
                                                        @click="ex.is_timed ? set.duration_seconds = Math.max(0, parseInt(set.duration_seconds || 0) - 5) : set.reps = Math.max(0, parseInt(set.reps || 0) - 1)"
                                                        class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center bg-surface-container-lowest text-on-surface active:scale-90 shadow-2xs"
                                                        :aria-label="ex.is_timed ? 'Kurangi detik' : 'Kurangi repetisi'"
                                                    >
                                                        <x-icon name="remove" class="w-4 h-4" />
                                                    </button>
                                                    <div class="flex items-center gap-1">
                                                        <input 
                                                            type="text" 
                                                            inputmode="numeric" 
                                                            :name="ex.is_timed ? 'exercises[' + exIdx + '][sets][' + sIdx + '][duration_seconds]' : 'exercises[' + exIdx + '][sets][' + sIdx + '][reps]'"
                                                            x-model="ex.is_timed ? set.duration_seconds : set.reps"
                                                            class="w-12 text-center font-metric text-sm font-bold text-on-surface bg-transparent focus:outline-primary tabular-nums"
                                                        />
                                                        <span class="font-headline text-[10px] text-on-surface-variant font-semibold" x-text="ex.is_timed ? 'DTK' : 'REP'"></span>
                                                    </div>
                                                    <button 
                                                        type="button" 
                                                        @click="ex.is_timed ? set.duration_seconds = parseInt(set.duration_seconds || 0) + 5 : set.reps = parseInt(set.reps || 0) + 1"
                                                        class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center bg-surface-container-lowest text-on-surface active:scale-90 shadow-2xs"
                                                        :aria-label="ex.is_timed ? 'Tambah detik' : 'Tambah repetisi'"
                                                    >
                                                        <x-icon name="add" class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex items-center justify-between pt-2">
                                    <button type="button" @click="addSet(exIdx)" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-pill glass-2 text-xs font-headline font-semibold text-primary hover:bg-surface-container-high transition-all">
                                        <x-icon name="add" class="w-3.5 h-3.5" />
                                        <span>+ Set</span>
                                    </button>

                                    <button type="button" @click="copyPreviousSet(exIdx)" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-pill text-xs font-headline font-medium text-on-surface-variant hover:text-on-surface transition-all">
                                        <x-icon name="content_copy" class="w-3.5 h-3.5" />
                                        <span>Salin set sebelumnya</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Card Evaluasi Sesi -->
            <x-card tier="1" padding="md" class="flex flex-col gap-5">
                <h3 class="font-headline text-base font-bold text-on-surface">Evaluasi & Kondisi Sesi</h3>

                <!-- Status Sesi -->
                <div class="flex flex-col gap-2">
                    <label class="font-headline text-xs font-bold text-on-surface uppercase tracking-wider">Status Pencatatan</label>
                    <div class="grid grid-cols-2 gap-2 max-w-sm">
                        <button type="button" @click="status = 'completed'" :class="status === 'completed' ? 'bg-secondary text-white shadow-md font-bold' : 'glass-2 text-on-surface hover:bg-surface-container-high font-medium'" class="h-11 rounded-field text-sm font-headline flex items-center justify-center gap-2 transition-all">
                            <x-icon name="check_circle" class="w-4 h-4" />
                            <span>Selesai</span>
                        </button>
                        <button type="button" @click="status = 'partial'" :class="status === 'partial' ? 'bg-primary text-white shadow-md font-bold' : 'glass-2 text-on-surface hover:bg-surface-container-high font-medium'" class="h-11 rounded-field text-sm font-headline flex items-center justify-center gap-2 transition-all">
                            <x-icon name="timelapse" class="w-4 h-4" />
                            <span>Parsial</span>
                        </button>
                    </div>
                    <input type="hidden" name="status" :value="status">
                </div>

                <!-- RPE Sesi -->
                <div class="flex flex-col gap-2">
                    <label class="font-headline text-xs font-bold text-on-surface uppercase tracking-wider">RPE Sesi (1–10)</label>
                    <x-rpe-picker name="rpe" :selected="$workout->rpe" />
                </div>

                <!-- Kondisi Tubuh -->
                <div class="flex flex-col gap-2">
                    <label class="font-headline text-xs font-bold text-on-surface uppercase tracking-wider">Refleksi Kondisi Tubuh</label>
                    <div class="grid grid-cols-3 gap-2 max-w-md">
                        <label class="flex items-center justify-center gap-2 h-11 rounded-field glass-2 cursor-pointer border border-outline-variant/30 text-xs font-headline font-semibold text-on-surface transition-all has-[:checked]:bg-primary-fixed has-[:checked]:text-primary has-[:checked]:border-primary">
                            <input type="radio" name="body_condition" value="segar" {{ $workout->body_condition === 'segar' ? 'checked' : '' }} class="sr-only">
                            <span>Segar</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 h-11 rounded-field glass-2 cursor-pointer border border-outline-variant/30 text-xs font-headline font-semibold text-on-surface transition-all has-[:checked]:bg-primary-fixed has-[:checked]:text-primary has-[:checked]:border-primary">
                            <input type="radio" name="body_condition" value="normal" {{ ($workout->body_condition === 'normal' || !$workout->body_condition) ? 'checked' : '' }} class="sr-only">
                            <span>Normal</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 h-11 rounded-field glass-2 cursor-pointer border border-outline-variant/30 text-xs font-headline font-semibold text-on-surface transition-all has-[:checked]:bg-primary-fixed has-[:checked]:text-primary has-[:checked]:border-primary">
                            <input type="radio" name="body_condition" value="lelah" {{ $workout->body_condition === 'lelah' ? 'checked' : '' }} class="sr-only">
                            <span>Lelah</span>
                        </label>
                    </div>
                </div>

                <!-- Catatan Nyeri & Checkbox Nyeri -->
                <div class="flex flex-col gap-3 pt-2 border-t border-outline-variant/30">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="pain_flag" value="1" x-model="painFlag" class="w-5 h-5 mt-0.5 rounded border-outline/50 text-error focus:ring-error" />
                        <div class="flex flex-col">
                            <span class="font-headline text-sm font-bold text-on-surface">Ada nyeri yang memburuk atau mengubah cara bergerak</span>
                            <span class="font-sans text-xs text-on-surface-variant">Centang jika kamu mengalami ketidaknyamanan abnormal selama latihan ini.</span>
                        </div>
                    </label>

                    <div x-show="painFlag" x-cloak class="p-4 rounded-field glass-3 border border-outline-variant/50 flex items-start gap-3 bg-surface-container-high/40">
                        <x-icon name="info" class="w-5 h-5 text-on-surface-variant shrink-0 mt-0.5" />
                        <div class="flex flex-col gap-1">
                            <span class="font-headline text-xs font-bold uppercase tracking-wider text-on-surface">Perhatian</span>
                            <p class="font-sans text-xs text-on-surface-variant leading-relaxed">
                                Pertimbangkan menghentikan progresi dan berkonsultasi dengan tenaga kesehatan.
                            </p>
                        </div>
                    </div>

                    <div x-show="painFlag" x-cloak>
                        <x-field label="Catatan Nyeri (Opsional)" name="pain_notes">
                            <input type="text" name="pain_notes" value="{{ $workout->pain_notes }}" class="w-full h-11 px-3.5 rounded-field glass-3 border border-outline-variant/50 font-sans text-sm focus:outline-primary" />
                        </x-field>
                    </div>
                </div>

                <!-- Catatan Umum -->
                <x-field label="Catatan Sesi (Opsional)" name="general_notes">
                    <textarea name="general_notes" rows="2" class="w-full p-3 rounded-field glass-3 border border-outline-variant/50 font-sans text-sm focus:outline-primary">{{ $workout->general_notes }}</textarea>
                </x-field>
            </x-card>

            <!-- Actions -->
            <div class="flex items-center justify-between gap-4 pt-2">
                <a href="{{ route('workouts.show', $workout->id) }}" class="px-5 py-3 rounded-pill glass-2 font-headline text-sm font-semibold text-on-surface hover:bg-surface-container-high transition-all">
                    Batal
                </a>

                <x-button type="submit" variant="primary" size="lg" class="shadow-xl">
                    <x-icon name="save" class="w-5 h-5" />
                    <span>Perbarui Latihan</span>
                </x-button>
            </div>
        </form>

        <!-- MODAL TAMBAH LATIHAN -->
        <div x-show="showAddExerciseModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-on-background/25 backdrop-blur-md" @click="showAddExerciseModal = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="w-full max-w-md glass-3 rounded-card p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4 max-h-[85vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                        <h3 class="font-headline text-lg font-bold text-on-surface">Tambah Latihan</h3>
                        <button type="button" @click="showAddExerciseModal = false" class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface-variant hover:text-on-surface">
                            <x-icon name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="font-headline text-xs font-semibold text-on-surface">Ketik Nama Gerakan Baru</label>
                        <div class="flex gap-2">
                            <input type="text" x-model="customExerciseName" placeholder="Contoh: Barbell Row" class="flex-1 h-11 px-3.5 rounded-field glass-2 border border-outline-variant/50 text-sm font-sans focus:outline-primary" />
                            <x-button type="button" variant="primary" size="md" @click="addNewExercise(customExerciseName)">Tambah</x-button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 pt-2 border-t border-outline-variant/20">
                        <label class="font-headline text-xs font-semibold text-on-surface-variant">Atau Pilih dari Pustaka</label>
                        <input type="text" x-model="searchQuery" placeholder="Cari latihan..." class="w-full h-10 px-3 rounded-field glass-2 border border-outline-variant/40 text-xs font-sans mb-1" />
                        <div class="flex flex-col gap-1 max-h-48 overflow-y-auto pr-1">
                            <template x-for="item in filteredAvailableExercises()" :key="item.id">
                                <button type="button" @click="addNewExercise(item.name, item.id)" class="flex items-center justify-between p-2.5 rounded-field hover:bg-surface-container-high text-left text-xs font-headline font-semibold text-on-surface transition-colors">
                                    <span x-text="item.name"></span>
                                    <span class="text-[10px] text-on-surface-variant" x-text="item.muscle_group"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function gymForm(config) {
            return {
                scheduledId: config.scheduledId,
                title: config.initialTitle,
                performedAt: config.initialPerformedAt,
                duration: config.initialDuration,
                status: config.initialStatus,
                painFlag: config.initialPainFlag,
                exercises: config.initialExercises,
                allAvailableExercises: config.allAvailableExercises,
                showAddExerciseModal: false,
                customExerciseName: '',
                searchQuery: '',

                toggleAccordion(idx) {
                    if (window.innerWidth < 1024) {
                        const target = !this.exercises[idx].is_open;
                        this.exercises.forEach((e, i) => e.is_open = (i === idx ? target : false));
                    } else {
                        this.exercises[idx].is_open = !this.exercises[idx].is_open;
                    }
                },

                addSet(exIdx) {
                    const ex = this.exercises[exIdx];
                    const lastSet = ex.sets[ex.sets.length - 1];
                    ex.sets.push({
                        set_number: ex.sets.length + 1,
                        weight_kg: lastSet ? lastSet.weight_kg : 0,
                        reps: lastSet ? lastSet.reps : 10,
                        duration_seconds: lastSet ? lastSet.duration_seconds : 0,
                        rpe: '',
                        checked: true,
                    });
                },

                copyPreviousSet(exIdx) {
                    const ex = this.exercises[exIdx];
                    if (ex.sets.length === 0) return;
                    const lastSet = ex.sets[ex.sets.length - 1];
                    ex.sets.push({
                        set_number: ex.sets.length + 1,
                        weight_kg: lastSet.weight_kg,
                        reps: lastSet.reps,
                        duration_seconds: lastSet.duration_seconds,
                        rpe: lastSet.rpe,
                        checked: true,
                    });
                },

                removeSet(exIdx, sIdx) {
                    this.exercises[exIdx].sets.splice(sIdx, 1);
                    this.onSetToggled();
                },

                removeExercise(exIdx) {
                    if (confirm('Hapus gerakan latihan ini dari sesi?')) {
                        this.exercises.splice(exIdx, 1);
                        this.onSetToggled();
                    }
                },

                addNewExercise(name, id = null) {
                    if (!name || !name.trim()) return;
                    this.exercises.push({
                        exercise_id: id,
                        exercise_name: name.trim(),
                        is_timed: false,
                        target_text: 'Kustom',
                        last_set_text: null,
                        notes: '',
                        is_open: true,
                        sets: [
                            { set_number: 1, weight_kg: 0, reps: 10, duration_seconds: 0, rpe: '', checked: true },
                            { set_number: 2, weight_kg: 0, reps: 10, duration_seconds: 0, rpe: '', checked: true },
                            { set_number: 3, weight_kg: 0, reps: 10, duration_seconds: 0, rpe: '', checked: true }
                        ]
                    });
                    this.customExerciseName = '';
                    this.showAddExerciseModal = false;
                },

                filteredAvailableExercises() {
                    if (!this.searchQuery) return this.allAvailableExercises;
                    const q = this.searchQuery.toLowerCase();
                    return this.allAvailableExercises.filter(e => e.name.toLowerCase().includes(q));
                },

                onSetToggled() {
                    let totalSets = 0;
                    let checkedSets = 0;
                    this.exercises.forEach(e => {
                        e.sets.forEach(s => {
                            totalSets++;
                            if (s.checked) checkedSets++;
                        });
                    });

                    if (checkedSets === 0) {
                        this.status = 'partial';
                    } else if (checkedSets === totalSets) {
                        this.status = 'completed';
                    } else {
                        this.status = 'partial';
                    }
                },

                formatVolume() {
                    let total = 0;
                    this.exercises.forEach(e => {
                        e.sets.forEach(s => {
                            if (s.checked && !e.is_timed && parseFloat(s.weight_kg) > 0 && parseInt(s.reps) > 0) {
                                total += parseFloat(s.weight_kg) * parseInt(s.reps);
                            }
                        });
                    });
                    return new Intl.NumberFormat('id-ID').format(Math.round(total));
                }
            };
        }
    </script>
</x-app-shell>
