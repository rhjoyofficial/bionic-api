<?php

if (!function_exists('format_number')) {
    /**
     * Formats a number with decimals and converts to Bangla digits if locale is 'bn'
     */
    function format_number($number, $decimals = 2)
    {
        if ($number === null) return null;

        $locale = app()->getLocale();

        // 1. Standard number format
        $formatted = number_format((float)$number, $decimals, '.', ',');

        // 2. Trim trailing zeros (e.g., 50.00 -> 50, 50.50 -> 50.5)
        if (strpos($formatted, '.') !== false) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        // 3. Convert digits if Bangla
        if ($locale === 'bn') {
            $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $banglaDigits  = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
            $formatted = str_replace($englishDigits, $banglaDigits, $formatted);
        }

        return $formatted;
    }
}

if (!function_exists('format_currency')) {
    /**
     * Formats currency using the format_number helper
     */
    function format_currency($amount, $currency = null)
    {
        if ($amount === null) return null;

        $locale = app()->getLocale();

        // 1. Set default currency symbol
        if ($currency === null) {
            $currency = ($locale === 'bn') ? '৳' : 'BDT';
        }

        // 2. Use the format_number helper for the numeric part
        $formatted = format_number($amount);

        // 3. Return with currency symbol placement
        return ($locale === 'bn')
            ? "{$currency}{$formatted}"
            : "{$formatted} {$currency}";
    }
}
