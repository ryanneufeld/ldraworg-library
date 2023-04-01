<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartSearchResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'results';

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'title' => $this->name(),
            'description' => $this->description,
            'url' => $this->isUnofficial() ? route('tracker.show', $this->id) : route('official.show', $this->id),  
        ];
    }
}
