<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoteType extends Model
{

    protected $primaryKey = 'code';
    public $incrementing = false;
    public $timestamps = false;
    
    protected $fillable = [
        'code',
        'short',
        'name',
        'phrase',
        'sort',
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'vote_type_code', 'code');
    }
    
}
