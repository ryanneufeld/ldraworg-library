<?php

namespace App\Models\Document;

use App\Models\Traits\HasOrder;
use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    use HasOrder;
    
    protected $fillable = [
        'category',
        'order'
    ];

    public $timestamps = false;

}
