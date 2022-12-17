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

//    protected $with = ['user', 'type'];
    
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
    
    public function updatePartSubpartCount() {
      $this->part->load('parents');
      if ($this->part->parents()->exists()) {
        foreach($this->part->parents as $part) {
          $part->updateUncertifiedSubpartsCache();
        }
      }
      else {
        $this->part->updateUncertifiedSubpartsCache();
      }  
    }  
}
