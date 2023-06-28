<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasPartRelease
{
    public function release() {
        return $this->belongsTo(\App\Models\PartRelease::class, 'part_release_id', 'id');
    }

    public function scopeOfficial(Builder $query) {
        return $query->whereNotNull('part_release_id');
    }

    public function scopeUnofficial(Builder $query) {
        return $query->whereNull('part_release_id');
    }
}