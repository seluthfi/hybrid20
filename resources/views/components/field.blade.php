@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
])

@php
    $id = $id ?? ($name ?? Str::random(8));
    $error = $error ?? ($name ? $errors->first($name) : null);
    $describedBy = [];
    if ($hint) $describedBy[] = "{$id}-hint";
    if ($error) $describedBy[] = "{$id}-error";
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-1.5 w-full']) }}>
    @if($label)
        <label for="{{ $id }}" class="font-headline text-xs font-semibold uppercase tracking-wider text-on-surface flex items-center justify-between">
            <span>{{ $label }}@if($required) <span class="text-error" aria-hidden="true">*</span>@endif</span>
        </label>
    @endif

    <div class="relative w-full">
        {{ $slot }}
    </div>

    @if($hint && !$error)
        <p id="{{ $id }}-hint" class="font-sans text-xs text-on-surface-variant leading-relaxed">
            {{ $hint }}
        </p>
    @endif

    @if($error)
        <p id="{{ $id }}-error" class="font-sans text-xs text-error font-medium flex items-center gap-1.5 leading-relaxed" role="alert">
            <x-icon name="error" class="w-3.5 h-3.5 shrink-0" />
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
