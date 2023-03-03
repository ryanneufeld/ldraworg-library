<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Storage;

use App\Models\Part;
use App\Models\PartRelease;
use App\Models\PartEvent;
use App\Models\PartHistory;
use App\Models\User;

class MakePartRelease implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ids;
    protected User $user;

    public $uniqueFor = 3600;
    public $timeout = 3600;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Array $ids, User $user)
    {
      $this->ids = $ids;
      $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $next = PartRelease::next();
      $note = Storage::disk('library')->get('official/models/Note' . $next['short'] . 'CA.txt');
      $release = PartRelease::create(['name' => $next['name'], 'short' => $next['short'], 'notes' => $note]);
      $partslist = [];
      foreach (Part::whereIn('id', $this->ids)->lazy() as $part) {
       // Update release for event released parts
        PartEvent::whereRelation('release', 'short', 'unof')->where('part_id', $part->id)->update(['part_release_id' => $release->id]);
  
        // Post a release event     
        PartEvent::createFromType('release', $this->user, $part, 'Release ' . $release->name, null, $release);
  
        // Add history line
        PartHistory::create(['user_id' => $this->user->id, 'part_id' => $part->id, 'comment' => 'Official Update ' . $release->name]);
        $part->refreshHeader();
  
        // Part is an official update
        if (!is_null($part->official_part_id)) {
          $opart = Part::find($part->official_part_id);
          $text = $part->get();
  
          // Update the official part
          if ($opart->isTexmap()) {
            $opart->body->body = $part->get();
            $opart->body->save();
            foreach($opart->history() as $h) {
              $h->delete();
            }
            foreach($part->history()->latest()->get() as $h) {
              PartHistory::create(['created_at' => $h->created_at, 'user_id' => $h->user_id, 'part_id' => $opart->id, 'comment' => $h->comment]);
            }
          } 
          else {
            $opart->fillFromText($text, false, $release);
          }
          $opart->unofficial_part_id = null;
          $opart->save();
  
          // Update events with official part id
          PartEvent::where('part_release_id', $release->id)->where('part_id', $part->id)->update(['part_id' => $opart->id]);
   
          $part->delete();
        }
        // Part is a new part
        else {
          // Make unofficial part official
          $part->release()->associate($release);
          $part->notification_users()->sync([]);
          $part->refreshHeader();
          $part->vote_sort = 1;
          $part->vote_summary = null;
          $part->uncertified_subpart_count = 0;
          $part->save();
  
          // Update parts list
          if ($part->type->folder == 'parts/')
            $partslist[] = [$part->description, $part->filename];
        }
      }
      $release->part_list = $partslist;
      $release->save();
    }
}
