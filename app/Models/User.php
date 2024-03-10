<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;
use App\Models\Traits\HasLicense;
use App\Models\Traits\HasParts;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, HasParts, HasLicense, HasRoles, Notifiable;

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
        'is_legacy',
        'is_synthetic',
        'is_ptadmin'
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
        'profile_settings' => 'array',
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function part_events(): HasMany
    {
        return $this->hasMany(PartEvent::class);
    }

    public function part_history(): HasMany
    {
        return $this->hasMany(PartHistory::class);
    }

    public function notification_parts(): BelongsToMany 
    {
        return $this->belongsToMany(Part::class, 'user_part_notifications', 'user_id', 'part_id');
    }
    
    public function authorString(): Attribute
    {       
        return Attribute::make(
            get: function(mixed $value, array $attributes) {
                if ($attributes['is_legacy'] === true) {
                    return $attributes['realname'];
                } else if ($attributes['is_ptadmin'] === true) {
                    return "[{$attributes['name']}]";
                } else {
                    return "{$attributes['realname']} [{$attributes['name']}]";
                }        
            }
        );
    }
    
    public function scopeFromAuthor(Builder $query, string $username, ?string $realname = null): void
    {
        $query->where(function (Builder $q) use ($username, $realname) {
            $q->orWhere('realname', $realname)->orWhere('name', $username);
        });
    }
    
    public function togglePartNotification(Part $part): void 
    {
        $this->notification_parts()->toggle([$part->id]);
    }

    public static function ptadmin(): self
    {
        return self::firstWhere('name', 'PTadmin');
    }

    public function historyString(): string 
    {
        if ($this->is_synthetic === true) {
            return "{{$this->realname}}";
        }
        if ($this->is_legacy === true) {
            return "{{$this->name}}";
        }

        return "[{$this->name}]";
    }
    
    public function toString(): string 
    {
      return "0 Author: " . $this->author_string;
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
        $part->refresh();
        $part->updateVoteData();
    }

    public function cancelVote(Part $part): void 
    {
        $this->votes()->where('part_id', $part->id)->delete();
        $part->updateVoteData();
    }

}
