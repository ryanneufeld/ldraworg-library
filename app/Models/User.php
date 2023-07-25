<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;
use App\Models\Traits\HasLicense;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasLicense, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'realname',
        'password',
        'part_license_id',
        'forum_user_id',
    ];

    protected $with = ['license'];

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

//    protected $with = ['license'];
    
    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function part_events(): HasMany
    {
        return $this->hasMany(PartEvent::class);
    }

    public function part_histories(): HasMany
    {
        return $this->hasMany(PartHistory::class);
    }

    public function notification_parts(): BelongsToMany 
    {
        return $this->belongsToMany(Part::class, 'user_part_notifications', 'user_id', 'part_id');
    }
    
    public function scopeHasSubmittedPart(Builder $query, Part $part): void
    {
        $query->whereHas('part_histories', function (Builder $q) use ($part) 
        {
            $q->where('part_id', $part->id);
        });
    }

    public function togglePartNotification(Part $part): void 
    {
        $this->notification_parts()->toggle([$part->id]);
    }

    // Find by user or real name
    public static function findByName($name, $rname = ''): self
    {
        if (!empty($rname)) {
            return self::firstWhere('realname',$rname) ?? self::firstWhere('name',$name);
        }  
        else {
            return self::firstWhere('name',$name);
        }  
    }
    
    public static function ptadmin(): self
    {
        return self::firstWhere('name', 'PTadmin');
    }
    
    public function authorString(): string
    {
        if ($this->account_type === 1) {
            return $this->realname;
        }
        elseif ($this->account_type === 2) {
            return "[{$this->name}]";
        }
        else {
            return trim("{$this->realname} [{$this->name}]");
        }
    }

    public function historyString(): string 
    {
        if ($this->account_type === 1) {
            return "{{$this->realname}}";
        }
        elseif ($this->account_type === 2 && $this->name !== 'PTadmin') {
            return "{{$this->name}}";
        }
        else {
            return "[{$this->name}]";
        }
    }
    
    public function toString(): string 
    {
      return "0 Author: " . $this->authorString();
    }

    public function castVote(Part $part, VoteType $votetype): void 
    {
        $vote = $this->votes()->firstWhere('part_id', $part->id);
        if (!empty($vote)) {
            $vote->vote_type_code = $votetype->code;
            $vote->save();
        }
        else {
            Vote::create([
            'part_id' => $part->id,
            'user_id' => $this->id,
            'vote_type_code' => $votetype->code,
            ]);
        }
        $part->updateVoteData();
    }

    public function cancelVote(Part $part): void 
    {
        $this->votes()->where('part_id', $part->id)->delete();
        $part->updateVoteData();
    }

}
