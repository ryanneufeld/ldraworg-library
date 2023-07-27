<?php

namespace App\Models;

use App\Models\Traits\HasPart;
use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\HasPartRelease;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartEvent extends Model
{
    use HasPartRelease, HasUser, HasPart;
    
    protected $fillable = [
        'created_at',
        'initial_submit',
        'part_id',
        'user_id',
        'vote_type_code',
        'part_release_id',
        'part_event_type_id',
        'comment',
        'deleted_filename',
        'deleted_description',
        'moved_from_filename',
        'moved_from_filename',
        'initial_submit',
    ];

    protected $casts = [
        'initial_submit' => 'boolean',
    ];
  
    public function part_event_type(): BelongsTo 
    {
        return $this->belongsTo(PartEventType::class);
    }
    
    public function vote_type(): BelongsTo 
    {
        return $this->belongsTo(VoteType::class);
    }
}
