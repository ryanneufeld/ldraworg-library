<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Parts;
use App\Models\PartHistory;
use App\Models\User;

class PartRelease extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'short', 'notes', 'created_at'];
    
    public function parts() {
      return $this->hasMany(Parts::class);
    }
    
    public function toString() {
      if ($this->short == 'unof') return ''; 
      return $this->short == 'original' ? " ORIGINAL" : " UPDATE {$this->name}";
    }

    public static function unofficial() {
      return self::firstWhere('short','unof');
    }

    public static function current() {
      return self::where('short', '<>', 'unof')->latest()->first();
    }      
    
    public static function next() {
      $current = self::current();
      $year = date_create();
      if (date_format($year, 'Y') != date_format(date_create($current->created_at), 'Y')) {
        return ['name' => date_format($year, 'Y') . "-01", 'short' => date_format($year, 'y') . '01'];
      }
      else {
        $num = (int) substr($current->name,-2) + 1;
        if ($num <= 9) {
          $num = "0$num";
        }
        return ['name' => date_format($year, 'Y') . "-$num", 'short' => date_format($year, 'y') . $num];
      }    
    }
}
