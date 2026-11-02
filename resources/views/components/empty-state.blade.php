@props([
    'icon' => 'schedule',
    'title' => 'Belum ada data',
    'message' => null,
    'actionText' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center p-8 sm:p-12 rounded-card glass-1 border border-outline-variant/30 gap-3']) }}>
    <div class="w-14 h-14 rounded-full bg-surface-container-high/80 text-on-surface-variant flex items-center justify-center shadow-xs mb-1">
        <x-icon :name="$icon" class="w-7 h-7" />
    </div>

    <h3 class="font-headline text-base font-bold text-on-surface tracking-tight">
        {{ $title }}
    </h3>

    @if($message)
        <p class="font-sans text-xs text-on-surface-variant max-w-sm leading-relaxed">
            {{ $message }}
        </p>
    @endif

    @if($actionText)
        <div class="pt-2">
            @if($actionHref)
                <a href="{{ $actionHref }}">
                    <x-button variant="primary" size="sm">
                        {{ $actionText }}
                    </x-button>
                </a>
            @else
                {{ $slot }}
            @endif
        </div>
    @endif
</div>
