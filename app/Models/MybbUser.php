<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MybbUser extends Model
{
    protected $table = 'mybb_users';

    protected $primaryKey = 'uid';

    public $timestamps = false;

    protected $connection = 'mybb';
}
