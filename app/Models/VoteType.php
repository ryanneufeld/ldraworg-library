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
    
    public static function defaultArray() {
      $arr = [];
      foreach (self::all() as $vt) $arr[$vt->code] = 0;
      return $arr;
    }
}
