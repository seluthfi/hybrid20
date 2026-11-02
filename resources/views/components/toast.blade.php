@props([
    'message' => 'Sesi tersimpan.',
    'description' => null,
    'icon' => 'check',
])

<div 
    x-data="{ show: false, timeout: null, messageText: @js($message) }"
    @show-toast.window="
        messageText = $event.detail.message || messageText;
        show = true;
        clearTimeout(timeout);
        timeout = setTimeout(() => show = false, 3500);
    "
    x-show="show"
    x-cloak
    x-transition:enter="ease-out duration-250"
    x-transition:enter-start="opacity-0 -translate-y-2 sm:translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="ease-out duration-180"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2 sm:translate-y-2"
    role="status"
    aria-live="polite"
    class="fixed top-20 sm:top-auto sm:bottom-24 inset-x-0 z-50 flex justify-center px-4 pointer-events-none"
>
    <div class="pointer-events-auto inline-flex items-center gap-3 px-5 py-3 rounded-pill glass-3 border border-white/80 shadow-xl shadow-on-surface/5">
        <div class="w-6 h-6 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container shrink-0">
            <x-icon :name="$icon" class="w-4 h-4" />
        </div>

        <div class="flex flex-col">
            <span class="font-headline text-sm font-semibold text-on-surface tracking-tight leading-tight" x-text="messageText">
                {{ $message }}
            </span>
            @if($description)
                <span class="font-sans text-xs text-on-surface-variant leading-tight">
                    {{ $description }}
                </span>
            @endif
        </div>
    </div>
</div>
