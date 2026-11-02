@props([
    'class' => 'w-full h-4',
    'rounded' => 'rounded-pill',
])

<div 
    {{ $attributes->merge(['class' => "bg-surface-container-highest/60 $rounded $class skeleton-shimmer"]) }}
    aria-hidden="true"
></div>
