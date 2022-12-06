<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use App\Models\Part;
use App\Models\VoteType;
use App\Models\User;

class Vote extends Model
{
    protected $fillable = ['user_id', 'part_id', 'vote_type_code'];

    protected static function booted()
    {
/*
        static::saved(function ($vote) {
          if ($vote->part->parents()->exists()) {
            foreach($vote->part->parents as $part) {
              $part->updateUncertifiedSubpartsCache();
            }
          }
          else {
            $vote->part->updateUncertifiedSubpartsCache();
          }  
        });
        static::deleted(function ($vote) {
          if ($vote->part->parents()->exists()) {
            foreach($vote->part->parents as $part) {
              $part->updateUncertifiedSubpartsCache();
            }
          }
          else {
            $vote->part->updateUncertifiedSubpartsCache();
          }  
        });
*/
    }
    
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
