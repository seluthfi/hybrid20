<x-app-shell title="Ubah Sesi Lari">
    <div 
        x-data="runForm({
            scheduledId: {{ $workout->scheduled_workout_id ?? 'null' }},
            initialTitle: '{{ addslashes($workout->title) }}',
            initialPerformedAt: '{{ $performedAtLocal->format('Y-m-d\TH:i') }}',
            initialDistance: '{{ $workout->distance_km ? \App\Support\Num::format($workout->distance_km, 1) : '' }}',
            initialDuration: '{{ \App\Support\PaceCalculator::formatDuration($workout->duration_seconds) }}',
            initialSteps: '{{ $workout->steps ?? '' }}',
            initialStatus: '{{ $workout->status }}',
            initialRpe: '{{ $workout->rpe ?? '' }}',
            initialBodyCondition: '{{ $workout->body_condition ?? 'normal' }}',
            initialPainFlag: {{ $workout->pain_flag ? 'true' : 'false' }},
            initialPainNotes: '{{ addslashes($workout->pain_notes ?? '') }}',
            initialGeneralNotes: '{{ addslashes($workout->general_notes ?? '') }}',
        })"
        class="flex flex-col gap-6 max-w-2xl mx-auto pb-16"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('workouts.show', $workout->id) }}" 
                   class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full glass-2 flex items-center justify-center text-on-surface hover:bg-surface-container-high transition-[transform,colors] duration-150 ease-out"
                   aria-label="Kembali"
                >
                    <x-icon name="arrow_back" class="w-5 h-5" />
                </a>
                <div class="flex flex-col">
                    <span class="font-headline text-xs font-semibold text-secondary uppercase tracking-wider">Mode Ubah</span>
                    <h1 class="font-headline text-2xl sm:text-3xl font-bold text-on-surface tracking-tight" x-text="title"></h1>
                </div>
            </div>
        </div>

        @if($errors->any())
            <x-banner variant="error" title="Gagal memperbarui sesi lari">
                <ul class="list-disc pl-4 text-xs font-sans">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </x-banner>
        @endif

        <form method="POST" action="{{ route('workouts.update', $workout->id) }}" class="flex flex-col gap-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="workout_type" value="run">

            <x-card tier="1" padding="lg" class="flex flex-col gap-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-field label="Nama Sesi" name="title" required>
                        <input type="text" name="title" x-model="title" class="w-full h-11 px-3.5 rounded-field glass-3 border border-outline-variant/50 font-sans text-sm focus:outline-primary" required />
                    </x-field>

                    <x-field label="Waktu Mulai" name="performed_at" required>
                        <input type="datetime-local" name="performed_at" x-model="performedAt" class="w-full h-11 px-3.5 rounded-field glass-3 border border-outline-variant/50 font-sans text-sm focus:outline-primary" required />
                    </x-field>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-headline text-xs font-bold text-on-surface uppercase tracking-wider">Jarak (KM)</label>
                        <div class="flex items-center justify-between p-1 rounded-pill bg-surface-container-high/40 border border-outline-variant/40">
                            <button 
                                type="button" 
                                @click="distance = Math.max(0, Math.round((parseFloat(distance || 0) - 0.1) * 10) / 10)"
                                class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center bg-surface-container-lowest text-on-surface active:scale-[0.95] shadow-2xs"
                                aria-label="Kurangi jarak"
                            >
                                <x-icon name="remove" class="w-4 h-4" />
                            </button>
                            <input 
                                type="text" 
                                name="distance_km" 
                                inputmode="decimal" 
                                x-model="distance" 
                                class="w-20 text-center font-metric text-lg font-bold text-on-surface bg-transparent focus:outline-none tabular-nums" 
                            />
                            <button 
                                type="button" 
                                @click="distance = Math.round((parseFloat(distance || 0) + 0.1) * 10) / 10"
                                class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center bg-surface-container-lowest text-on-surface active:scale-[0.95] shadow-2xs"
                                aria-label="Tambah jarak"
                            >
                                <x-icon name="add" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-headline text-xs font-bold text-on-surface uppercase tracking-wider">Durasi (Menit:Detik)</label>
                        <div class="flex items-center h-[52px] px-3.5 rounded-pill bg-surface-container-high/40 border border-outline-variant/40">
                            <x-icon name="schedule" class="w-5 h-5 text-on-surface-variant mr-2 shrink-0" />
                            <input 
                                type="text" 
                                name="duration" 
                                x-model="duration" 
                                class="w-full bg-transparent font-metric text-base font-bold text-on-surface focus:outline-none tabular-nums" 
                            />
                        </div>
                    </div>
                </div>

                <div class="p-3.5 rounded-field glass-2 border border-secondary/30 flex items-center justify-between bg-secondary-fixed/15">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-secondary flex items-center justify-center text-white shrink-0 shadow-xs">
                            <x-icon name="speed" class="w-5 h-5" />
                        </div>
                        <div class="flex flex-col">
                            <span class="font-headline text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Kecepatan Rata-rata</span>
                            <span class="font-metric text-lg font-bold text-on-surface tabular-nums" x-text="computedPace"></span>
                        </div>
                    </div>
                    <span class="text-xs font-headline font-semibold text-secondary px-2.5 py-1 rounded-pill bg-surface-container-lowest/80">
                        Otomatis
                    </span>
                </div>

                <x-field label="Langkah Saat Lari (Opsional)" name="steps" hint="Tidak ditambahkan ke total langkah harian">
                    <input type="text" name="steps" inputmode="numeric" x-model="steps" class="w-full h-11 px-3.5 rounded-field glass-3 border border-outline-variant/50 font-metric text-sm font-bold text-on-surface focus:outline-primary tabular-nums" />
                </x-field>
            </x-card>

            <x-card tier="1" padding="lg" class="flex flex-col gap-5">
                <h3 class="font-headline text-base font-bold text-on-surface">Evaluasi & Kondisi</h3>

                <div class="flex flex-col gap-2">
                    <label class="font-headline text-xs font-bold text-on-surface uppercase tracking-wider">Status Sesi</label>
                    <div class="grid grid-cols-2 gap-2 max-w-sm">
                        <button type="button" @click="status = 'completed'" :class="status === 'completed' ? 'bg-secondary text-white shadow-md font-bold' : 'glass-2 text-on-surface hover:bg-surface-container-high font-medium'" class="h-11 rounded-field text-sm font-headline flex items-center justify-center gap-2 transition-[transform,colors] duration-150 ease-out">
                            <x-icon name="check_circle" class="w-4 h-4" />
                            <span>Selesai</span>
                        </button>
                        <button type="button" @click="status = 'partial'" :class="status === 'partial' ? 'bg-primary text-white shadow-md font-bold' : 'glass-2 text-on-surface hover:bg-surface-container-high font-medium'" class="h-11 rounded-field text-sm font-headline flex items-center justify-center gap-2 transition-[transform,colors] duration-150 ease-out">
                            <x-icon name="timelapse" class="w-4 h-4" />
                            <span>Parsial</span>
                        </button>
                    </div>
                    <input type="hidden" name="status" :value="status">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-headline text-xs font-bold text-on-surface uppercase tracking-wider">RPE Sesi (1–10)</label>
                    <x-rpe-picker name="rpe" :selected="$workout->rpe" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-headline text-xs font-bold text-on-surface uppercase tracking-wider">Refleksi Kondisi Tubuh</label>
                    <div class="grid grid-cols-3 gap-2 max-w-md">
                        <label class="flex items-center justify-center gap-2 h-11 rounded-field glass-2 cursor-pointer border border-outline-variant/30 text-xs font-headline font-semibold text-on-surface transition-[transform,colors] duration-150 ease-out has-[:checked]:bg-primary-fixed has-[:checked]:text-primary has-[:checked]:border-primary">
                            <input type="radio" name="body_condition" value="segar" {{ $workout->body_condition === 'segar' ? 'checked' : '' }} class="sr-only">
                            <span>Segar</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 h-11 rounded-field glass-2 cursor-pointer border border-outline-variant/30 text-xs font-headline font-semibold text-on-surface transition-[transform,colors] duration-150 ease-out has-[:checked]:bg-primary-fixed has-[:checked]:text-primary has-[:checked]:border-primary">
                            <input type="radio" name="body_condition" value="normal" {{ ($workout->body_condition === 'normal' || !$workout->body_condition) ? 'checked' : '' }} class="sr-only">
                            <span>Normal</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 h-11 rounded-field glass-2 cursor-pointer border border-outline-variant/30 text-xs font-headline font-semibold text-on-surface transition-[transform,colors] duration-150 ease-out has-[:checked]:bg-primary-fixed has-[:checked]:text-primary has-[:checked]:border-primary">
                            <input type="radio" name="body_condition" value="lelah" {{ $workout->body_condition === 'lelah' ? 'checked' : '' }} class="sr-only">
                            <span>Lelah</span>
                        </label>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-2 border-t border-outline-variant/30">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="pain_flag" value="1" x-model="painFlag" class="w-5 h-5 mt-0.5 rounded border-outline/50 text-error focus:ring-error" />
                        <div class="flex flex-col">
                            <span class="font-headline text-sm font-bold text-on-surface">Ada nyeri yang memburuk atau mengubah cara berlari</span>
                            <span class="font-sans text-xs text-on-surface-variant">Centang jika ada nyeri lutut, sendi, atau ketidaknyamanan abnormal saat berlari.</span>
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

                <x-field label="Catatan Sesi (Opsional)" name="general_notes">
                    <textarea name="general_notes" rows="2" class="w-full p-3 rounded-field glass-3 border border-outline-variant/50 font-sans text-sm focus:outline-primary">{{ $workout->general_notes }}</textarea>
                </x-field>
            </x-card>

            <div class="flex items-center justify-between gap-4 pt-2">
                <a href="{{ route('workouts.show', $workout->id) }}" class="px-5 py-3 rounded-pill glass-2 font-headline text-sm font-semibold text-on-surface hover:bg-surface-container-high transition-[transform,colors] duration-150 ease-out">
                    Batal
                </a>

                <x-button type="submit" variant="primary" size="lg" class="shadow-xl">
                    <x-icon name="save" class="w-5 h-5" />
                    <span>Perbarui Sesi Lari</span>
                </x-button>
            </div>
        </form>
    </div>

    <script>
        function runForm(config) {
            return {
                scheduledId: config.scheduledId,
                title: config.initialTitle,
                performedAt: config.initialPerformedAt,
                distance: config.initialDistance,
                duration: config.initialDuration,
                steps: config.initialSteps,
                status: config.initialStatus,
                painFlag: config.initialPainFlag,

                get computedPace() {
                    const dist = parseFloat(String(this.distance).replace(',', '.'));
                    if (!dist || dist <= 0) return '-';

                    const durStr = String(this.duration).trim();
                    if (!durStr) return '-';

                    let totalSeconds = 0;
                    const parts = durStr.split(':');
                    if (parts.length === 2) {
                        const m = parseInt(parts[0], 10);
                        const s = parseInt(parts[1], 10);
                        if (isNaN(m) || isNaN(s)) return '-';
                        totalSeconds = (m * 60) + s;
                    } else if (parts.length === 3) {
                        const h = parseInt(parts[0], 10);
                        const m = parseInt(parts[1], 10);
                        const s = parseInt(parts[2], 10);
                        if (isNaN(h) || isNaN(m) || isNaN(s)) return '-';
                        totalSeconds = (h * 3600) + (m * 60) + s;
                    } else if (parts.length === 1 && !isNaN(parseInt(parts[0], 10))) {
                        totalSeconds = parseInt(parts[0], 10) * 60;
                    } else {
                        return '-';
                    }

                    if (totalSeconds <= 0) return '-';

                    const paceSeconds = Math.round(totalSeconds / dist);
                    const pMin = Math.floor(paceSeconds / 60);
                    const pSec = paceSeconds % 60;
                    const pSecStr = pSec < 10 ? '0' + pSec : String(pSec);

                    return `${pMin}'${pSecStr}"/km`;
                }
            };
        }
    </script>
</x-app-shell>
