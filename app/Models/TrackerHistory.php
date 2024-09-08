<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class TrackerHistory extends Model
{
    protected function casts(): array
    {
        return [
            'history_data' => AsArrayObject::class,
        ];
    }
}
