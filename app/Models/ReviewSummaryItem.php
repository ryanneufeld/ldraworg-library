<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ReviewSummary;

class ReviewSummaryItem extends Model
{
    protected $fillable = [
        'order',
        'review_summary_id',
        'part_id',
        'heading',
    ];
    
    protected $with = ['part'];

    public function review_summary()
    {
        return $this->belongsTo(ReviewSummary::class);
    }

    public function part() 
    {
        return $this->belongsTo(Part::class);
    }
    
    public function toString(): string
    {
        if (is_null($this->part)) {
            if (is_null($this->heading)) {
                return '/';
            } else {
                return "/ {$this->heading}";
            }
        } else {
            return $this->part->filename;
        }
    }
}
