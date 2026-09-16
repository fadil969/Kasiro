<?php

use Illuminate\Support\Carbon;

if (! function_exists('kasiro_tanggal')) {
    /**
     * Tanggal singkat sesuai bahasa aktif.
     * id → "13 Agu 2024"   en → "Aug 13, 2024"
     */
    function kasiro_tanggal($d): string
    {
        if (! $d) {
            return '-';
        }
        $dt   = $d instanceof \DateTimeInterface ? Carbon::instance(Carbon::parse($d)) : Carbon::parse((string) $d);
        $mon  = __('ui.date.months_short')[(int) $dt->format('n') - 1];
        $day  = (int) $dt->format('j');
        $year = $dt->format('Y');

        return app()->getLocale() === 'en' ? "$mon $day, $year" : "$day $mon $year";
    }
}

if (! function_exists('kasiro_tanggal_panjang')) {
    /**
     * Tanggal hari ini, format panjang sesuai bahasa aktif (untuk topbar).
     * id → "Selasa, 15 September 2026"   en → "Tuesday, September 15, 2026"
     */
    function kasiro_tanggal_panjang($d = null): string
    {
        $dt = $d ? Carbon::parse((string) $d) : Carbon::now();
        $hari  = __('ui.date.weekdays')[(int) $dt->format('w')];
        $bulan = __('ui.date.months')[(int) $dt->format('n') - 1];
        $day   = (int) $dt->format('j');
        $year  = $dt->format('Y');

        return app()->getLocale() === 'en'
            ? "$hari, $bulan $day, $year"
            : "$hari, $day $bulan $year";
    }
}

if (! function_exists('kasiro_status')) {
    /** Tampilkan nilai status DB (Aktif/Nonaktif) dalam bahasa aktif. */
    function kasiro_status(string $status): string
    {
        return __('ui.common.status_badge')[$status] ?? $status;
    }
}
