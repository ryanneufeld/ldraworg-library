<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Parts;

class PartRelease extends Model
{
    use HasFactory;
    
    public function parts() {
      return hasMany(Parts::class);
    }
}
