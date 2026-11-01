@props([
    'name' => null,
    'selected' => null,
])

<div 
    x-data="{ rpe: {{ $selected ? (int)$selected : 'null' }} }"
    {{ $attributes->merge(['class' => 'flex flex-col gap-2 w-full']) }}
>
    <div class="grid grid-cols-5 gap-2 w-full">
        @for($i = 1; $i <= 10; $i++)
            <button 
                type="button"
                @click="rpe = {{ $i }}"
                :class="rpe === {{ $i }} ? 'bg-primary text-on-primary shadow-md font-bold' : 'glass-2 text-on-surface hover:bg-surface-container-high font-medium'"
                class="min-h-[44px] min-w-[44px] rounded-field font-metric text-sm flex items-center justify-center border border-outline-variant/30 transition-all select-none tabular-nums"
                aria-label="Pilih RPE {{ $i }}"
            >
                {{ $i }}
            </button>
        @endfor
    </div>

    @if($name)
        <input type="hidden" name="{{ $name }}" :value="rpe">
    @endif
</div>
