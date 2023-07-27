<?php

namespace App\Models;

use App\Models\Traits\HasParts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartCategory extends Model
{
    use HasParts;

    public $timestamps = false;

    public function toString(): string 
    {
        return "0 !CATEGORY {$this->category}";
    }      
    
}
