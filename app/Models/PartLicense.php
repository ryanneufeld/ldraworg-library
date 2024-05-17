<?php

namespace App\Models;

use App\Models\Traits\HasParts;
use Illuminate\Database\Eloquent\Model;

class PartLicense extends Model
{
    use HasParts;

    public $timestamps = false;
    
    public $fillable = [
        'name',
        'text'
    ];
    
    public function toString(): string
    {
        return "0 !LICENSE {$this->text}";
    }

    public static function default(): self 
    {
        return self::firstWhere('name', config('ldraw.license.default'));
    }      
}
