<?php

namespace App\Models;

use App\Models\Rebrickable\RebrickablePart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StickerSheet extends Model
{
    protected $fillable = [
        'number',
        'rebrickable_part_id'
    ];

    public function rebrickable_part(): BelongsTo
    {
        return $this->BelongsTo(RebrickablePart::class, 'rebrickable_part_id', 'id');
    }
}
