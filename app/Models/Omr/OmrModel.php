<?php

namespace App\Models\Omr;

use App\Models\Traits\HasLicense;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OmrModel extends Model
{
    use HasUser, HasLicense;

    protected $guarded = [];

    protected $with = [
        'set',
    ];

    protected $casts = [
        'notes' => 'array',
    ];

    
    public function set(): BelongsTo 
    {
        return $this->belongsTo(Set::class, 'set_id', 'id');
    }

    public function filename(): string
    {
        $filename = $this->set->number;
        $filename .= $this->alt_model ? '_' . str_replace(' ', '-', $this->alt_model_name) : '';
        return "{$filename}.mpd";
    }
}
