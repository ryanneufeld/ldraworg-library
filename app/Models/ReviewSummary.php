<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ReviewSummaryItem;

class ReviewSummary extends Model
{
    protected $fillable = [
        'header',
        'order',
    ];
    
    public function items() {
        return $this->hasMany(ReviewSummaryItem::class, 'review_summary_id', 'id');
    }
}
