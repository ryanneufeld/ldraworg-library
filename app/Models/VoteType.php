<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Vote;

class VoteType extends Model
{

    protected $primaryKey = 'code';
    public $incrementing = false;
    public $timestamps = false;
    
    public function votes() {
      return $this->hasMany(Vote::class, 'vote_type_code', 'code');
    }
    
}
