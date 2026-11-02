@props([
    'href' => null,
    'title',
    'subtitle' => null,
    'icon' => null,
    'iconBg' => 'bg-primary-fixed text-primary',
    'badge' => null,
])

@php
    $tag = $href ? 'a' : 'div';
    $interactiveClass = $href ? 'hover:bg-surface-container-low/90 active:scale-[0.98] cursor-pointer' : '';
@endphp

<{{ $tag }} 
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => "flex items-center justify-between p-3.5 sm:p-4 rounded-field glass-2 border border-outline-variant/30 shadow-xs transition-[transform,colors] duration-150 ease-out select-none $interactiveClass"]) }}
>
    <div class="flex items-center gap-3.5 min-w-0">
        @if($icon)
            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 shadow-xs {{ $iconBg }}">
                <x-icon :name="$icon" class="w-5 h-5" />
            </div>
        @endif

        <div class="flex flex-col min-w-0">
            <span class="font-headline text-sm font-semibold text-on-surface truncate">
                {{ $title }}
            </span>
            @if($subtitle)
                <span class="font-sans text-xs text-on-surface-variant truncate">
                    {{ $subtitle }}
                </span>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-2 shrink-0 ml-3">
        @if($badge)
            {{ $badge }}
        @endif

        @if($href)
            <div class="w-8 h-8 rounded-full bg-surface-container-lowest/80 text-on-surface-variant flex items-center justify-center shadow-xs">
                <x-icon name="chevron_right" class="w-4 h-4" />
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
</{{ $tag }}>
