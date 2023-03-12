<?php

namespace App\LDraw;

use Illuminate\Database\Eloquent\Builder;

use App\LDraw\FileUtils;
use App\LDraw\PartCheck;

use App\Models\PartType;
use App\Models\User;
use App\Models\Part;

// This class is intended to be a bridge between the Laravel form requests
// and a generic "error" checker to allow for code reuse.
class LDrawFileValidate {
  public static function ValidAuthor($text){
    $results = [];
    if (!PartCheck::checkAuthor($text)) {
      $results[] = __('partcheck.missing', ['attribute' => 'Author:']);
    }
    elseif (!PartCheck::checkAuthorInUsers($text)) {
      $results[] = __('partcheck.author.registered', ['value' => 'Author:']);
    }
    return $results;
  }
  
  public static function ValidCategory($text) {
    $desc = FileUtils::getDescription($text);
    $type = FileUtils::getPartType($text);
    $cat = FileUtils::getCategory($text);

    if (($type['type'] == 'Part' || $type['type'] == 'Shortcut') && !PartCheck::checkCategory($text)) {
      $results[] = __('partcheck.category.invalid', ['value' => $cat['category']]);
    }  
    elseif ($cat['category'] == 'Moved' && $desc[0] != '~') {
      $results[] = __('partcheck.category.movedto');
    }
    return $results;
  }
  
  public static function ValidDescription($text) {
    $name = strtolower(FileUtils::getName($text));
    $type = FileUtils::getPartType($text);
    $inPartFolder = $type !== false && ($type['type'] == 'Part' || $type['type'] == 'Shortcut');
    
    $desc = FileUtils::getDescription($text);

    $results = [];
    if (!PartCheck::checkDescription($text)) {
      $results[] = __('partcheck.description.missing');
    }
    elseif (!PartCheck::checkLibraryApprovedDescription($text)) {
      $results[] = __('partcheck.description.invalidchars');
    }
    elseif ($inPartFolder && 
      ((substr($name, strrpos($name, '.dat') - 3, 1) == 'p' || substr($name, strrpos($name, '.dat') - 2, 1) == 'p') && 
       (mb_substr($desc, mb_strrpos($desc, ' ') + 1) != 'Pattern' && mb_substr($desc, mb_strrpos($desc, ' ') + 1) != '(Obsolete)'))) {
      $results[] = __('partcheck.description.patternword');
    }  
    return $results;    
  }
  
  public static function ValidHistory($text) {
    $history = FileUtils::getHistory($text);
    $results = [];
    if (!empty($history)) {
      if (count($history) < mb_substr_count($text, '!HISTORY')) {
        $results[] = __('partcheck.history.invalid');
      }
      else {
        foreach($history as $hist) {
          if (empty(User::firstWhere('name', $hist['user']) ?? User::firstWhere('realname', $hist['user']))) {
            $results[] = __('partcheck.history.author', ['value' => $hist['user'], 'date' => $hist['date']]);
          }
        }
      }
    }  
    return $results;    
  }
  
  public static function ValidKeywords($text) {
    $name = strtolower(FileUtils::getName($text));
    $type = FileUtils::getPartType($text);
    $inPartFolder = $type !== false && ($type['type'] == 'Part' || $type['type'] == 'Shortcut');
    
    $keywords = FileUtils::getKeywords($text);
    $isPattern = $inPartFolder && ((substr($name, strrpos($name, '.dat') - 3, 1) == 'p' || substr($name, strrpos($name, '.dat') - 2, 1) == 'p' || substr($name, strrpos($name, '.dat') - 2, 1) == 'd'));
    $results = [];
    if ($isPattern) {
      if (empty($keywords)) {
        $results[] = __('partcheck.keywords');
      }
      else {
        $setfound = false;
        foreach ($keywords as $word) {
          if (mb_strtolower(strtok($word, " ")) == 'set') {
            $setfound = true;
            break;
          } 
        }
        if (!$setfound) $results[] = __('partcheck.keywords');
      }  
    }         
    return $results;    
  }
  
  public static function ValidLicense($text) {
    $results = [];
    if (!PartCheck::checkLicense($text)) {
      $results[] = __('partcheck.missing', ['attribute' => '!LICENSE']);
    }  
    elseif (!PartCheck::checkLibraryApprovedLicense($text)) {
      $results[] = __('partcheck.license.approved');
    }  
    return $results;        
  }
  
  public static function ValidLines($text) {
    $headerend = FileUtils::headerEndLine($text);
    $text = explode("\n", $text);
    $results = [];
    foreach ($text as $index => $line) {
      if (!PartCheck::validLine($line)) {
        $results[] = __('partcheck.line.invalid', ['value' => $index + 1]);
      }
      elseif (!empty($line) && $index > $headerend && $line[0] === 0 && !in_array(strtok(mb_substr($line, 1), " "), FileUtils::$allowed_body_metas, true)) {
        $results[] = __('partcheck.line.invalidmeta', ['value' => $index + 1]);
      }  
    }      
    return $results;        
  }
  
  public static function ValidName($text, $filename = null, $input_pt = null) {
    $results = [];

    if (isset($filename)) {
      $filename = basename(strtolower($filename));
      if(!PartCheck::checkLibraryApprovedName("0 Name: $filename")) {
        $results[] = __('partcheck.name.invalidchars');
      }
      elseif($filename[0] == 'x') {
        $results[] = __('partcheck.name.xparts');
      }  
    }

    if (isset($input_pt)) $type = PartType::find($input_pt);
    
    if (!empty($text)) {
      $name = str_replace('\\','/', FileUtils::getName($text));

      if (!PartCheck::checkName($text)) {
        $results[] = __('partcheck.missing', ['attribute' => 'Name:']);
      }
      elseif (isset($filename) && basename($name) !== $filename) {
        $results[] = __('partcheck.name.mismatch', ['value' => basename($name)]);
      }
      elseif (isset($input_pt) && ('parts/' . $name !== $type->folder . basename($name)) && ('p/' . $name !== $type->folder . basename($name))) {
        $results[] = __('partcheck.folder', ['attribute' => 'Name:', 'value' => $name, 'folder' => $type->folder]);
      }
    }
    return $results;        
  }
  
  public static function ValidPartType($text, $input_pt = null) {
    $results = [];
    $ftype = FileUtils::getPartType($text);
    $part_type = PartType::firstWhere('type', $ftype['type']);
    if(isset($input_pt)) $form_type = PartType::find($input_pt);
    
    $name = str_replace('\\','/', FileUtils::getName($text));
    $desc = FileUtils::getDescription($text);
    $dtag = empty($desc) ? false : $desc[0];
    
    if (!PartCheck::checkPartType($text)) {
      $results[] = __('partcheck.missing', ['attribute' => '!LDRAW_ORG']);
    }
    else {
      if (!PartCheck::checkNameAndPartType($text)) {
        $results[] = __('partcheck.type.path', ['name' => $name, 'type' => $ftype['type']]);
      }
      if (isset($input_pt) && !empty($form_type) && !empty($part_type) && $form_type->folder != $part_type->folder) {
        $results[] = __('partcheck.folder', ['attribute' => '!LDRAW_ORG', 'value' => $ftype['type'], 'folder' => $form_type->folder]);
      }
      if ($ftype['type'] == 'Subpart' && $dtag != "~") {
        $results[] = __('partcheck.type.subpartdesc');
      }
      
      //Check qualifiers
      if ($ftype['qual'] == 'Physical_Color') {
        $results[] = __('partcheck.type.phycolor');
      }
      elseif ($ftype['qual'] == 'Alias' && $ftype['type'] != 'Shortcut' && $ftype['type'] != 'Part') {
        $results[] = __('partcheck.type.alias');
      }
      elseif ($ftype['qual'] == 'Alias' && $dtag != '=') {
        $results[] = __('partcheck.type.aliasdesc');
      }
      elseif ($ftype['qual'] == 'Flexible_Section' && $ftype['type'] != 'Part') {
       $results[] = __('partcheck.type.flex');
      }
      elseif ($ftype['qual'] == 'Flexible_Section' && !preg_match('#^[a-z0-9_-]+?k[a-z0-9]{2}(p[a-z0-9]{2,3})?\.dat#', $name, $matches)) {
        $results[] = __('partcheck.type.flexname');
      }
    }  
    return $results;            
  }
  
  public static function ValidSubmitAuthor($text, $user_id) {
    $author = FileUtils::getAuthor($text);
    $fuser = User::findByName($author['user'], $author['realname']);
    if (isset($fuser) && $fuser->id === $user_id) return;
    $h = FileUtils::getHistory($text);
  }
  
  public static function historyEventsCrossCheck(Part $part) {
    $id = $part->id;
    $eusers = User::whereNotIn('name', ['OrionP', 'cwdee', 'sbliss', 'PTadmin'])->whereHas('part_events', function (Builder $query) use ($id) {
      $query->whereRelation('part_event_type','slug','submit')->whereRelation('release','short','unof')->where('part_id', $id);
    })->get();
    $husers = $part->editHistoryUsers();
    if (!$husers->find($part->user->id)) $husers->add($part->user);
    $ediff = $eusers->diff($husers);
    if ($ediff->count() > 0) {
      return [__('partcheck.history.eventmismatch', ['users' => implode(', ', $ediff->pluck('name')->all())])];
    }
    else {
      return [];
    }
  }
}

