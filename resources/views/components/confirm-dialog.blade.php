@props([
    'id' => 'confirm-dialog',
    'title' => 'Konfirmasi Aksi',
    'message' => 'Apakah kamu yakin ingin melanjutkan?',
    'confirmText' => 'Lanjutkan',
    'cancelText' => 'Batal',
    'danger' => false,
])

<div 
    x-data="{ open: false }"
    x-show="open"
    x-cloak
    @keydown.escape.window="open = false"
    id="{{ $id }}"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    aria-describedby="{{ $id }}-desc"
>
    <!-- Overlay -->
    <div 
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-on-background/30 backdrop-blur-sm"
        aria-hidden="true"
    ></div>

    <!-- Dialog -->
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div 
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="scale-95 opacity-0"
            x-transition:enter-end="scale-100 opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="scale-100 opacity-100"
            x-transition:leave-end="scale-95 opacity-0"
            class="w-full max-w-md glass-3 rounded-card p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4 text-center sm:text-left"
        >
            <div class="flex flex-col gap-1.5">
                <h3 id="{{ $id }}-title" class="font-headline text-lg font-bold text-on-surface">
                    {{ $title }}
                </h3>
                <p id="{{ $id }}-desc" class="font-sans text-sm text-on-surface-variant leading-relaxed">
                    {{ $message }}
                </p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 pt-2">
                <x-button variant="ghost" size="md" @click="open = false">
                    {{ $cancelText }}
                </x-button>

                <x-button 
                    :variant="$danger ? 'danger' : 'primary'" 
                    size="md"
                    {{ $attributes }}
                >
                    {{ $confirmText }}
                </x-button>
            </div>
        </div>
    </div>
</div>
