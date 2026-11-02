<x-app-shell :title="'Makanan Baru'">
    <div class="flex flex-col gap-6 w-full max-w-2xl mx-auto pb-12">

        <div class="flex items-center gap-2">
            <a href="{{ route('foods.index') }}" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface hover:bg-surface-container active:scale-[0.97] transition-[transform,colors] duration-150 ease-out">
                <x-icon name="arrow_back" class="w-5 h-5" />
            </a>
            <h1 class="font-headline text-2xl font-bold tracking-tight text-on-surface">Tambah Makanan Baru</h1>
        </div>

        <x-card level="1" class="p-6">
            <form action="{{ route('foods.store') }}" method="POST" class="flex flex-col gap-5">
                @csrf

                <!-- Informasi Dasar -->
                <div class="flex flex-col gap-4">
                    <h2 class="font-headline text-sm font-bold text-primary uppercase tracking-wider">Identitas Dasar</h2>

                    <div>
                        <label for="name" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                            Nama Makanan <span class="text-error">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="misal: Dada Ayam Panggang" required class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                        @error('name')
                            <span class="block text-xs text-error font-sans mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="serving_name" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                                Nama Takaran / Porsi
                            </label>
                            <input type="text" id="serving_name" name="serving_name" value="{{ old('serving_name', '1 porsi') }}" placeholder="misal: 1 porsi / 1 mangkuk" class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                        </div>

                        <div>
                            <label for="serving_size_g" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                                Berat Takaran (gram)
                            </label>
                            <input type="number" step="0.1" id="serving_size_g" name="serving_size_g" value="{{ old('serving_size_g', '100') }}" class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                        </div>
                    </div>
                </div>

                <!-- Nilai Gizi Per Porsi -->
                <div class="flex flex-col gap-4 pt-3 border-t border-outline-variant/20">
                    <h2 class="font-headline text-sm font-bold text-secondary uppercase tracking-wider">Nilai Gizi Per Porsi</h2>

                    <div>
                        <label for="calories_kcal" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                            Energi Kalori (kcal) <span class="text-error">*</span>
                        </label>
                        <input type="number" step="0.1" id="calories_kcal" name="calories_kcal" value="{{ old('calories_kcal') }}" placeholder="0" required class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                        @error('calories_kcal')
                            <span class="block text-xs text-error font-sans mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label for="protein_g" class="block font-headline text-xs font-semibold text-primary mb-1.5">
                                Protein (g)
                            </label>
                            <input type="number" step="0.1" id="protein_g" name="protein_g" value="{{ old('protein_g', '0') }}" class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                        </div>

                        <div>
                            <label for="carbs_g" class="block font-headline text-xs font-semibold text-secondary mb-1.5">
                                Karbohidrat (g)
                            </label>
                            <input type="number" step="0.1" id="carbs_g" name="carbs_g" value="{{ old('carbs_g', '0') }}" class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary/40">
                        </div>

                        <div>
                            <label for="fat_g" class="block font-headline text-xs font-semibold text-tertiary mb-1.5">
                                Lemak (g)
                            </label>
                            <input type="number" step="0.1" id="fat_g" name="fat_g" value="{{ old('fat_g', '0') }}" class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-tertiary/40">
                        </div>
                    </div>

                    <span class="text-[11px] font-sans text-outline">
                        Catatan: Nilai gizi adalah perkiraan per 1 satuan porsi yang kamu tentukan.
                    </span>
                </div>

                <!-- Opsi Tambahan -->
                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="is_favorite" name="is_favorite" value="1" {{ old('is_favorite') ? 'checked' : '' }} class="w-4 h-4 rounded text-primary focus:ring-primary/40">
                    <label for="is_favorite" class="text-xs font-sans text-on-surface select-none">
                        Tandai sebagai Makanan Favorit (muncul di prioritas pencarian)
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant/20">
                    <a href="{{ route('foods.index') }}">
                        <x-button type="button" variant="ghost" size="md">
                            Batal
                        </x-button>
                    </a>
                    <x-button type="submit" variant="primary" size="md">
                        <x-icon name="check" class="w-4 h-4" />
                        <span>Simpan Makanan</span>
                    </x-button>
                </div>
            </form>
        </x-card>

    </div>
</x-app-shell>
