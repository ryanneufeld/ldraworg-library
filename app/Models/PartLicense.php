<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Part;

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

    public static function defaultLicense() {
      return self::firstWhere('name', config('ldraw.license.default'));
    }      
}
