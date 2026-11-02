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
                        <span class="block font-sans text-[11px] text-outline mt-1">Target jangka panjang; tidak harus tercapai dalam 20 minggu.</span>
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
                        <span class="block font-sans text-[11px] text-outline mt-1">Total akumulasi termasuk langkah saat lari.</span>
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
                        <span class="font-sans text-[11px] text-outline">
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

        <!-- 4. PENCADANGAN & EKSPOR DATA (PLAN.md §10.11: Nonaktif hingga Fase 8) -->
        <x-card level="1" class="p-6 opacity-75">
            <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-outline-variant/40 text-on-surface-variant flex items-center justify-center">
                        <x-icon name="backup" class="w-4 h-4" />
                    </div>
                    <div class="flex flex-col">
                        <h2 class="font-headline text-lg font-bold text-on-surface">Pencadangan & Pemulihan Data</h2>
                        <span class="font-sans text-xs text-outline">Tersedia pada Fase 8</span>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full bg-surface-container-high font-headline text-xs text-on-surface-variant font-medium">
                    Fase 8
                </span>
            </div>

            <p class="font-sans text-sm text-on-surface-variant mt-3">
                Fitur ekspor cadangan (ZIP) dan pemulihan data mandiri akan diaktifkan secara lengkap pada Fase 8 sesuai spesifikasi arsitektur offline & backup.
            </p>

            <div class="flex items-center gap-3 mt-4">
                <button type="button" disabled class="px-4 py-2 rounded-pill font-headline text-xs font-semibold bg-surface-container text-outline cursor-not-allowed">
                    Ekspor Cadangan (ZIP)
                </button>
                <button type="button" disabled class="px-4 py-2 rounded-pill font-headline text-xs font-semibold bg-surface-container text-outline cursor-not-allowed">
                    Pulihkan Data Cadangan
                </button>
            </div>
        </x-card>

    </div>
</x-app-shell>
