<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasLicense
{
    public function license() {
        return $this->belongsTo(\App\Models\PartLicense::class, 'part_license_id', 'id');
    }

    public function scopeLicenseName(Builder $query, string $name)
    {
        return $query->whereRelation('license', 'name', $name);
    }

    public function scopeNotLicenseName(Builder $query, string $name)
    {
        return $query->whereRelation('license', 'name', '<>', $name);
    }
}