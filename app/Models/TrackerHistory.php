<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class TrackerHistory extends Model
{
  protected $casts = [
    'history_data' => AsArrayObject::class,
  ];  
}
