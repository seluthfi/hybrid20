@props([
    'recurring' => false,
    'sessionUrl' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-card glass-2 border border-outline-variant/40 p-4 shadow-sm flex items-start gap-3.5']) }} role="note">
    <div class="w-8 h-8 rounded-full bg-primary-fixed text-primary flex items-center justify-center shrink-0 shadow-2xs">
        <x-icon name="info" class="w-4 h-4" />
    </div>

    <div class="flex flex-col gap-1 min-w-0 flex-1">
        <p class="font-sans text-xs text-on-surface leading-relaxed">
            @if($recurring)
                Ada catatan nyeri yang berulang. Pertimbangkan menghentikan progresi dan berkonsultasi dengan tenaga kesehatan.
            @else
                Pertimbangkan menghentikan progresi dan berkonsultasi dengan tenaga kesehatan.
            @endif
        </p>

        @if($sessionUrl)
            <a href="{{ $sessionUrl }}" class="font-headline text-xs font-semibold text-primary hover:text-primary-container inline-flex items-center gap-1 pt-0.5">
                <span>Lihat catatan sesi</span>
                <x-icon name="chevron_right" class="w-3.5 h-3.5" />
            </a>
        @endif
    </div>
</div>
