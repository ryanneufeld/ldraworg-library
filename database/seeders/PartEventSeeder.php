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
      $votetypes = VoteType::all()->pluck('code', 'name')->all();
      foreach (Storage::disk('public')->allDirectories('unofficial') as $dir) {
        if (strpos($dir,'images') !== false) continue;
        $files = Storage::disk('public')->files($dir);
        foreach ($files as $file) {
          if (pathinfo($file, PATHINFO_EXTENSION) == 'meta') {
            $partname = substr($file, 11,-5);
            $part = Part::firstWhere('filename', $partname);
            if (!isset($part)) continue;
            $metafile = Storage::disk('public')->get($file);
            $events = explode(str_repeat('=', 70) . "\n", $metafile);
            foreach ($events as $event) {
              if (empty($event)) continue;
              unset($comment);
              $date = '';
              unset($user);
              unset($eventtype);
              unset($votetype);
              $date_pattern = '#At\s+(\S+\s+\S+\s+\d+\s+\d+\:\d+\:\d+\s+\d+)#ius';
              $comment_pattern = '#Comments\:\n+([\s\S]*?)\z#ius';
              $submit_user_pattern = '#Submitted by\:\s(.*?)(\s+proxy=.*?)?\n#ius';
              $reviewer_user_pattern = '#Reviewer\:\s(.*?)\n#ius';
              $vote_pattern = '#Certification\:\s(.+?)\n#ius';
              $rename_pattern = '#part \'(.*)\' was renamed to \'(.*)\'\.#ius';
              $edit_pattern = '#a Parts Tracker Admin edited the header#ius';
              
              if (preg_match($date_pattern, $event, $matches)) {
                $date = date_format(date_create($matches[1]), 'Y-m-d H:i:s');
              }  
              if (preg_match($comment_pattern, $event, $matches)) {
                $comment = preg_replace('#\n{3,}#ius', "\n", $matches[1]);
                $comment = preg_replace('#\n$#ius', '', $comment);
              }
              
              if (mb_strpos($event,'the file was initially submitted.') !== false || mb_strpos($event,'a new version of the file was submitted.') !== false) {
                $eventtypeid = $eventtypes['submit'];
                $initsubmit = mb_strpos($event,'the file was initially submitted') !== false;
                if (preg_match($submit_user_pattern, $event, $matches)) {
                  $uname = preg_replace('#.*(\t.*)#iu','',$matches[1]);
                  if ($uname == 'simlego') $uname = 'Tore_Eriksson';
                  if ($uname == 'David Merryweather') $uname = 'hazydavy';
                  if ($uname == 'Valemar') $uname = 'rhsexton';
                  $user = User::firstWhere('name', $uname) ?? User::firstWhere('name','Non-CA User');
                }
                else {
                  $user = $part->user;
                }
              }
              else if (preg_match($reviewer_user_pattern, $event, $matches)) {
                if ($matches[1] == 'simlego') $matches[1] = 'Tore_Eriksson';
                if ($matches[1] == 'David Merryweather') $matches[1] = 'hazydavy';
                $user = User::firstWhere('name', $matches[1]) ?? User::firstWhere('name','Non-CA User');
                $eventtypeid = $eventtypes['review'];
                if (preg_match($vote_pattern, $event, $matches) && $eventtypeid == $eventtypes['review']) {
                  $vote = trim($matches[1]);
                  $votecode = $votetypes[ucfirst($vote)] ?? null;
                  if (isset($user) && isset($votecode) && $votetypes['Certify'] == $votecode && ($user->name == 'OrionP' || $user->name == 'cwdee')) {
                    $votecode = $votetypes['Admin Certify'];
                  }  
                  elseif (strpos($matches[1], 'fastrack')) {
                    $votecode = $votetypes['Admin Fast Track'];
                  }
                  elseif (!isset($votecode)) {
                    $eventtypeid = $eventtypes['comment'];
                 }
                }  
              }  
              else if (preg_match($rename_pattern, $event, $matches)) {
                $comment = $matches[0];
                $eventtypeid =  $eventtypes['rename'];
                $user = User::firstWhere('name', 'PTAdmin'); 
              }
              else if (preg_match($edit_pattern, $event, $matches)) {
                $eventtypeid = $eventtypes['edit'];
                $user = User::firstWhere('name', 'PTAdmin'); 
              }
              if (!isset($user)) {
                Log::debug("User not defined",['part'=>$partname,'event'=>$event]);
                $user = User::firstWhere('name', 'Non-CA User');
              }  
              if ($eventtypeid > 0) {
                $new_event = new PartEvent;
                $new_event->created_at = $date;
                $new_event->part()->associate($part);
                $new_event->user()->associate($user);
                $new_event->vote_type_code = $votecode ?? NULL;
                $new_event->release()->associate(PartRelease::firstWhere('name','unofficial'));
                $new_event->comment = $comment ?? NULL;
                $new_event->part_event_type_id = $eventtypeid;
                $new_event->initial_submit = $initsubmit ?? NULL;
                $new_event->save();
                
                if ($initsubmit && $part->type->type == 'Texmap' && $part->user->name == 'PTadmin') {
                  $part->user()->associate($user);
                  $part->save();
                }
              }  
            }
          }  
        }
      }
    }
}
