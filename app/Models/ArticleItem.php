<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleItem extends Model
{
    use HasFactory;
    
    protected $connection = 'cmsms';
    protected $table = 'cms_content_props';

}
