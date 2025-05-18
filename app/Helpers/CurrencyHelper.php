<?php

namespace App\Helpers;

use App\Models\Setting;

class CurrencyHelper
{
    /**
     * Format a number as currency
     *
     * @param float $amount
     * @return string
     */
    public static function format($amount)
    {
        $settings = Setting::getCurrencySettings();
        
        $symbol = $settings['currency_symbol'] ?? '$';
        $position = $settings['currency_position'] ?? 'before';
        $decimalSeparator = $settings['decimal_separator'] ?? '.';
        $thousandSeparator = $settings['thousand_separator'] ?? ',';
        $decimalPlaces = (int)($settings['decimal_places'] ?? 2);
        
        // Format the number
        $formattedNumber = number_format(
            (float)$amount,
            $decimalPlaces,
            $decimalSeparator,
            $thousandSeparator
        );
        
        // Add the currency symbol
        if ($position === 'before') {
            return $symbol . $formattedNumber;
        } else {
            return $formattedNumber . $symbol;
        }
    }
}