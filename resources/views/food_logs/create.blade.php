<x-app-shell :title="'Tambah ke Jurnal Makan'">
    <div class="flex flex-col gap-6 w-full max-w-2xl mx-auto pb-12" x-data="{
        searchQuery: '',
        activeFilter: '',
        foods: [],
        selectedFood: null,
        servings: 1,
        selectedMealType: '{{ $mealType }}',
        loggedOn: '{{ $date }}',
        loading: false,
        init() {
            this.fetchFoods();
        },
        fetchFoods() {
            this.loading = true;
            fetch('/foods/search?q=' + encodeURIComponent(this.searchQuery) + '&filter=' + encodeURIComponent(this.activeFilter))
                .then(res => res.json())
                .then(data => {
                    this.foods = data;
                    this.loading = false;
                })
                .catch(() => { this.loading = false; });
        },
        selectFood(food) {
            this.selectedFood = food;
            this.servings = 1;
        },
        get previewCalories() {
            if (!this.selectedFood) return 0;
            return Math.round(Number(this.selectedFood.calories_kcal) * Number(this.servings));
        },
        get previewProtein() {
            if (!this.selectedFood) return 0;
            return (Number(this.selectedFood.protein_g) * Number(this.servings)).toFixed(1);
        },
        get previewCarbs() {
            if (!this.selectedFood) return 0;
            return (Number(this.selectedFood.carbs_g) * Number(this.servings)).toFixed(1);
        },
        get previewFat() {
            if (!this.selectedFood) return 0;
            return (Number(this.selectedFood.fat_g) * Number(this.servings)).toFixed(1);
        },
        get previewWeight() {
            if (!this.selectedFood) return 0;
            return Math.round(Number(this.selectedFood.serving_size_g) * Number(this.servings));
        }
    }">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a href="{{ route('food-logs.index', ['date' => $date]) }}" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface hover:bg-surface-container active:scale-95 transition-all">
                    <x-icon name="arrow_back" class="w-5 h-5" />
                </a>
                <h1 class="font-headline text-2xl font-bold tracking-tight text-on-surface">Tambah Makanan</h1>
            </div>

            <a href="{{ route('foods.create') }}">
                <x-button variant="ghost" size="sm">
                    <x-icon name="add" class="w-4 h-4" />
                    <span>Buat Makanan Baru</span>
                </x-button>
            </a>
        </div>

        <!-- Search Bar & Filters (D17, PLAN.md §10.8) -->
        <x-card level="1" class="p-4 flex flex-col gap-3">
            <div class="relative flex items-center">
                <x-icon name="search" class="w-5 h-5 absolute left-3.5 text-outline pointer-events-none" />
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    @input.debounce.250ms="fetchFoods()" 
                    placeholder="Cari nama makanan... (misal: telur, dada ayam, nasi)" 
                    class="w-full pl-11 pr-4 py-3 rounded-full text-sm font-sans bg-surface-container-low border border-outline-variant/40 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition-all"
                >
            </div>

            <!-- Filter Chips (Favorit, Terakhir Dipakai) -->
            <div class="flex items-center gap-2 pt-1">
                <button 
                    type="button" 
                    @click="activeFilter = activeFilter === 'favorite' ? '' : 'favorite'; fetchFoods()" 
                    :class="activeFilter === 'favorite' ? 'bg-primary text-on-primary font-bold' : 'bg-surface-container-high text-on-surface-variant'" 
                    class="px-3 py-1.5 rounded-full text-xs font-headline flex items-center gap-1.5 transition-all"
                >
                    <x-icon name="star" class="w-3.5 h-3.5" />
                    <span>Favorit</span>
                </button>

                <button 
                    type="button" 
                    @click="activeFilter = activeFilter === 'recent' ? '' : 'recent'; fetchFoods()" 
                    :class="activeFilter === 'recent' ? 'bg-primary text-on-primary font-bold' : 'bg-surface-container-high text-on-surface-variant'" 
                    class="px-3 py-1.5 rounded-full text-xs font-headline flex items-center gap-1.5 transition-all"
                >
                    <x-icon name="history" class="w-3.5 h-3.5" />
                    <span>Terakhir Dipakai</span>
                </button>
            </div>
        </x-card>

        <!-- Search Results List -->
        <x-card level="1" class="p-4 max-h-72 overflow-y-auto">
            <template x-if="loading">
                <div class="p-6 text-center text-xs font-sans text-outline">Memuat daftar makanan...</div>
            </template>

            <div x-show="!loading" class="flex flex-col gap-2">
                <template x-for="item in foods" :key="item.id">
                    <div 
                        @click="selectFood(item)" 
                        :class="selectedFood?.id === item.id ? 'bg-primary-fixed/50 border-primary ring-2 ring-primary/30' : 'bg-surface-container-low/60 hover:bg-surface-container-low border-outline-variant/30'" 
                        class="p-3 rounded-xl border flex items-center justify-between gap-3 cursor-pointer transition-all"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0" :class="item.is_favorite ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface-variant'">
                                <x-icon name="restaurant" class="w-4 h-4" />
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="font-headline text-sm font-semibold text-on-surface truncate" x-text="item.name"></span>
                                <span class="font-sans text-xs text-on-surface-variant truncate" x-text="item.serving_name + ' (' + Number(item.serving_size_g).toFixed(0) + 'g) · ' + Number(item.calories_kcal).toFixed(0) + ' kcal'"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs font-sans text-outline tabular-nums" x-text="'P:' + Number(item.protein_g).toFixed(0) + ' K:' + Number(item.carbs_g).toFixed(0) + ' L:' + Number(item.fat_g).toFixed(0)"></span>
                            <div class="w-6 h-6 rounded-full flex items-center justify-center" :class="selectedFood?.id === item.id ? 'bg-primary text-on-primary' : 'text-outline'">
                                <x-icon name="check" class="w-4 h-4" />
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="foods.length === 0 && !loading">
                    <div class="p-6 text-center flex flex-col items-center justify-center gap-2">
                        <span class="text-sm font-sans text-on-surface-variant">Makanan tidak ditemukan.</span>
                        <a href="{{ route('foods.create') }}" class="text-xs font-headline font-semibold text-primary hover:underline">
                            + Buat makanan kustom baru
                        </a>
                    </div>
                </template>
            </div>
        </x-card>

        <!-- Form Konfirmasi Porsi & Catat (Muncul saat makanan dipilih) -->
        <template x-if="selectedFood">
            <x-card level="2" class="p-6">
                <form action="{{ route('food-logs.store') }}" method="POST" class="flex flex-col gap-5">
                    @csrf
                    <input type="hidden" name="food_id" :value="selectedFood?.id">

                    <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                        <div>
                            <span class="text-xs font-sans text-primary font-semibold uppercase tracking-wider">Makanan Terpilih</span>
                            <h2 class="font-headline text-lg font-bold text-on-surface" x-text="selectedFood?.name"></h2>
                        </div>
                        <div class="text-right">
                            <span class="font-headline text-2xl font-bold text-primary tabular-nums" x-text="previewCalories + ' kcal'"></span>
                            <span class="block text-xs font-sans text-outline tabular-nums" x-text="'P:' + previewProtein + 'g · K:' + previewCarbs + 'g · L:' + previewFat + 'g'"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Stepper Porsi -->
                        <div>
                            <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                                Jumlah Porsi
                            </label>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="if(servings > 0.5) servings = (Number(servings) - 0.5).toFixed(1)" class="w-10 h-10 rounded-xl bg-surface-container-high flex items-center justify-center text-on-surface active:scale-95 transition-all text-lg font-bold">
                                    -
                                </button>
                                <input type="number" step="0.1" min="0.1" max="50" name="servings" x-model="servings" required class="flex-1 text-center text-sm font-headline font-bold rounded-xl border border-outline-variant/60 bg-surface-container-lowest py-2 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                                <button type="button" @click="servings = (Number(servings) + 0.5).toFixed(1)" class="w-10 h-10 rounded-xl bg-surface-container-high flex items-center justify-center text-on-surface active:scale-95 transition-all text-lg font-bold">
                                    +
                                </button>
                            </div>
                            <span class="block text-[11px] font-sans text-outline mt-1 text-center" x-text="'Setara ' + previewWeight + ' gram'"></span>
                        </div>

                        <!-- Waktu Makan -->
                        <div>
                            <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                                Waktu Makan
                            </label>
                            <select name="meal_type" x-model="selectedMealType" class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                                <option value="breakfast">Sarapan</option>
                                <option value="lunch">Makan Siang</option>
                                <option value="dinner">Makan Malam</option>
                                <option value="snack">Camilan</option>
                            </select>
                        </div>

                        <!-- Tanggal Catat -->
                        <div>
                            <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">
                                Tanggal
                            </label>
                            <input type="date" name="logged_on" x-model="loggedOn" required class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-button type="submit" variant="primary" size="md">
                            <x-icon name="check" class="w-4 h-4" />
                            <span>Simpan ke Jurnal</span>
                        </x-button>
                    </div>
                </form>
            </x-card>
        </template>

    </div>
</x-app-shell>
