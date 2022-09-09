<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ArticleItem;

class Article extends Model
{
    use HasFactory;

    protected $connection = 'cmsms';
    protected $table = 'cms_content';
    protected $primaryKey = 'content_id';
    
    public function props()
    {
        return $this->hasMany(ArticleItem::class, 'content_id', 'content_id');
    }

    public function prop($prop)
    {
        return $this->props()->where('prop_name', $prop)->value('content');
    }
    public function children()
    {
        return $this->hasMany(Article::class, 'parent_id');
    }

    public function owner()
    {
        return $this->belongsTo(Article::class);
    }

    public function menuChildren()
    {
        return $this->children()->where('show_in_menu', 1)->where('active', 1)->orderBy('item_order')->get();
    }
    
    public function menuURL()
    {
       if (!empty($this->prop('url'))) {
         return $this->prop('url');
       }
       elseif (!empty($this->page_url)) {
         return "/". $this->page_url;
         
       }
       elseif (!empty($this->hierarchy_path)) {
         return "/". $this->hierarchy_path;
       }
       else {
         return "";
       }
    }
  
}
