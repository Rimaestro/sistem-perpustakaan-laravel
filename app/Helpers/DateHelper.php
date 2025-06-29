<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Format tanggal ke bahasa Indonesia
     */
    public static function formatIndonesian($date, $format = 'd F Y'): string
    {
        if (!$date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        // Set locale ke Indonesia
        $carbon->locale('id');
        
        return $carbon->translatedFormat($format);
    }

    /**
     * Format tanggal dengan hari dalam bahasa Indonesia
     */
    public static function formatWithDay($date): string
    {
        if (!$date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $carbon->locale('id');
        
        return $carbon->translatedFormat('l, d F Y');
    }

    /**
     * Format tanggal singkat (dd/mm/yyyy)
     */
    public static function formatShort($date): string
    {
        if (!$date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        return $carbon->format('d/m/Y');
    }

    /**
     * Hitung hari terlambat
     */
    public static function calculateOverdueDays($dueDate): int
    {
        if (!$dueDate) {
            return 0;
        }

        $carbon = $dueDate instanceof Carbon ? $dueDate : Carbon::parse($dueDate);

        if ($carbon->isFuture()) {
            return 0;
        }

        // Gunakan diffInDays dengan parameter false untuk mendapatkan nilai absolut integer
        return (int) $carbon->diffInDays(now(), false);
    }

    /**
     * Format mata uang Rupiah
     */
    public static function formatCurrency($amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Get relative time in Indonesian
     */
    public static function timeAgo($date): string
    {
        if (!$date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $carbon->locale('id');
        
        return $carbon->diffForHumans();
    }

    /**
     * Check if date is overdue
     */
    public static function isOverdue($dueDate): bool
    {
        if (!$dueDate) {
            return false;
        }

        $carbon = $dueDate instanceof Carbon ? $dueDate : Carbon::parse($dueDate);
        
        return $carbon->isPast();
    }

    /**
     * Get days until due date (negative if overdue)
     */
    public static function daysUntilDue($dueDate): int
    {
        if (!$dueDate) {
            return 0;
        }

        $carbon = $dueDate instanceof Carbon ? $dueDate : Carbon::parse($dueDate);

        return (int) now()->diffInDays($carbon, false);
    }
}
