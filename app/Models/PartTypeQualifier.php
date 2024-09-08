<?php

namespace App\Models;

use App\Models\Traits\HasParts;
use Illuminate\Database\Eloquent\Model;

class PartTypeQualifier extends Model
{
    use HasParts;

    public $timestamps = false;

    public $fillable = [
        'type',
        'name',
    ];
}
