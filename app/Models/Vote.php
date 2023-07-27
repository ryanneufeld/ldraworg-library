<?php

namespace App\Models;

use App\Models\Traits\HasPart;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    use HasUser, HasPart;

    protected $fillable = ['user_id', 'part_id', 'vote_type_code'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(VoteType::class, 'vote_type_code', 'code');
    }
}
