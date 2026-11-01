@props([
    'title',
    'subtitle' => null,
    'ariaLabel' => 'Grafik Analisis Data',
])

<div {{ $attributes->merge(['class' => 'rounded-card glass-1 p-4 sm:p-6 border border-outline-variant/30 flex flex-col gap-4 relative']) }}>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-outline-variant/20">
        <div class="flex flex-col">
            <h3 class="font-headline text-sm sm:text-base font-bold text-on-surface">
                {{ $title }}
            </h3>
            @if($subtitle)
                <span class="font-sans text-xs text-on-surface-variant">
                    {{ $subtitle }}
                </span>
            @endif
        </div>

        @if(isset($actions))
            <div class="flex items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>

    <!-- Chart Canvas Container -->
    <div class="relative w-full min-h-[220px]" aria-label="{{ $ariaLabel }}">
        {{ $slot }}
    </div>

    @if(isset($summaryTable))
        <div class="sr-only">
            {{ $summaryTable }}
        </div>
    @endif
</div>
