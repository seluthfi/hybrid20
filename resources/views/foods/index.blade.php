<x-app-shell :title="'Pustaka Makanan'">
    <div class="flex flex-col gap-6 w-full max-w-4xl mx-auto pb-12">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <a href="{{ route('food-logs.index') }}" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface hover:bg-surface-container active:scale-[0.97] transition-[transform,colors] duration-150 ease-out">
                        <x-icon name="arrow_back" class="w-5 h-5" />
                    </a>
                    <h1 class="font-headline text-2xl font-bold tracking-tight text-on-surface">Pustaka Makanan</h1>
                </div>
                <p class="font-sans text-xs text-on-surface-variant ml-11">
                    Kelola daftar makanan kustom dengan takaran porsi dan profil makronutrisi pribadimu.
                </p>
            </div>

            <x-button :href="route('foods.create')" variant="primary" size="md">
                <x-icon name="add" class="w-4 h-4" />
                <span>Makanan Baru</span>
            </x-button>
        </div>

        <!-- Daftar Makanan -->
        <x-card level="1" class="p-4 sm:p-6">
            <div class="flex flex-col gap-2">
                @forelse($foods as $food)
                    <div class="p-3.5 rounded-xl bg-surface-container-low/60 hover:bg-surface-container-low border border-outline-variant/30 flex items-center justify-between gap-3 transition-[transform,colors] duration-150 ease-out">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $food->is_favorite ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface-variant' }}">
                                <x-icon :name="$food->is_favorite ? 'star' : 'restaurant'" class="w-5 h-5" />
                            </div>

                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-headline text-sm font-bold text-on-surface truncate">
                                        {{ $food->name }}
                                    </span>
                                    @if($food->is_favorite)
                                        <span class="px-2 py-0.5 rounded-full bg-primary-fixed text-primary font-headline text-xs font-bold">
                                            Favorit
                                        </span>
                                    @endif
                                </div>
                                <span class="font-sans text-xs text-on-surface-variant">
                                    {{ $food->serving_name }} ({{ number_format($food->serving_size_g, 0) }} g) · {{ number_format($food->calories_kcal, 0) }} kcal
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <div class="hidden sm:flex flex-col items-end text-xs font-sans text-on-surface-variant tabular-nums">
                                <span>P: {{ number_format($food->protein_g, 1) }}g · K: {{ number_format($food->carbs_g, 1) }}g</span>
                                <span class="text-outline">L: {{ number_format($food->fat_g, 1) }}g</span>
                            </div>

                            <a href="{{ route('foods.edit', $food->id) }}" class="w-9 h-9 rounded-full flex items-center justify-center text-outline hover:text-on-surface hover:bg-surface-container-high transition-colors" aria-label="Edit makanan">
                                <x-icon name="edit" class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center flex flex-col items-center justify-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-surface-container-high text-outline flex items-center justify-center">
                            <x-icon name="restaurant_menu" class="w-6 h-6" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="font-headline text-base font-bold text-on-surface">Pustaka Makanan Kosong</span>
                            <span class="font-sans text-xs text-on-surface-variant">Tambahkan makanan yang sering kamu konsumsi untuk mempermudah pencatatan harian.</span>
                        </div>
                        <x-button :href="route('foods.create')" class="mt-2" variant="primary" size="sm">
                            <x-icon name="add" class="w-4 h-4" />
                            <span>Buat Makanan Pertama</span>
                        </x-button>
                    </div>
                @endforelse
            </div>

            @if($foods->hasPages())
                <div class="mt-4 pt-3 border-t border-outline-variant/30">
                    {{ $foods->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-app-shell>
