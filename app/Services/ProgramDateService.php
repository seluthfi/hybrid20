<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class ProgramDateService
{
    /**
     * Validate that the given date is a Monday (PLAN.md §8.1, D5).
     */
    public function validateStartDate(Carbon|string $startDate): Carbon
    {
        $date = $startDate instanceof Carbon ? $startDate->copy()->startOfDay() : Carbon::parse($startDate)->startOfDay();

        if (! $date->isMonday()) {
            throw new InvalidArgumentException('Tanggal mulai harus hari Senin.');
        }

        return $date;
    }

    /**
     * Calculate start date of a given week position (PLAN.md §8.1).
     * week_start(calendar_position) = start_date + 7 * (calendar_position - 1) days.
     */
    public function weekStartDate(Carbon|string $startDate, int $calendarPosition): Carbon
    {
        $start = $startDate instanceof Carbon ? $startDate->copy()->startOfDay() : Carbon::parse($startDate)->startOfDay();

        if ($calendarPosition < 1) {
            throw new InvalidArgumentException('Posisi kalender harus minimal 1.');
        }

        return $start->addDays(7 * ($calendarPosition - 1));
    }

    /**
     * Calculate date of a specific session/day in a week (PLAN.md §8.1).
     * session_date = week_start + (weekday - 1) days.
     * Weekday: 1 = Senin, 2 = Selasa, ..., 7 = Minggu.
     */
    public function sessionDate(Carbon|string $startDate, int $calendarPosition, int $weekday): Carbon
    {
        if ($weekday < 1 || $weekday > 7) {
            throw new InvalidArgumentException('Hari kerja (weekday) harus di antara 1 (Senin) dan 7 (Minggu).');
        }

        $weekStart = $this->weekStartDate($startDate, $calendarPosition);

        return $weekStart->addDays($weekday - 1);
    }

    /**
     * Calculate program active status and active position relative to today (PLAN.md §8.1).
     *
     * Posisi aktif = floor((hari_ini_lokal - start_date)/7) + 1.
     * Status:
     * - 'upcoming': hari ini < start_date ("Program dimulai N hari lagi")
     * - 'active': 1 <= posisi <= total_weeks
     * - 'completed': posisi > total_weeks ("Program selesai")
     *
     * @return array{status: string, active_position: int, days_until_start: int, total_weeks: int}
     */
    public function programStatus(Carbon|string $startDate, int $totalWeeks = 20, Carbon|string|null $today = null): array
    {
        $start = $startDate instanceof Carbon ? $startDate->copy()->startOfDay() : Carbon::parse($startDate)->startOfDay();
        $current = $today ? ($today instanceof Carbon ? $today->copy()->startOfDay() : Carbon::parse($today)->startOfDay()) : Carbon::today();

        if ($current->lt($start)) {
            $daysUntil = (int) $current->diffInDays($start);
            return [
                'status' => 'upcoming',
                'active_position' => 1,
                'days_until_start' => $daysUntil,
                'total_weeks' => $totalWeeks,
            ];
        }

        $daysDiff = (int) $start->diffInDays($current);
        $activePosition = (int) floor($daysDiff / 7) + 1;

        if ($activePosition > $totalWeeks) {
            return [
                'status' => 'completed',
                'active_position' => $totalWeeks,
                'days_until_start' => 0,
                'total_weeks' => $totalWeeks,
            ];
        }

        return [
            'status' => 'active',
            'active_position' => $activePosition,
            'days_until_start' => 0,
            'total_weeks' => $totalWeeks,
        ];
    }
}
