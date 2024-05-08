<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartRenderView extends Model
{
    protected $fillable = [
        'part_name',
        'matrix',
    ];

    public $timestamps = false;
}
