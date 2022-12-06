<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

use Spatie\Permission\Traits\HasRoles;
use App\Models\Part;
use App\Models\Vote;
use App\Models\PartEvent;
use App\Models\PartHistory;
use App\Models\PartLicense;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function parts()
    {
        return $this->hasMany(Part::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function part_events()
    {
        return $this->hasMany(PartEvent::class);
    }

    public function part_histories()
    {
        return $this->hasMany(PartHistory::class);
    }

    public function license()
    {
        return $this->belongsTo(PartLicense::class, 'part_license_id', 'id');
    }
    
    // Find by user or real name
    public static function findByName($name, $rname = '') {
      if (!empty($rname)) {
        return self::firstWhere('realname',$rname) ?? self::firstWhere('name',$name);
      }  
      else {
        return self::firstWhere('name',$name);
      }  
    }
    
    public static function ptadmin() {
      return self::firstWhere('name', 'PTadmin');
    }
    
    public function authorString() {
      if ($this->hasRole('Legacy User')) {
        return $this->realname;
      }
      elseif ($this->hasRole('Synthetic User')) {
        return "[{$this->name}]";
      }
      else {
        return trim("{$this->realname} [{$this->name}]");
      }
    }

    public function historyString() {
      if ($this->hasRole('Legacy User')) {
        return "{{$this->realname}}";
      }
      elseif ($this->hasRole('Synthetic User')) {
        return "{{$this->name}}";
      }
      else {
        return "[{$this->name}]";
      }
    }
    
    public function toString() {
      return "0 Author: " . $this->authorString();
    }
}
