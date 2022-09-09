<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Part;

class PartHistory extends Model
{
    use HasFactory;
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
    
    public function toString() {
      $date = date_format(date_create($this->created_at), "Y-m-d");
      $user = $this->user->historyString();
      return "0 !HISTORY $date $user {$this->comment}";
    }
}
