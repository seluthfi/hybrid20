@props([
    'status' => 'planned',
])

@php
    $config = match(strtolower($status)) {
        'completed' => [
            'text' => 'Selesai',
            'icon' => 'check_circle',
            'classes' => 'bg-secondary-container text-on-secondary-container border-secondary/20',
        ],
        'partial' => [
            'text' => 'Parsial',
            'icon' => 'timelapse',
            'classes' => 'bg-primary-fixed text-on-primary-fixed-variant border-primary/20',
        ],
        'skipped' => [
            'text' => 'Terlewat',
            'icon' => 'event_busy',
            'classes' => 'bg-surface-container-highest text-on-surface-variant border-outline-variant/50',
        ],
        'rescheduled' => [
            'text' => 'Ditunda',
            'icon' => 'update',
            'classes' => 'bg-tertiary-fixed text-on-tertiary-fixed-variant border-tertiary/20',
        ],
        default => [
            'text' => 'Direncanakan',
            'icon' => 'schedule',
            'classes' => 'bg-surface-container-high text-on-surface-variant border-outline-variant/40',
        ],
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-3 py-1 rounded-pill font-headline text-xs font-semibold border shadow-xs select-none ' . $config['classes']]) }}>
    <x-icon :name="$config['icon']" class="w-3.5 h-3.5 shrink-0" />
    <span>{{ $config['text'] }}</span>
</span>
