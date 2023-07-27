<?php

namespace App\Models;

use App\Models\Traits\HasPart;
use Illuminate\Database\Eloquent\Model;

class PartHelp extends Model
{
    use HasPart;

    protected $fillable = [
        'order',
        'text',
        'part_id',
    ];

    public $timestamps = false;
}
