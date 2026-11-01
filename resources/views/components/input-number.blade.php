@props([
    'name' => null,
    'id' => null,
    'value' => 0,
    'step' => 1,
    'min' => null,
    'max' => null,
    'unit' => null,
    'decimal' => false,
])

@php
    $id = $id ?? ($name ?? Str::random(8));
    $inputmode = $decimal ? 'decimal' : 'numeric';
@endphp

<div 
    x-data="{
        val: {{ (float)$value }},
        step: {{ (float)$step }},
        min: {{ $min !== null ? (float)$min : 'null' }},
        max: {{ $max !== null ? (float)$max : 'null' }},
        decrement() {
            let next = this.val - this.step;
            if (this.min !== null && next < this.min) next = this.min;
            this.val = Math.round(next * 100) / 100;
        },
        increment() {
            let next = this.val + this.step;
            if (this.max !== null && next > this.max) next = this.max;
            this.val = Math.round(next * 100) / 100;
        }
    }"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-between p-1 rounded-card glass-3 w-full border border-outline-variant/40 shadow-sm']) }}
>
    <button 
        type="button" 
        @click="decrement()"
        class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center bg-surface-container-high/60 hover:bg-surface-container-high text-on-surface active:scale-90 transition-transform"
        aria-label="Kurangi nilai"
    >
        <x-icon name="remove" class="w-5 h-5" />
    </button>

    <div class="flex items-center justify-center gap-1.5 flex-1 px-2">
        <input 
            type="text" 
            inputmode="{{ $inputmode }}"
            id="{{ $id }}"
            @if($name) name="{{ $name }}" @endif
            x-model="val"
            class="w-full text-center font-metric text-lg font-bold text-on-surface bg-transparent focus:outline-none tabular-nums"
        />
        @if($unit)
            <span class="font-headline text-xs font-semibold text-on-surface-variant uppercase">{{ $unit }}</span>
        @endif
    </div>

    <button 
        type="button" 
        @click="increment()"
        class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center bg-surface-container-high/60 hover:bg-surface-container-high text-on-surface active:scale-90 transition-transform"
        aria-label="Tambah nilai"
    >
        <x-icon name="add" class="w-5 h-5" />
    </button>
</div>
