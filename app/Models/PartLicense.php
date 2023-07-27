<?php

namespace App\Models;

use App\Models\Traits\HasParts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartLicense extends Model
{
    use HasParts;

    public $timestamps = false;
    
    public function toString(): string
    {
        return "0 !LICENSE {$this->text}";
    }

    public static function default(): self 
    {
        return self::firstWhere('name', config('ldraw.license.default'));
    }      
}
