<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Part;
use App\Models\PartEventType;
use App\Models\PartRelease;
use App\Models\VoteType;
use App\Models\User;
use RuntimeException;

class PartEvent extends Model
{
    protected $fillable = [
      'created_at',
      'initial_submit',
      'part_id',
      'user_id',
      'vote_type_code',
      'part_release_id',
      'part_event_type_id',
      'comment',
      'deleted_filename',
      'deleted_description',
      'moved_from_filename',
    ];

    protected $casts = [
      'initial_submit' => 'boolean',
    ];
  
    public function part_event_type() {
      return $this->belongsTo(PartEventType::class);
    }
    
    public function user() {
      return $this->belongsTo(User::class);
    }

    public function vote_type() {
      return $this->belongsTo(VoteType::class);
    }

    public function part() {
      return $this->belongsTo(Part::class);
    }
    
    public function release() {
      return $this->belongsTo(PartRelease::class, 'part_release_id');
    }
    
    public static function createFromType(string $type, User $user, Part $part, string $comment = null, string $vote_code = null, PartRelease $release = null, bool $init_submit = null): self {
      $type = PartEventType::firstWhere('slug', $type);
      if (is_null($type)) throw new RuntimeException("Part Event Type: $type, not found");
      $event = self::create([
        'comment' => $comment,
        'user_id' => $user->id,
        'part_id' => $part->id,
        'vote_type_code' => $vote_code,
        'part_event_type_id' => $type->id,
        'part_release_id' => $release->id ?? PartRelease::unofficial()->id,
      ]);
      $event->initial_submit = $init_submit;
      $event->save();
      return $event;
    }
}
