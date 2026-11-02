@props([
    'days' => [],
    'selectedDate' => null,
])

@php
    // Default fallback to 7 days if empty
    if (empty($days)) {
        $dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        $days = array_map(function($index, $name) {
            return [
                'day_name' => $name,
                'date_number' => $index + 1,
                'date' => '2026-11-0' . ($index + 2),
                'status' => 'planned',
                'is_active' => $index === 0,
            ];
        }, array_keys($dayNames), $dayNames);
    }
@endphp

<div {{ $attributes->merge(['id' => 'week-strip', 'data-scroll-keep' => true, 'class' => 'flex items-center justify-between gap-1 p-1.5 rounded-card glass-2 border border-outline-variant/30 shadow-xs w-full overflow-x-auto']) }}>
    @foreach($days as $day)
        @php
            $isActive = $day['is_active'] ?? ($selectedDate && ($day['date'] ?? '') === $selectedDate);
            $status = $day['status'] ?? 'planned';
            $dotColor = match($status) {
                'completed' => 'bg-secondary',
                'partial' => 'bg-primary-container',
                'skipped' => 'bg-outline',
                'rescheduled' => 'bg-tertiary',
                default => 'bg-surface-container-highest',
            };
        @endphp

        <button 
            type="button"
            @click="$dispatch('date-selected', '{{ $day['date'] ?? '' }}')"
            class="flex-1 min-w-[44px] min-h-[56px] py-1.5 px-1 rounded-pill flex flex-col items-center justify-between transition-[transform,colors] duration-150 ease-out active:scale-[0.98] select-none {{ $isActive ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface hover:bg-surface-container-high/60' }}"
            aria-label="{{ $day['day_name'] }}, tanggal {{ $day['date_number'] }}, status {{ $status }}"
        >
            <span class="font-headline text-xs font-semibold uppercase tracking-wider {{ $isActive ? 'text-on-primary' : 'text-on-surface-variant' }}">
                {{ $day['day_name'] }}
            </span>

            <span class="font-metric text-sm font-bold tabular-nums">
                {{ $day['date_number'] }}
            </span>

            <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-on-primary' : $dotColor }}"></span>
        </button>
    @endforeach
</div>
