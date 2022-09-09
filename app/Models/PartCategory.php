<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Part;

class PartCategory extends Model
{
    public $timestamps = false;

    public function parts()
    {
        return $this->hasMany(Part::class);
    }
}
