@props([
    'name',
    'ariaLabel',
    'variant' => 'glass',
    'size' => 'md',
    'type' => 'button',
])

@php
    $sizeClasses = [
        'sm' => 'w-11 h-11',
        'md' => 'w-12 h-12',
        'lg' => 'w-14 h-14',
    ][$size] ?? 'w-11 h-11';

    $iconSizes = [
        'sm' => 'w-5 h-5',
        'md' => 'w-6 h-6',
        'lg' => 'w-7 h-7',
    ][$size] ?? 'w-5 h-5';

    $variantClasses = [
        'glass' => 'glass-2 text-on-surface hover:bg-surface-container-highest/80 shadow-sm active:scale-95',
        'ghost' => 'bg-transparent text-on-surface hover:bg-surface-container-high/60 active:scale-95',
        'primary' => 'bg-primary text-on-primary shadow-md shadow-primary/30 hover:bg-primary-container active:scale-95',
    ][$variant] ?? 'glass-2 text-on-surface';
@endphp

<button 
    type="{{ $type }}" 
    aria-label="{{ $ariaLabel }}"
    {{ $attributes->merge(['class' => "rounded-full flex items-center justify-center transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary $sizeClasses $variantClasses"]) }}
>
    <x-icon :name="$name" :class="$iconSizes" />
</button>
