@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'disabled' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-headline font-semibold rounded-pill transition-[transform,colors,opacity] duration-150 ease-out select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none active:scale-[0.98]';

    $sizeClasses = [
        'sm' => 'min-h-[44px] px-4 text-xs gap-1.5',
        'md' => 'min-h-[48px] px-6 text-sm gap-2',
        'lg' => 'min-h-[56px] px-8 text-base gap-2.5',
    ][$size] ?? 'min-h-[48px] px-6 text-sm gap-2';

    $variantClasses = [
        'primary' => 'bg-gradient-to-r from-primary to-primary-container text-on-primary shadow-lg shadow-primary/25 hover:shadow-primary/40 hover:opacity-95',
        'secondary' => 'bg-secondary text-on-secondary shadow-lg shadow-secondary/20 hover:bg-[#00574b] hover:shadow-secondary/35',
        'ghost' => 'bg-transparent text-on-surface hover:bg-surface-container-high/50',
        'danger' => 'bg-error text-on-error shadow-lg shadow-error/25 hover:bg-error/90',
        'outline' => 'border border-outline/30 bg-surface-container-lowest/70 text-on-surface hover:bg-surface-container-lowest shadow-sm',
    ][$variant] ?? 'bg-primary text-on-primary';
@endphp

<button 
    type="{{ $type }}" 
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $variantClasses"]) }}
>
    {{ $slot }}
</button>
