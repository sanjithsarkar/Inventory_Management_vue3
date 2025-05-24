<?php

namespace App\Services\Stripe;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StripeClient
{
    /**
     * Stripe API Endpoint
     */
    const ENDPOINT = 'https://api.stripe.com/v1/';
    
    /**
     * Secret API Key
     */
    protected $secretKey;
    
    /**
     * API Version
     */
    protected $apiVersion;
    
    /**
     * Request timeout
     */
    protected $timeout;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->secretKey = config('stripe.sk');
        $this->apiVersion = config('stripe.api_version');
        $this->timeout = config('stripe.timeout', 60);
        
        if (empty($this->secretKey)) {
            throw new \Exception('Stripe secret key is not configured');
        }
    }
    
    /**
     * Get request headers
     * 
     * @return array
     */
    protected function getHeaders()
    {
        $appInfo = [
            'name' => config('app.name'),
            'version' => config('app.version', '1.0.0'),
            'url' => config('app.url'),
        ];
        
        $userAgent = [
            'lang' => 'php',
            'publisher' => 'laravel',
            'uname' => php_uname(),
            'lang_version' => phpversion(),
            'application' => $appInfo,
        ];
        
        return [
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Stripe-Version' => $this->apiVersion,
            'User-Agent' => $appInfo['name'] . '/' . $appInfo['version'] . ' (' . $appInfo['url'] . ')',
            'X-Stripe-Client-User-Agent' => json_encode($userAgent),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
    }
    
    /**
     * Generate idempotency key
     * 
     * @param array $data Request data
     * @param string $endpoint API endpoint
     * @return string
     */
    protected function generateIdempotencyKey($data, $endpoint)
    {
        // If there's a transaction ID, use it for a more specific key
        if (isset($data['metadata']['transaction_id'])) {
            $transactionId = $data['metadata']['transaction_id'];
            $customerId = $data['customer'] ?? '';
            $source = $data['source'] ?? $customerId;
            
            return $transactionId . '-' . $source . '-' . $endpoint;
        }
        
        // Otherwise generate a UUID
        return (string) Str::uuid();
    }
    
    /**
     * Send a request to Stripe API
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param bool $idempotent Whether to use idempotency key
     * @return object
     * @throws \Exception
     */
    public function request($method, $endpoint, $data = [], $idempotent = true)
    {
        $headers = $this->getHeaders();
        $method = strtolower($method);
        $url = self::ENDPOINT . $endpoint;
        
        // Add idempotency key for POST requests
        if ($idempotent && $method === 'post') {
            $idempotencyKey = $this->generateIdempotencyKey($data, $endpoint);
            $headers['Idempotency-Key'] = $idempotencyKey;
            
            Log::debug('Stripe API request', [
                'endpoint' => $endpoint,
                'method' => $method,
                'idempotency_key' => $idempotencyKey
            ]);
        }
        
        try {
            $response = Http::withHeaders($headers)
                ->timeout($this->timeout)
                ->asForm()
                ->{$method}($url, $data);
            
            if ($response->successful()) {
                return $response->object();
            }
            
            $error = $response->json();
            Log::error('Stripe API Error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'error' => $error
            ]);
            
            throw new \Exception(
                $error['error']['message'] ?? 'Unknown Stripe API error',
                $response->status()
            );
        } catch (\Exception $e) {
            Log::error('Stripe API Exception', [
                'endpoint' => $endpoint,
                'method' => $method,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * GET request
     * 
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @return object
     */
    public function get($endpoint, $params = [])
    {
        return $this->request('get', $endpoint, $params, false);
    }
    
    /**
     * POST request
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
     * DELETE request
     * 
     * @param string $endpoint API endpoint
     * @return object
     */
    public function delete($endpoint)
    {
        return $this->request('delete', $endpoint, [], false);
    }
}