<x-app-shell :title="'Pengaturan'">
    <div class="flex flex-col gap-6 w-full max-w-4xl mx-auto pb-12">

        <!-- Flash Alert Messages -->
        @if(session('success'))
            <x-banner type="info" :message="session('success')" class="mb-2" />
        @endif
        @if($errors->any())
            <x-banner type="error" :message="$errors->first()" class="mb-2" />
        @endif

        <div class="flex flex-col gap-1">
            <h1 class="font-headline text-2xl lg:text-3xl font-bold tracking-tight text-on-surface">
                Pengaturan Akun & Program
            </h1>
            <p class="font-sans text-sm text-on-surface-variant">
                Kelola target kebugaran harian, tanggal program, dan keamanan akun.
            </p>
        </div>

        <!-- 1. FORM TARGET FISIK & NUTRISI (PLAN.md §10.11) -->
        <x-card level="1" class="p-6">
            <form action="{{ route('settings.update') }}" method="POST" class="flex flex-col gap-6">
                @csrf
                @method('PUT')

                <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                            <x-icon name="straighten" class="w-4 h-4" />
                        </div>
                        <h2 class="font-headline text-lg font-bold text-on-surface">Target Fisik & Nutrisi</h2>
                    </div>
                    <span class="font-sans text-xs text-outline">Satuan: kg dan km</span>
                </div>

                <!-- Grid Profil Fisik -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="height_cm" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                            Tinggi Badan (cm)
                        </label>
                        <input 
                            type="text" 
                            inputmode="decimal"
                            id="height_cm" 
                            name="height_cm" 
                            value="{{ old('height_cm', $settings->height_cm) }}" 
                            class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40"
                        >
                    </div>

                    <div>
                        <label for="starting_weight_kg" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                            Berat Awal (kg)
                        </label>
                        <input 
                            type="text" 
                            inputmode="decimal"
                            id="starting_weight_kg" 
                            name="starting_weight_kg" 
                            value="{{ old('starting_weight_kg', $settings->starting_weight_kg) }}" 
                            class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40"
                        >
                    </div>

                    <div>
                        <label for="target_weight_kg" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                            Target Berat (kg)
                        </label>
                        <input 
                            type="text" 
                            inputmode="decimal"
                            id="target_weight_kg" 
                            name="target_weight_kg" 
                            value="{{ old('target_weight_kg', $settings->target_weight_kg) }}" 
                            class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40"
                        >
                        <span class="block font-sans text-xs text-outline mt-1">Target jangka panjang; tidak harus tercapai dalam 20 minggu.</span>
                    </div>
                </div>

                <!-- Grid Target Harian -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-outline-variant/20">
                    <div>
                        <label for="daily_steps_target" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                            Target Langkah Harian
                        </label>
                        <input 
                            type="text" 
                            inputmode="numeric"
                            id="daily_steps_target" 
                            name="daily_steps_target" 
                            value="{{ old('daily_steps_target', $settings->daily_steps_target) }}" 
                            class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40"
                        >
                        <span class="block font-sans text-xs text-outline mt-1">Total akumulasi termasuk langkah saat lari.</span>
                    </div>

                    <div>
                        <label for="daily_calorie_target" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                            Target Kalori Harian (kcal)
                        </label>
                        <input 
                            type="text" 
                            inputmode="numeric"
                            id="daily_calorie_target" 
                            name="daily_calorie_target" 
                            value="{{ old('daily_calorie_target', $settings->daily_calorie_target) }}" 
                            class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40"
                        >
                    </div>
                </div>

                <!-- Grid Makronutrisi -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="protein_target_g" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                            Target Protein (g)
                        </label>
                        <input 
                            type="text" 
                            inputmode="decimal"
                            id="protein_target_g" 
                            name="protein_target_g" 
                            value="{{ old('protein_target_g', $settings->protein_target_g) }}" 
                            class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40"
                        >
                    </div>

                    <div>
                        <label for="carbs_target_g" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                            Target Karbohidrat (g)
                        </label>
                        <input 
                            type="text" 
                            inputmode="decimal"
                            id="carbs_target_g" 
                            name="carbs_target_g" 
                            value="{{ old('carbs_target_g', $settings->carbs_target_g) }}" 
                            class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40"
                        >
                    </div>

                    <div>
                        <label for="fat_target_g" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                            Target Lemak (g)
                        </label>
                        <input 
                            type="text" 
                            inputmode="decimal"
                            id="fat_target_g" 
                            name="fat_target_g" 
                            value="{{ old('fat_target_g', $settings->fat_target_g) }}" 
                            class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40"
                        >
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <x-button type="submit" variant="primary" size="md">
                        <x-icon name="check" class="w-4 h-4" />
                        <span>Simpan Target</span>
                    </x-button>
                </div>
            </form>
        </x-card>

        <!-- 2. TANGGAL MULAI PROGRAM (Senin) -->
        @if($program)
            <x-card level="1" class="p-6">
                <form action="{{ route('settings.update-program') }}" method="POST" class="flex flex-col gap-4">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-secondary/10 text-secondary flex items-center justify-center">
                                <x-icon name="event" class="w-4 h-4" />
                            </div>
                            <h2 class="font-headline text-lg font-bold text-on-surface">Tanggal Mulai Program</h2>
                        </div>
                        <span class="font-sans text-xs text-secondary font-medium">Wajib Hari Senin</span>
                    </div>

                    <div class="max-w-md flex flex-col gap-1.5">
                        <label for="start_date" class="font-headline text-xs font-semibold text-on-surface">
                            Tanggal Mulai (Senin)
                        </label>
                        <input 
                            type="date" 
                            id="start_date" 
                            name="start_date" 
                            value="{{ old('start_date', $program->start_date ? \Carbon\Carbon::parse($program->start_date)->format('Y-m-d') : '') }}" 
                            class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary/40"
                            required
                        >
                        <span class="font-sans text-xs text-outline">
                            Semua jadwal sesi latihan 20 minggu dihitung mulai dari hari Senin ini.
                        </span>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-button type="submit" variant="secondary" size="md">
                            <x-icon name="event_repeat" class="w-4 h-4" />
                            <span>Ubah Tanggal Mulai</span>
                        </x-button>
                    </div>
                </form>
            </x-card>
        @endif

        <!-- 3. GANTI KATA SANDI -->
        <x-card level="1" class="p-6">
            <form action="{{ route('password.update') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                @method('PUT')

                <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-surface-container-high text-on-surface flex items-center justify-center">
                            <x-icon name="lock" class="w-4 h-4" />
                        </div>
                        <h2 class="font-headline text-lg font-bold text-on-surface">Ganti Kata Sandi</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="current_password" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                            Kata Sandi Saat Ini
                        </label>
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40"
                            required
                        >
                    </div>

                    <div>
                        <label for="password" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                            Kata Sandi Baru
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40"
                            required
                        >
                    </div>

                    <div>
                        <label for="password_confirmation" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40"
                            required
                        >
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <x-button type="submit" variant="ghost" size="md">
                        <x-icon name="vpn_key" class="w-4 h-4" />
                        <span>Perbarui Kata Sandi</span>
                    </x-button>
                </div>
            </form>
        </x-card>

        <!-- 4. PENCADANGAN & EKSPOR DATA (PLAN.md §8.13, §10.11, Fase 8) -->
        <x-card level="1" class="p-6" x-data="{
            restoreModalOpen: {{ $errors->has('backup_file') || $errors->has('confirm_replace') ? 'true' : 'false' }},
            previewLoading: false,
            previewData: null,
            previewError: null,
            confirmed: false,
            fileName: '',
            onFileChange(e) {
                const file = e.target.files[0];
                if (!file) {
                    this.fileName = '';
                    this.previewData = null;
                    this.previewError = null;
                    return;
                }
                this.fileName = file.name;
                this.previewLoading = true;
                this.previewError = null;
                this.previewData = null;

                const formData = new FormData();
                formData.append('backup_file', file);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route('backup.preview') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(({ status, body }) => {
                    this.previewLoading = false;
                    if (status >= 400) {
                        this.previewError = body.error || 'Berkas cadangan tidak valid atau rusak.';
                    } else {
                        this.previewData = body;
                    }
                })
                .catch(err => {
                    this.previewLoading = false;
                    this.previewError = 'Gagal memeriksa berkas cadangan: koneksi terputus.';
                });
            }
        }">
            <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                        <x-icon name="backup" class="w-4 h-4" />
                    </div>
                    <div class="flex flex-col">
                        <h2 class="font-headline text-lg font-bold text-on-surface">Pencadangan & Pemulihan Data</h2>
                        <span class="font-sans text-xs text-outline">Format arsip ZIP mandiri</span>
                    </div>
                </div>
            </div>

            <p class="font-sans text-sm text-on-surface-variant mt-3">
                Ekspor seluruh data latihan, jadwal program, jurnal nutrisi, riwayat berat badan, foto privat, dan pengaturan ke berkas ZIP mandiri, atau pulihkan dari cadangan sebelumnya.
            </p>

            <div class="flex flex-wrap items-center gap-3 mt-5">
                <x-button href="{{ route('backup.export') }}" variant="secondary" size="md">
                    <x-icon name="save" class="w-4 h-4" />
                    <span>Ekspor Cadangan (ZIP)</span>
                </x-button>

                <x-button type="button" variant="ghost" size="md" @click="restoreModalOpen = true">
                    <x-icon name="history" class="w-4 h-4" />
                    <span>Pulihkan Data Cadangan</span>
                </x-button>
            </div>

            <!-- MODAL PEMULIHAN CADANGAN -->
            <div 
                x-show="restoreModalOpen" 
                x-cloak 
                @keydown.escape.window="restoreModalOpen = false"
                class="fixed inset-0 z-50 overflow-y-auto"
                style="display: none;"
                role="dialog"
                aria-modal="true"
                aria-labelledby="restore-modal-title"
            >
                <!-- Backdrop -->
                <div 
                    x-show="restoreModalOpen"
                    x-cloak
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="restoreModalOpen = false"
                    class="fixed inset-0 bg-on-background/40 transition-opacity"
                    aria-hidden="true"
                ></div>

                <!-- Sheet Wrapper -->
                <div class="fixed inset-x-0 bottom-0 sm:inset-0 sm:flex sm:items-center sm:justify-center p-0 sm:p-4 pointer-events-none">
                    <div 
                        x-show="restoreModalOpen"
                        x-cloak
                        x-transition:enter="ease-out duration-250"
                        x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                        x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
                        x-transition:leave="ease-in duration-180"
                        x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
                        x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                        class="w-full sm:max-w-lg glass-3 rounded-t-sheet sm:rounded-card p-5 sm:p-6 shadow-2xl border border-white/80 pointer-events-auto max-h-[90vh] flex flex-col gap-4 overflow-y-auto"
                    >
                        <!-- Header Modal -->
                        <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                    <x-icon name="backup" class="w-4 h-4" />
                                </div>
                                <h3 id="restore-modal-title" class="font-headline text-lg font-bold text-on-surface">
                                    Pulihkan Data Cadangan
                                </h3>
                            </div>

                            <button 
                                type="button" 
                                @click="restoreModalOpen = false"
                                class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high/60 active:scale-[0.97] transition-[transform,colors] duration-150 ease-out"
                                aria-label="Tutup dialog"
                            >
                                <x-icon name="close" class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Form Import -->
                        <form action="{{ route('backup.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                            @csrf

                            @if($errors->has('backup_file'))
                                <div class="p-3 rounded-field bg-error-container text-error text-xs font-sans">
                                    {{ $errors->first('backup_file') }}
                                </div>
                            @endif

                            <div>
                                <label for="backup_file_input" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                                    Pilih Berkas ZIP Cadangan
                                </label>
                                <input 
                                    type="file" 
                                    id="backup_file_input" 
                                    name="backup_file" 
                                    accept=".zip"
                                    @change="onFileChange($event)"
                                    class="w-full text-xs font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface file:mr-3 file:py-1.5 file:px-3 file:rounded-pill file:border-0 file:text-xs file:font-semibold file:bg-surface-container-high file:text-on-surface hover:file:bg-surface-container-highest cursor-pointer"
                                    required
                                >
                                <span class="block font-sans text-xs text-outline mt-1">
                                    Hanya berkas .zip hasil ekspor resmi dari HYBRID 20.
                                </span>
                            </div>

                            <!-- Loading State -->
                            <div x-show="previewLoading" x-cloak class="flex items-center gap-3 p-3.5 rounded-field bg-surface-container-low text-on-surface">
                                <x-icon name="schedule" class="w-5 h-5 animate-spin text-primary" />
                                <span class="font-sans text-xs">Memverifikasi integritas dan manifest cadangan...</span>
                            </div>

                            <!-- Error Preview -->
                            <div x-show="previewError" x-cloak class="p-3.5 rounded-field bg-error-container text-error font-sans text-xs">
                                <p class="font-semibold mb-1">Pemeriksaan Berkas Gagal</p>
                                <p x-text="previewError"></p>
                            </div>

                            <!-- Ringkasan Pratinjau Manifest -->
                            <div x-show="previewData && !previewLoading" x-cloak class="flex flex-col gap-3 p-4 rounded-field bg-surface-container-low border border-outline-variant/40">
                                <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                                    <span class="font-headline text-xs font-bold text-on-surface">Rincian Cadangan</span>
                                    <span class="font-sans text-xs text-outline" x-text="'Versi ' + (previewData?.app_version || '1.0.0')"></span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs font-sans text-on-surface-variant">
                                    <div class="flex flex-col">
                                        <span class="text-xs text-outline">Tanggal Ekspor:</span>
                                        <span class="font-medium text-on-surface" x-text="previewData?.exported_at ? new Date(previewData.exported_at).toLocaleString('id-ID') : '-'"></span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs text-outline">Foto Privat:</span>
                                        <span class="font-medium text-on-surface" x-text="(previewData?.photo_count || 0) + ' berkas foto'"></span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs text-outline">Catatan Latihan:</span>
                                        <span class="font-medium text-on-surface" x-text="(previewData?.counts?.workouts || 0) + ' sesi · ' + (previewData?.counts?.exercise_sets || 0) + ' set'"></span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs text-outline">Catatan Nutrisi:</span>
                                        <span class="font-medium text-on-surface" x-text="(previewData?.counts?.food_logs || 0) + ' entri'"></span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs text-outline">Pengukuran Tubuh:</span>
                                        <span class="font-medium text-on-surface" x-text="(previewData?.counts?.body_measurements || 0) + ' catatan'"></span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs text-outline">Aktivitas Langkah:</span>
                                        <span class="font-medium text-on-surface" x-text="(previewData?.counts?.daily_activities || 0) + ' hari'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Peringatan Keras Penggantian Data (PLAN.md §8.13) -->
                            <div class="p-3.5 rounded-field bg-surface-container-high border border-outline-variant/40 text-on-surface flex flex-col gap-2">
                                <div class="flex items-center gap-2 text-on-surface font-headline text-xs font-bold">
                                    <x-icon name="info" class="w-4 h-4 shrink-0 text-primary" />
                                    <span>Peringatan Penggantian Data</span>
                                </div>
                                <p class="font-sans text-xs text-on-surface-variant">
                                    Semua data yang ada saat ini akan diganti sepenuhnya dengan data dari cadangan ini.
                                </p>
                                <a href="{{ route('backup.export') }}" class="font-sans text-xs text-primary font-semibold underline hover:text-primary-container">
                                    Unduh cadangan saat ini dulu
                                </a>
                            </div>

                            <!-- Checkbox Persetujuan Eksplisit (PLAN.md §8.13) -->
                            <div class="flex items-start gap-2.5 pt-1">
                                <input 
                                    type="checkbox" 
                                    id="confirm_replace" 
                                    name="confirm_replace" 
                                    value="1"
                                    x-model="confirmed"
                                    class="mt-1 w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/40 cursor-pointer"
                                    required
                                >
                                <label for="confirm_replace" class="font-sans text-xs text-on-surface cursor-pointer select-none">
                                    Saya mengerti bahwa data saat ini akan diganti.
                                </label>
                            </div>
                            @if($errors->has('confirm_replace'))
                                <span class="block font-sans text-xs text-error">
                                    {{ $errors->first('confirm_replace') }}
                                </span>
                            @endif

                            <!-- Tombol Aksi -->
                            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/30">
                                <x-button type="button" variant="ghost" size="md" @click="restoreModalOpen = false">
                                    <span>Batal</span>
                                </x-button>

                                <x-button type="submit" variant="danger" size="md" x-bind:disabled="!confirmed || previewLoading">
                                    <x-icon name="backup" class="w-4 h-4" />
                                    <span>Mulai Pulihkan Data</span>
                                </x-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </x-card>

    </div>
</x-app-shell>
