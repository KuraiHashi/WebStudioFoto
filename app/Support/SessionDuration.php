<?php

namespace App\Support;

class SessionDuration
{
    private const COMPONENT_PATTERN = '/(\d+(?:[.,]\d+)?)\s*(hari|days?|jam|j|hours?|hrs?|h|menit|minutes?|mins?|min|mnt|m|detik|dtk|seconds?|secs?|sec|s)\b/iu';

    private const DURATION_PATTERN = '/^\s*(?:\d+(?:[.,]\d+)?\s*(?:hari|days?|jam|j|hours?|hrs?|h|menit|minutes?|mins?|min|mnt|m|detik|dtk|seconds?|secs?|sec|s)\s*)+$/iu';

    public static function inSeconds(?string $duration, int $default = 7200): int
    {
        return self::parse($duration) ?? $default;
    }

    public static function isValid(?string $duration): bool
    {
        return self::parse($duration) !== null;
    }

    private static function parse(?string $duration): ?int
    {
        if ($duration === null || trim($duration) === '') {
            return null;
        }

        $duration = trim($duration);

        if (preg_match('/^sehari$/iu', $duration) === 1) {
            return 86400;
        }

        if (preg_match('/^\d+(?:[.,]\d+)?$/u', $duration) === 1) {
            $seconds = (float) str_replace(',', '.', $duration) * 3600;

            return $seconds > 0 && $seconds <= PHP_INT_MAX ? (int) round($seconds) : null;
        }

        if (preg_match(self::DURATION_PATTERN, $duration) !== 1) {
            return null;
        }

        preg_match_all(self::COMPONENT_PATTERN, $duration, $components, PREG_SET_ORDER);

        $seconds = 0.0;

        foreach ($components as $component) {
            $amount = (float) str_replace(',', '.', $component[1]);
            $unit = mb_strtolower($component[2]);
            $multiplier = match (true) {
                in_array($unit, ['hari', 'day', 'days'], true) => 86400,
                in_array($unit, ['jam', 'j', 'hour', 'hours', 'hr', 'hrs', 'h'], true) => 3600,
                in_array($unit, ['menit', 'mnt', 'min', 'mins', 'minute', 'minutes', 'm'], true) => 60,
                default => 1,
            };
            $seconds += $amount * $multiplier;
        }

        return $seconds > 0 && $seconds <= PHP_INT_MAX ? (int) round($seconds) : null;
    }
}
