<?php

namespace App\LDraw;

use ArtisanSdk\RateLimiter\Contracts\Bucket;
use ArtisanSdk\RateLimiter\Limiter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Rebrickable
{
    protected Limiter $limiter;

    public function __construct(
        public readonly string $api_key,
        public readonly string $api_url
    ) {
        $this->limiter = app(Limiter::class, ['bucket' => app(Bucket::class, ['rebrickable', 2, 1])]);
    }

    protected function makeApiCall(string $url): ?array
    {
        if ($this->limiter->exceeded()) {
            sleep($this->limiter->backoff());
        }

        $response = Http::withHeaders([
            'Authorization' => "key {$this->api_key}",
        ])
        ->acceptJson()
        ->get($url);
        
        $this->limiter->hit();

        if (!$response->successful()) {
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

    public function getSetParts(string $setnumber): ?Collection 
    {
        $parts = $this->makeApiCall("{$this->api_url}/sets/{$setnumber}/parts/?inc_minifig_parts=1");
        if (is_null($parts)) {
            return null;
        }
        $parts = collect($parts);
        $parts = $parts->map(fn (array $part, int $key) =>
            [
                'rb_part_number' => $part['part']['part_num'],
                'rb_part_name' => $part['part']['name'],
                'rb_part_url' => $part['part']['part_url'],
                'color_name' => $part['color']['name'],
                'ldraw_color_number' => $part['color']['external_ids']['LDraw']['ext_ids'][0] ?? null,
                'ldraw_part_number' => $part['part']['external_ids']['LDraw'][0] ?? null,
                'bricklink_part_number' => $part['part']['external_ids']['BrickLink'][0] ?? null,
                'quantity' => $part['quantity'],
                'print_of' => $part['part']['print_of'] ?? null,
            ]
        );
        return $parts;
    }

    public function getSet(string $setnumber): ?array 
    {
        return $this->makeApiCall("{$this->api_url}/sets/{$setnumber}/");
    }  

    public function getPart(string $partnumber): ?array 
    {
        $part = $this->makeApiCall("{$this->api_url}/parts/{$partnumber}/");
        if (is_null($part)) {
            return null;
        }
        $part = [
            'rb_part_number' => $part['part_num'],
            'rb_part_name' => $part['name'],
            'rb_part_url' => $part['part_url'],
            'ldraw_part_number' => $part['external_ids']['LDraw'][0] ?? null,
            'bricklink_part_number' => $part['external_ids']['BrickLink'][0] ?? null,
            'print_of' => $part['print_of'] ?? null,
        ];
        return $part;
    }  

    public function getParts(array $partnumbers): ?Collection 
    {
        $parts = implode(',', $partnumbers);
        $parts = $this->makeApiCall("{$this->api_url}/parts/?part_nums={$parts}");
        if (is_null($parts)) {
            return null;
        }
        $parts = collect($parts);
        $parts = $parts->map(fn (array $part, int $key) =>
            [
                'rb_part_number' => $part['part_num'],
                'rb_part_name' => $part['name'],
                'rb_part_url' => $part['part_url'],
                'ldraw_part_number' => $part['external_ids']['LDraw'][0] ?? null,
                'bricklink_part_number' => $part['external_ids']['BrickLink'][0] ?? null,
                'print_of' => $part['print_of'] ?? null,
            ]
        );
        return $parts;
    }
    
}