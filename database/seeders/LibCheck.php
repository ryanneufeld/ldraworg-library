<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\LDraw\FileUtils;

use App\Models\User;
use App\Models\PartCategory;
use App\Models\PartRelease;
use App\Models\PartHistory;
use App\Models\PartType;
use App\Models\PartTypeQualifier;
use App\Models\PartLicense;

class LibCheck extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    file_put_contents(storage_path('logs/laravel.log'),'');
    foreach (['unofficial', 'official'] as $lib) {
      foreach (Storage::disk('local')->allDirectories('library/' . $lib) as $dir) {
        if (strpos($dir,'images') !== false || strpos($dir,'models') !== false) continue;
        $files = Storage::disk('local')->files($dir);
//        $files = ['library/unofficial/parts/75544.dat', 'library/unofficial/parts/75544p01.dat', 'library/unofficial/parts/92910.dat'];
        foreach ($files as $file) {
          if (pathinfo($file, PATHINFO_EXTENSION) == 'dat') {
            gc_collect_cycles();
            $errors = [];
            $text = Storage::disk('local')->get($file);
            $text = mb_convert_encoding($text, 'UTF-8', ['ASCII','ISO-8859-1','UTF-8']);
            
            $desc = FileUtils::getDescription($text);
            $author = FileUtils::getAuthor($text);
            $name = FileUtils::getName($text);
            $pt = FileUtils::getPartType($text);
            $category = FileUtils::getCategory($text);
            $help = FileUtils::getHelp($text);
            $keywords = FileUtils::getKeywords($text);
            $cmdline = FileUtils::getCmdLine($text);
            $history = FileUtils::getHistory($text);
            $bfc = FileUtils::getBFC($text);
            $license = FileUtils::getLicense($text);
            $release = FileUtils::getRelease($text);
            
            if ($desc === false) $errors[] = "Malformed description";
            if ($author === false) $errors[] = "Invalid/Missing author";
            if ($name === false) $errors[] = "Invalid/Missing name";
            if ($category === false) $errors[] = "Invalid/Missing category";
            if ($license === false) $errors[] = "Invalid/Missing license";
            if ($bfc === false) $errors[] = "Invalid/Missing bfc";
            if ($pt === false) $errors[] = "Invalid/Missing !LDRAW_ORG";

            $cttext = FileUtils::cleanFileText(Storage::disk('local')->get($file));

            $ctdesc = FileUtils::getDescription($cttext);
            $ctauthor = FileUtils::getAuthor($cttext);
            $ctname = FileUtils::getName($cttext);
            $ctpt = FileUtils::getPartType($cttext);
            $ctcategory = FileUtils::getCategory($cttext);
            $cthelp = FileUtils::getHelp($cttext);
            $ctkeywords = FileUtils::getKeywords($cttext);
            $ctcmdline = FileUtils::getCmdLine($cttext);
            $cthistory = FileUtils::getHistory($cttext);
            $ctbfc = FileUtils::getBFC($cttext);
            $ctlicense = FileUtils::getLicense($cttext);
            $ctrelease = FileUtils::getRelease($cttext);

            if ($desc !== $ctdesc) $errors[] = "Cleaned text problem: description\n" . print_r($desc, true) . "\n" . print_r($ctdesc, true);
            if ($author !== $ctauthor) $errors[] = "Cleaned text problem: author" . print_r($author, true) . "\n" . print_r($ctauthor, true);
            if ($name !== $ctname) $errors[] = "Cleaned text problem: name" . print_r($name, true) . "\n" . print_r($ctname, true);
            if ($pt !== $ctpt) $errors[] = "Cleaned text problem: type" . print_r($pt, true) . "\n" . print_r($ctpt, true);
            if ($license !== $ctlicense) $errors[] = "Cleaned text problem: license" . print_r($license, true) . "\n" . print_r($ctlicense, true);
//            if ($help !== $cthelp) $errors[] = "Cleaned text problem: help" . print_r($help, true) . "\n" . print_r($cthelp, true);
            if ($category !== $ctcategory) $errors[] = "Cleaned text problem: category" . print_r($category, true) . "\n" . print_r($ctcategory, true);
//            if ($keywords !== $ctkeywords) $errors[] = "Cleaned text problem: keywords" . print_r($keywords, true) . "\n" . print_r($ctkeywords, true);
            if ($cmdline !== $ctcmdline) $errors[] = "Cleaned text problem: cmdline" . print_r($cmdline, true) . "\n" . print_r($ctcmdline, true);
            if ($bfc !== $ctbfc) $errors[] = "Cleaned text problem: bfc" . print_r($bfc, true) . "\n" . print_r($ctbfc, true);
            if ($release !== $ctrelease) $errors[] = "Cleaned text problem: release" . print_r($release, true) . "\n" . print_r($ctrelease, true);
//            if ($history !== $cthistory) $errors[] = "Cleaned text problem: history" . print_r($history, true) . "\n" . print_r($cthistory, true);

            if ($author !== false) {
              $user = User::findByName($author['user'], $author['realname']);
              if (empty($user)) $errors[] = "Author not found in DB";
            }  
            if ($pt !== false) {
              $type = User::findByName($author['user'], $author['realname']);
              $qual = PartTypeQualifier::findByType($pt['qual']);
              if (empty($type)) $errors[] = "Type not found in DB";
              if (!empty($pt['qual']) && empty($qual)) $errors[] = "Type qualifier not found in DB";
            }  
            if ($release !== false) {
              $rel = PartRelease::firstWhere('name', $release['release']) ?? PartRelease::firstWhere('short', 'unof');
              if ($rel->short == 'unof' && $pt['unofficial'] != 'Unofficial_') $errors[] = "Release not found in DB";
            }  
            if ($license !== false) {
              $lic = PartLicense::firstWhere('text', $license);
              if (empty($lic)) $errors[] = "License not found in DB";
            }  

            if (!empty($type) && ($type->name == 'Part' || ($type->name == 'Shortcut' && mb_strpos($name, "s\\") === false))) {
              $cat = PartCategory::findByName(FileUtils::getCategory($text));
              if (empty($cat)) $errors[] = "Category: $category not found in DB";
            }
            $history = FileUtils::getHistory($cttext, true);
            if ($history !== false) {
              if (count($history) != mb_substr_count($cttext, '0 !HISTORY')) {
                $errors[] = "!HISTORY line count Mismatch";
              }
              foreach($history as $hist) {
                if ($hist['user'] == -1) {
                  $errors[] = "!HISTORY user not found in DB";
                  break;
                }                  
              }  
            }
            if (count($errors) > 0) {
              Log::debug($file);
              Log::debug($errors);
            }              
          }
          
        }  
      }  
    }
    }
}
