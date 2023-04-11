<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Models\PartEventType;
use App\Models\PartEvent;
use App\Models\User;
use App\Models\Part;
use App\Models\VoteType;
use App\Models\PartRelease;

class PartEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $eventtypes = PartEventType::all()->pluck('id', 'slug')->all();
      $votetypes = VoteType::all()->pluck('code', 'short')->all();
      $patterns = [
        'date' => '#^At (?<date>\w\w\w \w\w\w \d{1,2} \d\d\:\d\d\:\d\d \d\d\d\d)#um',
        'comment' => '#Comments\:\n(?<comment>.*)$#us',
        'submit_user' => '#^Submitted by\: (?<user>.*?)( proxy=.*?)?$#um',
        'reviewer_user' => '#^Reviewer\: (?<user>.*?)$#um',
        'vote' => '#^Certification\: (?<vote>hold|novote|certify|fasttrack)$#um',
        'rename' => '#part \'(.*)\' was renamed to \'(.*)\'\.$#um',
        'edit' => '#a Parts Tracker Admin edited the header.$#um',
      ];
      foreach (Storage::disk('local')->allDirectories('library/unofficial') as $dir) {
        if (strpos($dir,'images') !== false) continue;
        $files = Storage::disk('local')->files($dir);
        foreach ($files as $file) {
          if (pathinfo($file, PATHINFO_EXTENSION) == 'meta') {
            $partname = substr($file, 19,-5);
            $part = Part::findByName($partname, true);
            if (empty($part)) continue;
            $metafile = Storage::disk('local')->get($file);
            $events = explode(str_repeat('=', 70) . "\n", $metafile);
            foreach ($events as $event) {
              if (empty($event)) continue;
              
              $event = trim($event);
              $event = preg_replace('#\R#us', "\n", $event);
              $event = preg_replace('#\n{3,}#us', "\n\n", $event);
              $event = preg_replace('#\h+#us', ' ', $event);
              
              preg_match($patterns['date'], $event, $matches);
              $date = date_format(date_create($matches['date']), 'Y-m-d H:i:s');

              if (preg_match($patterns['submit_user'], $event, $matches)) {
                $eid = $eventtypes['submit'];
                $user = User::findByName(trim($matches['user']), trim($matches['user']));
                if (preg_match($patterns['comment'], $event, $matches)) {
                  $comment = $matches['comment'];
                }
                else {
                  $comment = null;
                }
                $vc = null;                
              }
              elseif (preg_match($patterns['reviewer_user'], $event, $matches)) {
                $user = User::findByName(trim($matches['user']), trim($matches['user'])) ?? User::findByName('unknown');
                preg_match($patterns['vote'], $event, $matches);
                if ($matches['vote'] == 'novote') {
                  $vc = null;
                  $eid = $eventtypes['comment'];
                }
                else {                
                  $vc = $votetypes[$matches['vote']];
                  if ($vc == 'C' && ($user->name == 'OrionP' || $user->name == 'cwdee')) $vc = $votetypes['admincertify'];
                  $eid = $eventtypes['review'];
                }
                
                if (preg_match($patterns['comment'], $event, $matches)) {
                  $comment = $matches['comment'];
                }
                else {
                  $comment = null;
                }  
              }
              elseif (preg_match($patterns['rename'], $event, $matches)) {
                $eid = $eventtypes['rename'];
                $user = User::ptadmin();
                $vc = null;
                $comment = $matches[0];
              }
              elseif (preg_match($patterns['edit'], $event, $matches)) {
                $eid = $eventtypes['edit'];
                $user = User::ptadmin();
                $vc = null;
                $comment = null;
              }
              
              if (!isset($user) || $eid == '') dd($event, $partname);
              if (!is_null($comment)) $comment = nl2br(htmlspecialchars($comment));
              PartEvent::create([
                'created_at' => $date,
                'part_event_type_id' => $eid,
                'initial_submit' => null,
                'part_id' => $part->id,
                'user_id' => $user->id,
                'vote_type_code' => $vc,
                'part_release_id' => PartRelease::unofficial()->id,
                'comment' => $comment,
              ]);
              unset($user);
              $eid = '';
            }
            unset($part);
            unset($metafile);
            unset($events);
          }  
        }
      }

      $parts = Part::with('events')->whereRelation('type','format','png')->whereRelation('release','short','unof')->lazy();
      foreach ($parts as $part) {
        $uid = $part->events()->where('part_event_type_id', 2)->oldest()->first()->user_id;
        $part->user_id = $uid;
        $part->save();
      }

    }
}
