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
      foreach (Storage::disk('public')->allDirectories('unofficial') as $dir) {
        if (mb_strpos($dir,'images') !== false) continue;
        $files = Storage::disk('public')->files($dir);
        foreach ($files as $file) {
          if (pathinfo($file, PATHINFO_EXTENSION) == 'vote') {
            $partname = substr($file, 11,-5);
            $votefile = Storage::disk('public')->get($file);
            $votes = explode("\n", $votefile);
            $fasttrack = (strpos($votefile, 'PTadmin1=certify') !== false && 
                          strpos($votefile, 'PTadmin2=certify') !== false && 
                          (strpos($votefile, 'OrionP=certify') !== false || 
                           strpos($votefile, 'cwdee=certify') !== false));
            foreach ($votes as $vote) {
              if (empty($vote)) continue;
              $v = explode('=', $vote);
              if (!isset($v[1]) or $v[1] == 'novote') continue;
              $user = User::firstWhere('name',$v[0]);
              $part = Part::where('filename', $partname)->where('unofficial', true)->first();
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
                $votetype = VoteType::find($code);
                if (isset($votetype)) {
                  $partvote = new Vote;
                  $partvote->user()->associate($user);
                  $partvote->part()->associate($part);
                  //$partvote->vote_type_code = $votetype->code;
                  $partvote->type()->associate($votetype);
                  $partvote->saveQuietly();
                }
              }
            }
          }
        }
      }
    }
    
}
