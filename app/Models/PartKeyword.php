<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartKeyword extends Model
{
    protected $fillable = [
      'keyword',
    ];

    public $timestamps = false;

    public function parts() {
      return $this->belongsToMany(self::class, 'parts_part_keywords', 'part_keyword_id', 'part_id');
    }

    public static function findByKeyword($keyword) {
      return self::firstWhere('keyword', $keyword);
    }  
    
    public static function findByKeywordOrCreate($keyword) {
      return self::findByKeyword($keyword) ?? self::Create(['keyword' => $keyword]);
    }  
}
