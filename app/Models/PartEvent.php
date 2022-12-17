<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Part;
use App\Models\PartEventType;
use App\Models\PartRelease;
use App\Models\VoteType;
use App\Models\User;

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
    ];

    //protected $with = ['part_event_type', 'user', 'vote_type'];
    
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
    
    public static function createFromType($type, $user, $part, $comment = null, $vote_code = null, $release = null, $init_submit = null) {
      $type = PartEventType::firstWhere('slug', $type);
      if (is_null($release)) $release = PartRelease::unofficial();
      if ($vote_code == null && $type->short == 'review') return;
      self::create([
        'comment' => $comment,
        'initial_submit' => $init_submit,
        'user_id' => $user->id,
        'part_id' => $part->id,
        'part_event_type_id' => $type->id,
        'part_release_id' => PartRelease::unofficial()->id
      ]);
      
    }
}
