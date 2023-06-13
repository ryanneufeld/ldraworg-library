<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartLicense extends Model
{
    public $timestamps = false;

    public function parts()
    {
        return $this->hasMany(Part::class);
    }

    public static function findByName($name) {
      return self::firstWhere('name', $name);
    }
    
    public function toString() {
      return "0 !LICENSE {$this->text}";
    }

    public static function default() {
      return self::firstWhere('name', config('ldraw.license.default'));
    }      
}
