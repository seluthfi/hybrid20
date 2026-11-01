@props([
    'class' => 'w-full h-4',
    'rounded' => 'rounded-pill',
])

<div 
    {{ $attributes->merge(['class' => "bg-surface-container-highest/60 $rounded $class overflow-hidden relative motion-safe:animate-pulse"]) }}
    aria-hidden="true"
></div>
