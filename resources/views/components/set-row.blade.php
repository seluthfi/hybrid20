@props([
    'setNumber' => 1,
    'weight' => 0,
    'reps' => 0,
    'rpe' => null,
    'completed' => false,
])

<div 
    x-data="{
        checked: {{ $completed ? 'true' : 'false' }},
        weight: {{ (float)$weight }},
        reps: {{ (int)$reps }},
        rpe: {{ $rpe !== null ? (int)$rpe : "''" }}
    }"
    {{ $attributes->merge(['class' => 'flex flex-col gap-2 p-3 sm:p-4 rounded-field glass-3 border border-outline-variant/40 shadow-xs transition-opacity duration-150 ease-out']) }}
    :class="checked ? 'opacity-85' : ''"
>
    <!-- Baris Atas: Nomor Set, RPE, Checkbox -->
    <div class="flex items-center justify-between gap-3">
        <span class="font-headline text-xs font-bold text-on-surface uppercase tracking-wider">
            Set {{ $setNumber }}
        </span>

        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1.5 bg-surface-container-high/60 px-2.5 py-1 rounded-pill">
                <span class="font-headline text-xs text-on-surface-variant font-semibold">RPE</span>
                <input 
                    type="text" 
                    inputmode="numeric" 
                    placeholder="-" 
                    x-model="rpe"
                    class="w-8 text-center font-metric text-xs font-bold text-on-surface bg-transparent focus:outline-none tabular-nums"
                />
            </div>

            <label class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center cursor-pointer active:scale-[0.97] transition-transform duration-150 ease-out" aria-label="Tandai set selesai">
                <input type="checkbox" x-model="checked" class="sr-only peer" />
                <div class="w-8 h-8 rounded-full border-2 border-outline/50 flex items-center justify-center transition-colors duration-150 ease-out peer-checked:bg-secondary peer-checked:border-secondary text-transparent peer-checked:text-white shadow-xs">
                    <x-icon name="check" class="w-4 h-4" />
                </div>
            </label>
        </div>
    </div>

    <!-- Baris Bawah: Stepper Beban & Repetisi -->
    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-outline-variant/30">
        <!-- Beban -->
        <div class="flex items-center justify-between p-1 rounded-pill bg-surface-container-high/40 border border-outline-variant/30">
            <button 
                type="button" 
                @click="weight = Math.max(0, Math.round((weight - 0.5) * 10) / 10)"
                class="w-9 h-9 min-w-[36px] min-h-[36px] rounded-full flex items-center justify-center bg-surface-container-lowest text-on-surface active:scale-[0.95] shadow-2xs"
                aria-label="Kurangi beban"
            >
                <x-icon name="remove" class="w-4 h-4" />
            </button>
            <div class="flex items-center gap-1">
                <input 
                    type="text" 
                    inputmode="decimal" 
                    x-model="weight"
                    class="w-12 text-center font-metric text-sm font-bold text-on-surface bg-transparent focus:outline-none tabular-nums"
                />
                <span class="font-headline text-[10px] text-on-surface-variant font-semibold">KG</span>
            </div>
            <button 
                type="button" 
                @click="weight = Math.round((weight + 0.5) * 10) / 10"
                class="w-9 h-9 min-w-[36px] min-h-[36px] rounded-full flex items-center justify-center bg-surface-container-lowest text-on-surface active:scale-[0.95] shadow-2xs"
                aria-label="Tambah beban"
            >
                <x-icon name="add" class="w-4 h-4" />
            </button>
        </div>

        <!-- Reps -->
        <div class="flex items-center justify-between p-1 rounded-pill bg-surface-container-high/40 border border-outline-variant/30">
            <button 
                type="button" 
                @click="reps = Math.max(0, reps - 1)"
                class="w-9 h-9 min-w-[36px] min-h-[36px] rounded-full flex items-center justify-center bg-surface-container-lowest text-on-surface active:scale-[0.95] shadow-2xs"
                aria-label="Kurangi repetisi"
            >
                <x-icon name="remove" class="w-4 h-4" />
            </button>
            <div class="flex items-center gap-1">
                <input 
                    type="text" 
                    inputmode="numeric" 
                    x-model="reps"
                    class="w-12 text-center font-metric text-sm font-bold text-on-surface bg-transparent focus:outline-none tabular-nums"
                />
                <span class="font-headline text-[10px] text-on-surface-variant font-semibold">REP</span>
            </div>
            <button 
                type="button" 
                @click="reps = reps + 1"
                class="w-9 h-9 min-w-[36px] min-h-[36px] rounded-full flex items-center justify-center bg-surface-container-lowest text-on-surface active:scale-[0.95] shadow-2xs"
                aria-label="Tambah repetisi"
            >
                <x-icon name="add" class="w-4 h-4" />
            </button>
        </div>
    </div>
</div>
