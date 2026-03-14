<?php

if (!function_exists('format_number')) {
    /**
     * Formats a number with standard decimals and separators.
     */
    function format_number($number, $decimals = 2)
    {
        if ($number === null) return null;

        // 1. Standard number format (1,000.00)
        $formatted = number_format((float)$number, $decimals, '.', ',');

        // 2. Trim trailing zeros for a cleaner look (e.g., 50.00 -> 50)
        if (strpos($formatted, '.') !== false) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        return $formatted;
    }
}

if (!function_exists('format_currency')) {
    /**
     * Formats currency using the format_number helper in a standard BDT format.
     */
    function format_currency($amount, $symbol = '৳')
    {
        if ($amount === null) return null;

        // Use the format_number helper for the numeric part
        $formatted = format_number($amount);

        // Standard placement: Symbol then Amount (e.g., ৳1,200)
        return "{$symbol}{$formatted}";
    }
}
