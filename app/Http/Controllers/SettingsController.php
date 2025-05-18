<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Get all settings
     */
    public function index(Request $request)
    {
        $group = $request->query('group', 'general');
        $settings = Setting::where('group', $group)->get();
        
        return response()->json($settings);
    }

    /**
     * Get currency settings
     */
    public function getCurrencySettings()
    {
        $settings = Setting::getCurrencySettings();
        
        return response()->json($settings);
    }

    /**
     * Update currency settings
     */
    public function updateCurrencySettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency_code' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:10',
            'currency_position' => 'required|in:before,after',
            'decimal_separator' => 'required|string|max:1',
            'thousand_separator' => 'required|string|max:1',
            'decimal_places' => 'required|integer|min:0|max:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updated = [];
        
        foreach ($request->all() as $key => $value) {
            $updated[$key] = Setting::set($key, $value, 'currency');
        }
        
        // Clear settings cache
        Setting::clearCache();
        
        return response()->json([
            'message' => 'Currency settings updated successfully',
            'settings' => Setting::getCurrencySettings()
        ]);
    }

    /**
     * Get available currencies
     */
    public function getAvailableCurrencies()
    {
        $currencies = [
            'USD' => ['name' => 'US Dollar', 'symbol' => '$'],
            'EUR' => ['name' => 'Euro', 'symbol' => '€'],
            'GBP' => ['name' => 'British Pound', 'symbol' => '£'],
            'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥'],
            'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥'],
            'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹'],
            'BDT' => ['name' => 'Bangladeshi Taka', 'symbol' => '৳'],
            'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$'],
            'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$'],
            'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'CHF'],
            'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$'],
            'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
            'THB' => ['name' => 'Thai Baht', 'symbol' => '฿'],
            'IDR' => ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp'],
            'PHP' => ['name' => 'Philippine Peso', 'symbol' => '₱'],
            'VND' => ['name' => 'Vietnamese Dong', 'symbol' => '₫'],
            'KRW' => ['name' => 'South Korean Won', 'symbol' => '₩'],
            'NGN' => ['name' => 'Nigerian Naira', 'symbol' => '₦'],
            'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R'],
            'BRL' => ['name' => 'Brazilian Real', 'symbol' => 'R$'],
            'MXN' => ['name' => 'Mexican Peso', 'symbol' => 'Mex$'],
            'RUB' => ['name' => 'Russian Ruble', 'symbol' => '₽'],
            'TRY' => ['name' => 'Turkish Lira', 'symbol' => '₺'],
            'SAR' => ['name' => 'Saudi Riyal', 'symbol' => '﷼'],
            'AED' => ['name' => 'United Arab Emirates Dirham', 'symbol' => 'د.إ'],
        ];
        
        return response()->json($currencies);
    }
}