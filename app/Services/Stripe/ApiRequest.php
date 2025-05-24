<?php

namespace App\Services\Stripe;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Stripe API Request Handler
 * 
 * Handles communication with the Stripe API endpoints
 */
class ApiRequest
{
    /**
     * Stripe API Endpoint
     */
    const ENDPOINT = 'https://api.stripe.com/v1/';
    
    /**
     * Stripe API Version
     */
    const API_VERSION = '2023-10-16';
    
    /**
     * Secret API Key
     * @var string
     */
    private static $secretKey = '';
    
    /**
     * Set secret API Key
     * @param string $secretKey
     */
    public static function setSecretKey($secretKey)
    {
        self::$secretKey = $secretKey;
    }
    
    /**
     * Get secret key
     * @return string
     */
    public static function getSecretKey()
    {
        if (!self::$secretKey) {
            self::$secretKey = config('stripe.sk');
        }
        
        if (empty(self::$secretKey)) {
            throw new \Exception('Stripe secret key is not configured');
        }
        
        return self::$secretKey;
    }
    
    /**
     * Generates the user agent for API requests
     * @return array
     */
    public static function getUserAgent()
    {
        $appInfo = [
            'name' => config('app.name'),
            'version' => config('app.version', '1.0.0'),
            'url' => config('app.url'),
        ];
        
        return [
            'lang' => 'php',
            'publisher' => 'laravel',
            'uname' => php_uname(),
            'lang_version' => phpversion(),
            'application' => $appInfo,
        ];
    }
    
    /**
     * Generates the headers for API requests
     * @return array
     */
    public static function getHeaders()
    {
        $userAgent = self::getUserAgent();
        $appInfo = $userAgent['application'];
        
        return [
            'Authorization' => 'Bearer ' . self::getSecretKey(),
            'Stripe-Version' => self::API_VERSION,
            'User-Agent' => $appInfo['name'] . '/' . $appInfo['version'] . ' (' . $appInfo['url'] . ')',
            'X-Stripe-Client-User-Agent' => json_encode($userAgent),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
    }
    
    /**
     * Send a request to Stripe's API
     *
     * @param array $requestData Request data
     * @param string $endpoint API endpoint
     * @param string $method HTTP method (POST, GET, DELETE)
     * @param bool $idempotent Whether to use idempotency key
     * @return object|array Response data
     * @throws \Exception
     */
    public static function request($requestData, $endpoint, $method = 'POST', $idempotent = true)
    {
        $headers = self::getHeaders();
        
        // Add idempotency key for POST requests to prevent duplicate operations
        if ($idempotent && $method === 'POST') {
            $idempotencyKey = Str::uuid()->toString();
            $headers['Idempotency-Key'] = $idempotencyKey;
            Log::info("Stripe API request with idempotency key: {$idempotencyKey}");
        }
        
        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->asForm()
                ->{strtolower($method)}(self::ENDPOINT . $endpoint, $requestData);
            
            if ($response->failed()) {
                $error = $response->json();
                Log::error('Stripe API Error', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'error' => $error
                ]);
                
                throw new \Exception(
                    isset($error['error']['message']) 
                        ? $error['error']['message'] 
                        : 'Unknown Stripe API error'
                );
            }
            
            return $response->object();
        } catch (\Exception $e) {
            Log::error('Stripe API Exception', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Retrieve data from Stripe API
     *
     * @param string $endpoint API endpoint
     * @return object Response data
     * @throws \Exception
     */
    public static function retrieve($endpoint)
    {
        return self::request([], $endpoint, 'GET', false);
    }
    
    /**
     * Create a resource in Stripe
     *
     * @param array $data Resource data
     * @param string $endpoint API endpoint
     * @return object Response data
     * @throws \Exception
     */
    public static function create($data, $endpoint)
    {
        return self::request($data, $endpoint, 'POST');
    }
    
    /**
     * Update a resource in Stripe
     *
     * @param array $data Update data
     * @param string $endpoint API endpoint
     * @return object Response data
     * @throws \Exception
     */
    public static function update($data, $endpoint)
    {
        return self::request($data, $endpoint, 'POST');
    }
    
    /**
     * Delete a resource in Stripe
     *
     * @param string $endpoint API endpoint
     * @return object Response data
     * @throws \Exception
     */
    public static function delete($endpoint)
    {
        return self::request([], $endpoint, 'DELETE', false);
    }
}