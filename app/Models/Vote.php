<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = ['user_id', 'part_id', 'vote_type_code'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function type()
    {
        return $this->belongsTo(VoteType::class, 'vote_type_code', 'code');
    }
}
