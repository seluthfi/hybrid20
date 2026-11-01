@props([
    'id' => 'sheet-modal',
    'title' => null,
])

<div 
    x-data="{ open: false }"
    x-show="open"
    @keydown.escape.window="open = false"
    id="{{ $id }}"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
    role="dialog"
    aria-modal="true"
    @if($title) aria-labelledby="{{ $id }}-title" @endif
>
    <!-- Frosted Backdrop Overlay -->
    <div 
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-on-background/25 backdrop-blur-md transition-opacity"
        aria-hidden="true"
    ></div>

    <!-- Sheet Wrapper: Bottom Sheet on Mobile, Centered Modal on Desktop -->
    <div class="fixed inset-x-0 bottom-0 sm:inset-0 sm:flex sm:items-center sm:justify-center p-0 sm:p-4 pointer-events-none">
        <div 
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
            x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
            x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
            class="w-full sm:max-w-lg glass-3 rounded-t-sheet sm:rounded-card p-5 sm:p-6 shadow-2xl border border-white/80 pointer-events-auto max-h-[90vh] flex flex-col gap-4 overflow-y-auto"
        >
            <!-- Drag Handle Mobile -->
            <div class="flex sm:hidden items-center justify-center pt-1 pb-2">
                <div class="w-12 h-1.5 rounded-full bg-outline-variant/60"></div>
            </div>

            <!-- Header -->
            <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                @if($title)
                    <h3 id="{{ $id }}-title" class="font-headline text-lg font-bold text-on-surface tracking-tight">
                        {{ $title }}
                    </h3>
                @else
                    <div></div>
                @endif

                <button 
                    type="button" 
                    @click="open = false"
                    class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high/60 active:scale-95 transition-all"
                    aria-label="Tutup dialog"
                >
                    <x-icon name="close" class="w-5 h-5" />
                </button>
            </div>

            <!-- Content -->
            <div class="flex flex-col gap-3 py-1">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
