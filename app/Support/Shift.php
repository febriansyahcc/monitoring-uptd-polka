<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Shift operasional ULPLTD Poka (zona waktu aplikasi, lihat config/app.php):
 * pagi 08:00–16:00, sore 16:00–24:00, malam 00:00–08:00 — sama dengan
 * CurrentMonitoringController::SHIFT_INTERVALS dan resources/js/utils/shift.js.
 */
final class Shift
{
    public const LABELS = [
        'pagi' => 'Shift Pagi',
        'sore' => 'Shift Sore',
        'malam' => 'Shift Malam',
    ];

    public static function keyAt(Carbon $time): string
    {
        $hour = (int) $time->format('G');

        return match (true) {
            $hour >= 8 && $hour < 16 => 'pagi',
            $hour >= 16 => 'sore',
            default => 'malam',
        };
    }

    /** @return array{key: string, label: string} */
    public static function current(?Carbon $time = null): array
    {
        $key = self::keyAt($time ?? Carbon::now());

        return ['key' => $key, 'label' => self::LABELS[$key]];
    }
}
