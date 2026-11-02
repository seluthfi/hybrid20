@props([
    'value' => 0,
    'max' => 100,
    'color' => 'primary',
    'height' => 'h-2',
])

@php
    $percentage = $max > 0 ? ($value / $max) * 100 : 0;
    $isExceeded = $value > $max;
    $clampedPercentage = min(100, max(0, $percentage));

    $colorClasses = match($color) {
        'secondary' => 'bg-secondary',
        'tertiary' => 'bg-tertiary',
        default => 'bg-primary',
    };

    if ($isExceeded) {
        $colorClasses = 'bg-primary-container';
    }
@endphp

<div {{ $attributes->merge(['class' => "w-full bg-surface-container-highest/60 rounded-pill overflow-hidden $height p-0.5 border border-outline-variant/30"]) }}>
    <div 
        class="h-full w-full rounded-pill origin-left transition-transform duration-200 ease-out {{ $colorClasses }}"
        style="transform: scaleX({{ $clampedPercentage / 100 }});"
        role="progressbar"
        aria-valuenow="{{ $value }}"
        aria-valuemin="0"
        aria-valuemax="{{ $max }}"
    ></div>
</div>
