@props([
    'options' => [],
    'name' => null,
    'selected' => null,
    'model' => null,
])

@php
    $keys = array_keys($options);
    $defaultSelected = $selected ?? ($keys[0] ?? '');
@endphp

<div 
    x-data="{ current: '{{ $defaultSelected }}' }"
    {{ $attributes->merge(['class' => 'inline-flex items-center p-1 rounded-pill glass-2 border border-outline-variant/40 shadow-sm w-full']) }}
>
    @foreach($options as $key => $label)
        <button 
            type="button"
            @click="current = '{{ $key }}'; {{ $model ? "$model = '$key';" : '' }}"
            :class="current === '{{ $key }}' ? 'bg-surface-container-lowest text-on-surface shadow-md font-semibold' : 'text-on-surface-variant hover:text-on-surface font-medium'"
            class="flex-1 min-h-[44px] px-3 py-2 rounded-pill font-headline text-xs text-center transition-[transform,colors] duration-150 ease-out active:scale-[0.98] select-none flex items-center justify-center"
        >
            {{ $label }}
        </button>
    @endforeach

    @if($name)
        <input type="hidden" name="{{ $name }}" :value="current">
    @endif
</div>
