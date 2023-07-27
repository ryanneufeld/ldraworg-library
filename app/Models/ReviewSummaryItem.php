<?php

namespace App\Models;

use App\Models\Traits\HasPart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewSummaryItem extends Model
{
    use HasPart;

    protected $fillable = [
        'order',
        'review_summary_id',
        'part_id',
        'heading',
    ];
    
    protected $with = ['part'];

    public function review_summary(): BelongsTo
    {
        return $this->belongsTo(ReviewSummary::class);
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
