<?php

namespace App\Models;

use App\Models\Traits\HasParts;
use Illuminate\Database\Eloquent\Model;

class PartType extends Model
{
    use HasParts;

    public $timestamps = false;
    
    public function toString(bool $unofficial = false): string 
    {
        $u = $unofficial ? 'Unofficial_' : '';
        return "0 !LDRAW_ORG {$u}{$this->type}";
    }      
}
