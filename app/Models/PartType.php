<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Part;

class PartType extends Model
{
    public $timestamps = false;

    public function parts()
    {
        return $this->hasMany(Part::class);
    }
    
    public static function findByType($type) {
      return self::firstWhere('type', $type);
    }

    public function toString($unofficial = false) {
      $u = $unofficial ? 'Unofficial_' : '';
      return "0 !LDRAW_ORG {$u}{$this->type}";
    }      
}
