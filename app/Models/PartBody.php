<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Part;

class PartBody extends Model
{
  protected $fillable = [
    'body',
    'part_id',
  ];

  public $timestamps = false;
  
  public function body() {
    return $this->belongsTo(Part::class, 'part_id', 'id');
  }
}
