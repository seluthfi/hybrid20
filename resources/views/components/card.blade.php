@props([
    'tier' => '1',
    'padding' => 'md',
])

@php
    $tierClass = match((string)$tier) {
        '2' => 'glass-2',
        '3' => 'glass-3',
        default => 'glass-1',
    };

    $paddingClass = match($padding) {
        'none' => 'p-0',
        'sm' => 'p-3 sm:p-4',
        'lg' => 'p-6 sm:p-8',
        default => 'p-4 sm:p-6',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-card $tierClass $paddingClass relative overflow-hidden"]) }}>
    {{ $slot }}
</div>
