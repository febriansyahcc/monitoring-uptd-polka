<?php

namespace App\Support;

/**
 * Kode status yang dikirim ke frontend. Frontend memetakan kode ini ke warna lewat
 * resources/js/utils/status.js — teks tampilan tidak dipakai untuk menentukan warna.
 */
final class Status
{
    public const OK = 'ok';
    public const WARNING = 'warning';
    public const CRITICAL = 'critical';
    public const EMPTY = 'empty';

    /** Ketahanan stok BBM (HOP, hari). Tanpa data → empty; ≤ 5 hari (termasuk 0) → critical; ≤ 10 → warning. */
    public static function fuel(bool $hasData, float $daysOfSupply): string
    {
        if (!$hasData) {
            return self::EMPTY;
        }

        if ($daysOfSupply <= 5) {
            return self::CRITICAL;
        }

        return $daysOfSupply <= 10 ? self::WARNING : self::OK;
    }

    /** Status penanganan gangguan: yang belum selesai perlu perhatian. */
    public static function disturbance(?string $status): string
    {
        return match ($status) {
            'Selesai' => self::OK,
            'Dalam Penanganan', 'Investigasi' => self::WARNING,
            default => self::EMPTY,
        };
    }
}
