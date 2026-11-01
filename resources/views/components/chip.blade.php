@props([
    'active' => false,
    'dotColor' => null,
    'interactive' => false,
])

@php
    $baseClasses = 'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-pill font-headline text-xs font-semibold select-none transition-all duration-200 border';
    
    $stateClasses = $active
        ? 'bg-primary-fixed text-on-primary-fixed border-primary/30 shadow-sm'
        : 'glass-2 text-on-surface-variant hover:text-on-surface border-outline-variant/40 shadow-sm';

    $interactiveClasses = $interactive ? 'cursor-pointer active:scale-95 min-h-[44px]' : 'min-h-[32px]';
@endphp

@if($interactive)
    <button type="button" {{ $attributes->merge(['class' => "$baseClasses $stateClasses $interactiveClasses"]) }}>
        @if($dotColor)
            <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $dotColor }};"></span>
        @endif
        <span>{{ $slot }}</span>
    </button>
@else
    <span {{ $attributes->merge(['class' => "$baseClasses $stateClasses $interactiveClasses"]) }}>
        @if($dotColor)
            <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $dotColor }};"></span>
        @endif
        <span>{{ $slot }}</span>
    </span>
@endif
