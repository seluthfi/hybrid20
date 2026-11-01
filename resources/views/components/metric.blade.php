@props([
    'value',
    'label',
    'unit' => null,
    'trend' => null,
    'size' => 'md',
])

@php
    $valueSize = match($size) {
        'sm' => 'text-xl sm:text-2xl',
        'lg' => 'text-3xl sm:text-4xl',
        default => 'text-2xl sm:text-3xl',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-0.5']) }}>
    <span class="font-headline text-xs font-semibold text-on-surface-variant uppercase tracking-wider">
        {{ $label }}
    </span>

    <div class="flex items-baseline gap-1.5">
        <span class="font-metric font-bold text-on-surface tabular-nums tracking-tight {{ $valueSize }}">
            {{ $value }}
        </span>

        @if($unit)
            <span class="font-headline text-xs font-semibold text-on-surface-variant uppercase">
                {{ $unit }}
            </span>
        @endif
    </div>

    @if($trend)
        <span class="font-sans text-xs text-on-surface-variant font-medium mt-0.5">
            {{ $trend }}
        </span>
    @endif
</div>
