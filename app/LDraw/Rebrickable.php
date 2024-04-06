<?php

namespace App\LDraw;

use ArtisanSdk\RateLimiter\Buckets\Leaky;
use ArtisanSdk\RateLimiter\Limiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Rebrickable
{
    protected Limiter $limiter;
    protected Leaky $bucket;

    public function __construct(
        public readonly string $api_key,
        public readonly string $api_url
    ) {
        $this->bucket = new Leaky('rebrickable', 2, 1);
        $this->limiter = new Limiter(Cache::store(), $this->bucket);
    }

    protected function makeApiCall(string $url): ?array
    {
        if ($this->limiter->exceeded()) {
            sleep($this->bucket->rate());
        }

        $response = Http::withHeaders([
            'Authorization' => "key {$this->api_key}",
        ])
        ->acceptJson()
        ->get($url);
        
        $this->limiter->hit();

        if ($response->failed()) {
            return null;
        }

        if (!is_null($response->json('next')) && !is_null($response->json('results'))) {
            $result = array_merge($response->json('results'), $this->makeApiCall($response->json('next')));
            return $result;           
        } elseif (!is_null($response->json('results'))) {
            return $response->json('results');
        }
        return $response->json();   
    }

    public function getSetParts(string $setnumber): array {
        return $this->makeApiCall("{$this->api_url}/sets/{$setnumber}/parts/?inc_minifig_parts=1");
    }

    public function getSet(string $setnumber): array {
        return $this->makeApiCall("{$this->api_url}/sets/{$setnumber}/");
    }  
}