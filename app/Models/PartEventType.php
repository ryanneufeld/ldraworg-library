<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\PartEvent;

class PartEventType extends Model
{
    use HasFactory;

    public function events() {
      return $this->hasMany(PartEvent::class);
    }
}
