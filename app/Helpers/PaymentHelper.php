<?php

namespace App\Helpers;

class PaymentHelper
{
    /**
     * Convert a decimal amount to cents for Stripe
     *
     * @param float $amount Amount in decimal (e.g., 19.99)
     * @return int Amount in cents (e.g., 1999)
     */
    public static function amountToCents($amount)
    {
        return (int) round($amount * 100);
    }
}