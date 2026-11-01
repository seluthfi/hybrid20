<?php

namespace App\Support;

class PaceCalculator
{
    /**
     * Calculate pace in seconds per kilometer.
     * Rule (§8.4): pace_seconds_per_km = round(duration_seconds / distance_km).
     * Returns null if distance is 0 / empty or duration is empty.
     */
    public static function calculatePaceSeconds(?int $durationSeconds, ?float $distanceKm): ?int
    {
        if ($durationSeconds === null || $durationSeconds <= 0 || $distanceKm === null || $distanceKm <= 0) {
            return null;
        }

        return (int) round($durationSeconds / $distanceKm);
    }

    /**
     * Format pace seconds per km to m'ss"/km string (e.g. 5'48"/km).
     * Returns "-" if null or <= 0.
     */
    public static function formatPace(?int $paceSeconds): string
    {
        if ($paceSeconds === null || $paceSeconds <= 0) {
            return '-';
        }

        $minutes = intdiv($paceSeconds, 60);
        $seconds = $paceSeconds % 60;

        return sprintf("%d'%02d\"/km", $minutes, $seconds);
    }

    /**
     * Parse duration string "MM:SS" or "HH:MM:SS" or "MM" into total seconds.
     * Minutes can be >= 60.
     */
    public static function parseDurationToSeconds(?string $durationStr): ?int
    {
        if ($durationStr === null || trim($durationStr) === '') {
            return null;
        }

        $trimmed = trim($durationStr);

        $parts = explode(':', $trimmed);
        if (count($parts) === 3) {
            // HH:MM:SS
            $hours = (int) $parts[0];
            $minutes = (int) $parts[1];
            $seconds = (int) $parts[2];
            return ($hours * 3600) + ($minutes * 60) + $seconds;
        }

        if (count($parts) === 2) {
            // MM:SS (minutes can be >= 60)
            $minutes = (int) $parts[0];
            $seconds = (int) $parts[1];
            return ($minutes * 60) + $seconds;
        }

        if (count($parts) === 1 && is_numeric($parts[0])) {
            // Just minutes
            return ((int) $parts[0]) * 60;
        }

        return null;
    }

    /**
     * Format duration seconds to "MM:SS" (or "H:MM:SS" if >= 1 hour).
     */
    public static function formatDuration(?int $durationSeconds): string
    {
        if ($durationSeconds === null || $durationSeconds <= 0) {
            return '-';
        }

        $hours = intdiv($durationSeconds, 3600);
        $remaining = $durationSeconds % 3600;
        $minutes = intdiv($remaining, 60);
        $seconds = $remaining % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
