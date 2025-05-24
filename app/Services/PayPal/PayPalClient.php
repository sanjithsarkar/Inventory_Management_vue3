<?php

namespace App\Services\PayPal;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PayPalClient
{
    /**
     * PayPal API base URLs
     */
    const SANDBOX_API_URL = 'https://api-m.sandbox.paypal.com';
    const LIVE_API_URL = 'https://api-m.paypal.com';
    
    /**
     * @var string Client ID from PayPal
     */
    protected $clientId;
    
    /**
     * @var string Client Secret from PayPal
     */
    protected $clientSecret;
    
    /**
     * @var string API mode (sandbox or live)
     */
    protected $mode;
    
    /**
     * @var int Request timeout in seconds
     */
    protected $timeout = 30;
    
    /**
     * @var string Access token cache key
     */
    protected $tokenCacheKey = 'paypal_access_token';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mode = config('paypal.mode', 'sandbox');
        
        // Get credentials based on mode
        if ($this->mode === 'sandbox') {
            $this->clientId = config('paypal.sandbox.client_id');
            $this->clientSecret = config('paypal.sandbox.client_secret');
        } else {
            $this->clientId = config('paypal.live.client_id');
            $this->clientSecret = config('paypal.live.client_secret');
        }
        
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new \Exception('PayPal client ID or secret is not configured for ' . $this->mode . ' mode');
        }
    }
    
    /**
     * Get the base API URL based on mode
     * 
     * @return string
     */
    protected function getBaseUrl()
    {
        return $this->mode === 'sandbox' ? self::SANDBOX_API_URL : self::LIVE_API_URL;
    }
    
    /**
     * Get access token for API requests
     * 
     * @return string
     */
    public function getAccessToken()
    {
        // Try to get token from cache first
        if (Cache::has($this->tokenCacheKey)) {
            return Cache::get($this->tokenCacheKey);
        }
        
        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post($this->getBaseUrl() . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials'
                ]);
                
            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'];
                $expiresIn = $data['expires_in'] - 60; // Subtract 60 seconds to be safe
                
                // Cache the token
                Cache::put($this->tokenCacheKey, $token, $expiresIn);
                
                return $token;
            }
            
            Log::error('PayPal token error', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);
            
            throw new \Exception('Failed to get PayPal access token');
        } catch (\Exception $e) {
            Log::error('PayPal token exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Make a request to the PayPal API
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return object
     */
    public function request($method, $endpoint, $data = [])
    {
        $token = $this->getAccessToken();
        $url = $this->getBaseUrl() . $endpoint;
        $method = strtolower($method);
        
        try {
            $response = Http::withToken($token)
                ->timeout($this->timeout)
                ->withHeaders([
                    'PayPal-Request-Id' => uniqid('paypal_request_'),
                    'Prefer' => 'return=representation'
                ])
                ->{$method}($url, $data);
                
            if ($response->successful()) {
                return $response->object();
            }
            
            Log::error('PayPal API error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->json()
            ]);
            
            throw new \Exception(
                $response->json()['message'] ?? 'Unknown PayPal API error',
                $response->status()
            );
        } catch (\Exception $e) {
            Log::error('PayPal API exception', [
                'endpoint' => $endpoint,
                'method' => $method,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Make a GET request
     * 
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @return object
     */
    public function get($endpoint, $params = [])
    {
        return $this->request('get', $endpoint, $params);
    }
    
    /**
     * Make a POST request
     * 
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return object
     */
    public function post($endpoint, $data = [])
    {
        return $this->request('post', $endpoint, $data);
    }
    
    /**
     * Make a PATCH request
     * 
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return object
     */
    public function patch($endpoint, $data = [])
    {
        return $this->request('patch', $endpoint, $data);
    }
    
    /**
     * Make a DELETE request
     * 
     * @param string $endpoint API endpoint
     * @return object
     */
    public function delete($endpoint)
    {
        return $this->request('delete', $endpoint);
    }
}
