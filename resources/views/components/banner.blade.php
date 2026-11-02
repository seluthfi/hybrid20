@props([
    'type' => 'info',
    'title' => null,
    'actionText' => null,
    'actionClick' => null,
])

@php
    $config = match($type) {
        'error' => [
            'icon' => 'error',
            'bg' => 'bg-error-container/30 border-error/30 text-on-surface',
            'iconBg' => 'bg-error text-on-error',
        ],
        'offline' => [
            'icon' => 'sync_problem',
            'bg' => 'glass-2 border-outline-variant/40 text-on-surface',
            'iconBg' => 'bg-surface-container-highest text-on-surface-variant',
        ],
        'warning' => [
            'icon' => 'warning',
            'bg' => 'bg-tertiary-fixed/40 border-tertiary/30 text-on-surface',
            'iconBg' => 'bg-tertiary text-on-tertiary',
        ],
        default => [
            'icon' => 'info',
            'bg' => 'glass-1 border-outline-variant/40 text-on-surface',
            'iconBg' => 'bg-primary-fixed text-primary',
        ],
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-card p-4 border shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 ' . $config['bg']]) }}>
    <div class="flex items-start gap-3 min-w-0">
        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 shadow-2xs {{ $config['iconBg'] }}">
            <x-icon :name="$config['icon']" class="w-4 h-4" />
        </div>

        <div class="flex flex-col gap-0.5 min-w-0">
            @if($title)
                <span class="font-headline text-sm font-semibold text-on-surface">
                    {{ $title }}
                </span>
            @endif
            <p class="font-sans text-xs text-on-surface-variant leading-relaxed">
                {{ $slot }}
            </p>
        </div>
    </div>

    @if($actionText)
        <div class="shrink-0 self-end sm:self-center ml-11 sm:ml-0">
            <button 
                type="button" 
                @if($actionClick) @click="{{ $actionClick }}" @endif
                class="min-h-[44px] px-4 rounded-pill glass-3 text-xs font-headline font-semibold text-on-surface hover:bg-surface-container-lowest active:scale-95 transition-all shadow-xs"
            >
                {{ $actionText }}
            </button>
        </div>
    @endif
</div>
