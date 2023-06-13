<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartHelp extends Model
{
  protected $fillable = [
    'order',
    'text',
    'part_id',
  ];

  public $timestamps = false;

  public function part()
  {
      return $this->belongsTo(Part::class);
  }

}
