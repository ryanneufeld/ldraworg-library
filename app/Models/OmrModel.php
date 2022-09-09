<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OmrModel extends Model
{
    use HasFactory;
    
    public function author() {
      return $this->belongsTo(User::class);
    }  
 
    public function set() {
      return $this->hasOne(Set::class);
    }
    
    public function files() {
      return $this->hasMany(File::class);
    }  

}
