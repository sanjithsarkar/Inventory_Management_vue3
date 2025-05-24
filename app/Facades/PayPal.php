<?php

namespace App\Facades;

use App\Services\PayPal\PayPalService;
use Illuminate\Support\Facades\Facade;

class PayPal extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return PayPalService::class;
    }
}