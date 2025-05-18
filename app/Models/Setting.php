<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'group'];

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // Try to get from cache first
        return Cache::remember('setting_' . $key, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @return bool
     */
    public static function set(string $key, $value, string $group = 'general')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        // Clear the cache for this key
        Cache::forget('setting_' . $key);
        
        return $setting->wasRecentlyCreated || $setting->wasChanged();
    }

    /**
     * Get all settings by group
     *
     * @param string $group
     * @return array
     */
    public static function getByGroup(string $group)
    {
        return Cache::remember('settings_group_' . $group, 3600, function () use ($group) {
            return self::where('group', $group)
                ->get()
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Get all currency settings
     *
     * @return array
     */
    public static function getCurrencySettings()
    {
        return self::getByGroup('currency');
    }

    /**
     * Clear settings cache
     */
    public static function clearCache()
    {
        $keys = self::all()->pluck('key')->toArray();
        foreach ($keys as $key) {
            Cache::forget('setting_' . $key);
        }
        
        $groups = self::distinct('group')->pluck('group')->toArray();
        foreach ($groups as $group) {
            Cache::forget('settings_group_' . $group);
        }
    }
}