<?php

namespace App\Support;

class Num
{
    /**
     * Parse input to integer.
     * Strips thousand separators (dots or commas) and non-digit characters (except optional leading minus).
     * Example: "2.420" -> 2420, "10,000" -> 10000.
     */
    public static function toInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }

        // Remove dots, spaces, commas
        $cleaned = preg_replace('/[^\d\-]/', '', $str);

        if ($cleaned === '' || $cleaned === '-') {
            return null;
        }

        return (int) $cleaned;
    }

    /**
     * Parse input to decimal / float.
     * Rule (§8.11): koma = desimal; bila tanpa koma, satu titik = desimal; "1.234,5" -> 1234.5.
     */
    public static function toDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }

        // Check if there is a comma
        if (str_contains($str, ',')) {
            // Remove dots as thousand separators
            $withoutDots = str_replace('.', '', $str);
            // Replace comma with dot for standard decimal notation
            $normalized = str_replace(',', '.', $withoutDots);
        } else {
            // No comma: if there are multiple dots, all but the last are thousand separators
            $dotCount = substr_count($str, '.');
            if ($dotCount > 1) {
                $lastDotPos = strrpos($str, '.');
                $before = str_replace('.', '', substr($str, 0, $lastDotPos));
                $after = substr($str, $lastDotPos + 1);
                $normalized = $before . '.' . $after;
            } else {
                $normalized = $str;
            }
        }

        // Strip any remaining unwanted characters except digit, dot, minus
        $cleaned = preg_replace('/[^\d\.\-]/', '', $normalized);

        if ($cleaned === '' || $cleaned === '-' || $cleaned === '.') {
            return null;
        }

        return (float) $cleaned;
    }

    /**
     * Format number with id-ID locale convention (dot for thousands, comma for decimals).
     */
    public static function format(mixed $value, int $decimals = 0): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $floatVal = (float) $value;

        // number_format: decimals, decimal_separator, thousands_separator
        $formatted = number_format($floatVal, $decimals, ',', '.');

        return $formatted;
    }

    /**
     * Format decimal for display, trimming trailing zeros if desired or keeping fixed decimals.
     * Example: 10.0 -> "10", 10.5 -> "10,5" (or keep 1 decimal for weight/distance: "74,0")
     */
    public static function formatDecimal(mixed $value, int $decimals = 1, bool $trimTrailingZeros = false): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $formatted = self::format($value, $decimals);

        if ($trimTrailingZeros && str_contains($formatted, ',')) {
            $formatted = rtrim(rtrim($formatted, '0'), ',');
        }

        return $formatted;
    }
}
