<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

use App\Models\Part;
use App\Models\PartRelease;
use App\Models\User;
use App\Models\PartCategory;
use App\Models\PartHistory;
use App\Models\PartType;
use App\Models\PartTypeQualifier;

class PartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      foreach (['official','unofficial'] as $lib) {
        foreach (Storage::disk('public')->allDirectories($lib) as $dir) {
          if (strpos($dir,'images') !== false || strpos($dir,'models') !== false) continue;
          $files = Storage::disk('public')->files($dir);
          foreach ($files as $file) {
            $partname = mb_substr($file, mb_strpos($file, 'official') + 9);
            unset($category);
            if (pathinfo($file, PATHINFO_EXTENSION) == 'dat') {
              $filestring = Part::getPartFile($file);
              
              if (preg_match('#^\s?0\s+(.*?)[\r\n]#ius', $filestring, $matches)) $description = $matches[1];

              //Part Author
              if (preg_match('#\s?0\s+Author\:\s+(.*?)[\r\n]#ius', $filestring, $matches)) {
                $author = trim($matches[1]);
                $uname_start = mb_strpos($author, '[');
                if ($uname_start !== false) {
                  if ($uname_start == 0) {
                    $rname = '';
                    $uname = mb_substr($author, 1, -1);
                  }
                  else {
                    $uname = mb_substr($author, $uname_start + 1, mb_strpos($author, ']') - $uname_start - 1);
                    $rname = trim(mb_substr($author, 0, mb_strpos($author, '[')));
                  }
                }
                else {
                  $rname = $author;
                  $uname = '';
                }
                
                $user = User::firstWhere('realname',$rname);
                
                if (!isset($user)) {
                  $user = User::firstWhere('name',$uname);
                }
                if (!isset($user)) {
                  Log::debug ("Author not found: '{$matches[1]}', '$rname', '$uname'");
                }  
              }
              else {
                  Log::debug ("Author not found: '$filestring'");
                  exit;
              }  
              //Part Category
              $category_pattern = '#\s?0\s+!CATEGORY\s+(.*?)[\r\n]#ius';
              if ($dir != 'official/parts' && substr($dir, 2) != 'official/parts') {
                $category = null;
              }  
              elseif (preg_match($category_pattern, $filestring, $matches)) {
                $category = PartCategory::firstWhere('category', $matches[1]);
              }
              else {
                $cat_str = str_replace(['~','|','=','_'], '', mb_strstr($description, " ", true));
                $category = PartCategory::firstWhere('category', $cat_str);
              }
              
              $type_pattern = '#\s?0\s+!LDRAW_ORG\s+(Unofficial_)?(?P<type>Part|Subpart|Primitive|8_Primitive|48_Primitive|Shortcut)(\s+(?P<op_qual>Alias|Physical_Colour|Flexible_Section))?(\s+((?P<release>ORIGINAL|UPDATE)(\s+(?P<releasename>\d{4}-\d{2}))?))?[\r\n]#ius';
              if (preg_match($type_pattern, $filestring, $matches)) {
                //preg_match optional pattern bug workaround
                $matches = array_merge(['type' => '', 'op_qual' => '', 'release' => '', 'releasename' => ''], $matches);
                
                $type = PartType::firstWhere('type', $matches['type']);
                $op_qual = PartTypeQualifier::firstWhere('type', $matches['op_qual']);
                if ($lib == 'unofficial') {
                   $release = PartRelease::firstWhere('short','unof');
                }  
                elseif ($matches['release'] == 'ORIGINAL') {
                  $release = PartRelease::firstWhere('short','original');
                }
                elseif ($matches['release'] == 'UPDATE') {
                  $releasename = $matches['releasename'];
                  $release = PartRelease::firstWhere('name',$releasename);
                }
                if (!isset($release)) Log::debug ("Type not found", ['matches' => $matches]);
                if (!isset($type)) Log::debug ("Release not found", ['matches' => $matches]);
              }
            }
            elseif (pathinfo($file, PATHINFO_EXTENSION) == 'png') {
              $description = 'TEXMAP Image ' . $partname;
              $user = User::firstWhere('realname','PTadmin');
              $filestring = base64_encode(Storage::disk('public')->get($file));
              $type = PartType::firstWhere('type', 'Texmap');
              $release = PartRelease::firstWhere('short','2201');
              $category = null;
            }
            else {
              continue;
            }
            
            $part = new Part;
            $part->filename = $partname;
            $part->data_filename = $partname;
            $part->description = $description;
            if (isset($category)) $part->category()->associate($category);
            $part->user()->associate($user);
            $part->release()->associate($release);
            $part->type()->associate($type);
            if (isset($op_qual)) $part->type_qualifier()->associate($op_qual);
            $part->release()->associate($release);
            $part->unofficial = $lib == 'unofficial';
            if ($part->unofficial) {
              $offpart = Part::where('filename', $part->filename)->where('unofficial', false)->first();
              if (isset($offpart)) {
                $part->officialPart()->associate($offpart);
              }  
            }
            $part->save();
            $part->updateHistory();
          }
          
        }  
      }
    }    
}
