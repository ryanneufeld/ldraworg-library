<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Vote;
use App\Models\VoteType;
use App\Models\User;
use App\Models\Part;

class VoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      gc_collect_cycles();
      foreach (Storage::disk('local')->allDirectories('library/unofficial') as $dir) {
        if (mb_strpos($dir,'images') !== false) continue;
        $files = Storage::disk('local')->files($dir);
        foreach ($files as $file) {
          if (pathinfo($file, PATHINFO_EXTENSION) == 'vote') {
            $partname = substr($file, 19,-5);
            $votefile = Storage::disk('local')->get($file);
            $votes = explode("\n", $votefile);
            $fasttrack = (strpos($votefile, 'PTadmin1=certify') !== false && 
                          strpos($votefile, 'PTadmin2=certify') !== false && 
                          (strpos($votefile, 'OrionP=certify') !== false || 
                           strpos($votefile, 'cwdee=certify') !== false));
            foreach ($votes as $vote) {
              if (empty($vote)) continue;
              $v = explode('=', trim($vote));
              if (!isset($v[1]) or $v[1] == 'novote') continue;
              $user = User::findByName($v[0]);
              $part = Part::where('filename', $partname)->whereRelation('release', 'short', 'unof')->first();
              if (isset($user) and isset($part)) {
                switch($v[1]) {
                  case 'certify':
                    if ($user->name == 'OrionP' or $user->name == 'cwdee') {
                      if ($fasttrack) {
                        $code = 'T';
                      }
                      else {
                        $code = 'A';
                      }
                    }
                    else {
                      $code = 'C';
                    }
                    break;
                  case 'hold':
                    $code = 'H';
                    break;
                }
                Vote::create(['user_id' => $user->id, 'part_id' => $part->id, 'vote_type_code' => $code]);
              }
            }
          }
        }
      }
    }
    
}
