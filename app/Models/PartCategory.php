<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PartCategory extends Model
{
    public $timestamps = false;

    public function parts()
    {
        return $this->hasMany(Part::class);
    }

    public static function findByName($name) {
      if (empty(self::firstWhere('category', $name))) Log::debug($name);
      return self::firstWhere('category', $name);
    }  

    public function toString() {
      return "0 !CATEGORY {$this->category}";
    }      
    
}
