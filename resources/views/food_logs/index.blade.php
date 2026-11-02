<x-app-shell :title="'Jurnal Makan'">
    <div class="flex flex-col gap-6 w-full max-w-4xl mx-auto pb-12" x-data="{
        editLogModalOpen: {{ $errors->any() ? 'true' : 'false' }},
        activeLog: null,
        editServings: 1,
        editMealType: 'breakfast',
        openEdit(log) {
            this.activeLog = log;
            this.editServings = log.servings;
            this.editMealType = log.meal_type;
            this.editLogModalOpen = true;
        }
    }">

        <!-- Header: Tanggal & Pintasan Pustaka Makanan -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('food-logs.index', ['date' => $date->copy()->subDay()->toDateString()]) }}" 
                   class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center glass-2 text-on-surface hover:bg-surface-container active:scale-[0.97] transition-[transform,colors] duration-150 ease-out" 
                   aria-label="Hari sebelumnya">
                    <x-icon name="chevron_left" class="w-5 h-5" />
                </a>

                <div class="flex items-center gap-2 px-4 py-2 rounded-full glass-2 border border-white/60">
                    <x-icon name="calendar_today" class="w-4 h-4 text-primary" />
                    <span class="font-headline text-sm font-bold text-on-surface">
                        {{ $date->isToday() ? 'Hari ini, ' : '' }}{{ $date->translatedFormat('l, d M Y') }}
                    </span>
                </div>

                <a href="{{ route('food-logs.index', ['date' => $date->copy()->addDay()->toDateString()]) }}" 
                   class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center glass-2 text-on-surface hover:bg-surface-container active:scale-[0.97] transition-[transform,colors] duration-150 ease-out" 
                   aria-label="Hari berikutnya">
                    <x-icon name="chevron_right" class="w-5 h-5" />
                </a>
            </div>

            <div class="flex items-center gap-2">
                <x-button :href="route('foods.index')" variant="ghost" size="sm">
                    <x-icon name="menu_book" class="w-4 h-4" />
                    <span>Pustaka Makanan</span>
                </x-button>

                <x-button :href="route('food-logs.create', ['date' => $dateString])" variant="primary" size="sm">
                    <x-icon name="add" class="w-4 h-4" />
                    <span>Catat Makanan</span>
                </x-button>
            </div>
        </div>

        <!-- Total Nutrition Summary Card (Glass-1) -->
        <x-card level="1" class="p-6 relative overflow-hidden">
            <div class="flex flex-col gap-5">
                <!-- Header Kalori -->
                <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-2">
                    <div>
                        <span class="font-headline text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            Total Energi Harian
                        </span>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="font-headline text-3xl sm:text-4xl font-bold tabular-nums text-primary">
                                {{ number_format($totalCalories, 0, ',', '.') }}
                            </span>
                            <span class="font-sans text-sm text-on-surface-variant">
                                / {{ number_format($targetCalories, 0, ',', '.') }} kcal
                            </span>
                        </div>
                    </div>

                    <div>
                        @if($isOverCalorie)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-pill bg-surface-container-high text-on-surface font-headline text-xs font-semibold">
                                melebihi target {{ number_format($calorieDiff, 0, ',', '.') }} kcal
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-pill bg-secondary-container text-on-secondary-container font-headline text-xs font-semibold">
                                sisa {{ number_format($calorieDiff, 0, ',', '.') }} kcal
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Progress Bar Kalori (Dibatasi 100%, netral jika melebihi) -->
                <div class="w-full flex flex-col gap-1.5">
                    <div class="w-full h-3 rounded-full bg-surface-container-high overflow-hidden">
                        <div class="h-full rounded-full bg-primary transition-[transform,colors] duration-150 ease-out duration-500" style="width: {{ $caloriePercent }}%"></div>
                    </div>
                    <div class="flex justify-between text-[11px] font-sans text-on-surface-variant">
                        <span>Pencapaian: {{ $caloriePercent }}%</span>
                        <span>Target: {{ number_format($targetCalories, 0, ',', '.') }} kcal</span>
                    </div>
                </div>

                <!-- 3 Kolom Makronutrisi -->
                <div class="grid grid-cols-3 gap-3 pt-2 border-t border-outline-variant/30">
                    <!-- Protein -->
                    <div class="p-3 rounded-xl bg-surface-container-low/70 flex flex-col gap-1.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-headline font-semibold text-primary">Protein</span>
                            <span class="font-headline font-bold text-on-surface tabular-nums">{{ $proteinPercent }}%</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-surface-container-high overflow-hidden">
                            <div class="h-full rounded-full bg-primary" style="width: {{ $proteinPercent }}%"></div>
                        </div>
                        <div class="text-[11px] font-sans text-on-surface-variant tabular-nums">
                            <strong>{{ number_format($totalProtein, 1, ',', '.') }}g</strong> / {{ $targetProtein }}g
                        </div>
                    </div>

                    <!-- Karbohidrat -->
                    <div class="p-3 rounded-xl bg-surface-container-low/70 flex flex-col gap-1.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-headline font-semibold text-secondary">Karbohidrat</span>
                            <span class="font-headline font-bold text-on-surface tabular-nums">{{ $carbsPercent }}%</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-surface-container-high overflow-hidden">
                            <div class="h-full rounded-full bg-secondary" style="width: {{ $carbsPercent }}%"></div>
                        </div>
                        <div class="text-[11px] font-sans text-on-surface-variant tabular-nums">
                            <strong>{{ number_format($totalCarbs, 1, ',', '.') }}g</strong> / {{ $targetCarbs }}g
                        </div>
                    </div>

                    <!-- Lemak -->
                    <div class="p-3 rounded-xl bg-surface-container-low/70 flex flex-col gap-1.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-headline font-semibold text-tertiary">Lemak</span>
                            <span class="font-headline font-bold text-on-surface tabular-nums">{{ $fatPercent }}%</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-surface-container-high overflow-hidden">
                            <div class="h-full rounded-full bg-tertiary" style="width: {{ $fatPercent }}%"></div>
                        </div>
                        <div class="text-[11px] font-sans text-on-surface-variant tabular-nums">
                            <strong>{{ number_format($totalFat, 1, ',', '.') }}g</strong> / {{ $targetFat }}g
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- 4 Kelompok Waktu Makan (Sarapan, Makan Siang, Makan Malam, Camilan) -->
        <div class="flex flex-col gap-4">
            @foreach($meals as $meal)
                <x-card level="1" class="p-4 sm:p-5 flex flex-col gap-3">
                    <!-- Meal Header: Judul + Subtotal Makro + Tombol Tambah -->
                    <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                        <div class="flex items-center gap-2">
                            <h2 class="font-headline text-base font-bold text-on-surface">{{ $meal['title'] }}</h2>
                            <span class="text-xs font-sans text-outline tabular-nums">
                                · {{ number_format($meal['calories'], 0, ',', '.') }} kcal
                            </span>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="hidden sm:flex items-center gap-2 text-xs font-sans text-on-surface-variant tabular-nums">
                                <span>P: {{ number_format($meal['protein'], 1, ',', '.') }}g</span>
                                <span>·</span>
                                <span>K: {{ number_format($meal['carbs'], 1, ',', '.') }}g</span>
                                <span>·</span>
                                <span>L: {{ number_format($meal['fat'], 1, ',', '.') }}g</span>
                            </div>

                            <a href="{{ route('food-logs.create', ['meal_type' => $meal['key'], 'date' => $dateString]) }}" 
                               class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center text-primary hover:bg-primary hover:text-on-primary transition-[transform,colors] duration-150 ease-out"
                               aria-label="Tambah {{ $meal['title'] }}">
                                <x-icon name="add" class="w-4 h-4" />
                            </a>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="flex flex-col gap-2">
                        @forelse($meal['items'] as $item)
                            <div @click="openEdit({{ json_encode($item) }})" 
                                 class="p-3 rounded-xl bg-surface-container-low/60 hover:bg-surface-container-low border border-outline-variant/30 flex items-center justify-between gap-3 cursor-pointer transition-[transform,colors] duration-150 ease-out active:scale-[0.99]">
                                <div class="flex flex-col min-w-0">
                                    <span class="font-headline text-sm font-semibold text-on-surface truncate">
                                        {{ $item->food_name_snapshot }}
                                    </span>
                                    <span class="font-sans text-xs text-on-surface-variant">
                                        {{ $item->servings }} porsi · {{ round((float)$item->servings * (float)$item->serving_size_g_snapshot) }} g
                                    </span>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    <div class="text-right">
                                        <span class="block font-headline text-sm font-bold text-on-surface tabular-nums">
                                            {{ number_format($item->calories_kcal, 0, ',', '.') }} kcal
                                        </span>
                                        <span class="block font-sans text-[11px] text-outline tabular-nums">
                                            P:{{ number_format($item->protein_g, 0) }} K:{{ number_format($item->carbs_g, 0) }} L:{{ number_format($item->fat_g, 0) }}
                                        </span>
                                    </div>
                                    <x-icon name="edit" class="w-4 h-4 text-outline" />
                                </div>
                            </div>
                        @empty
                            <div class="p-3 text-center text-xs font-sans text-outline">
                                Belum ada catatan {{ strtolower($meal['title']) }}.
                            </div>
                        @endforelse
                    </div>
                </x-card>
            @endforeach
        </div>

        <!-- MODAL UBAH / HAPUS CATATAN MAKANAN -->
        <div x-show="editLogModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-on-background/40" @click="editLogModalOpen = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="w-full max-w-md glass-3 rounded-card p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4">
                    <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                        <div>
                            <span class="text-xs font-sans text-outline">Ubah Catatan</span>
                            <h3 class="font-headline text-lg font-bold text-on-surface" x-text="activeLog?.food_name_snapshot"></h3>
                        </div>
                        <button type="button" @click="editLogModalOpen = false" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors">
                            <x-icon name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    <form :action="'/food-logs/' + (activeLog?.id || '')" method="POST" class="flex flex-col gap-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="edit-servings" class="block font-headline text-xs font-semibold text-on-surface mb-1.5">Jumlah Porsi</label>
                            <input id="edit-servings" type="text" inputmode="decimal" name="servings" x-model="editServings" required aria-describedby="servings-error" class="w-full text-sm font-sans rounded-field border @error('servings') border-error @else border-outline-variant/60 @enderror bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                            @error('servings')
                                <p id="servings-error" class="text-xs text-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">Waktu Makan</label>
                            <select name="meal_type" x-model="editMealType" class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                                <option value="breakfast">Sarapan</option>
                                <option value="lunch">Makan Siang</option>
                                <option value="dinner">Makan Malam</option>
                                <option value="snack">Camilan</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-outline-variant/20">
                            <button type="button" @click="$refs.deleteLogForm.submit()" class="text-xs font-headline font-semibold text-error hover:bg-error-container/40 px-3 py-2 rounded-pill transition-colors">
                                Hapus
                            </button>

                            <x-button type="submit" variant="primary" size="md">
                                Simpan Perubahan
                            </x-button>
                        </div>
                    </form>

                    <!-- Form Delete Tersembunyi -->
                    <form x-ref="deleteLogForm" :action="'/food-logs/' + (activeLog?.id || '')" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-shell>
