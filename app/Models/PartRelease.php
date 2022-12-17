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
    
    public function parts() {
      return hasMany(Parts::class);
    }
    
    public function toString() {
      if ($this->short == 'unof') return ''; 
      return $this->short == 'original' ? "ORIGINAL" : "UPDATE {$this->name}";
    }

    public static function unofficial() {
      return self::firstWhere('short','unof');
    }

    public static function current() {
      return self::where('short', '<>', 'unof')->latest()->first();
    }      
    
    // Warning: this function does not check if the release line already exists
    public function addHistoryToParts() {
      foreach ($this->parts() as $part) {
        PartHistory::create([
          'user_id' => User::ptadmin(), 
          'part_id' => $part->id, 
          'created_at' => date_format(date_create($this->created_at), "Y-m-d"), 
          'comment' => "Official Update {$this->name}"
        ]);
      }  
    }  
}
