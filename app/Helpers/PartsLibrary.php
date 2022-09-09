<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

use App\Models\Part;
use App\Models\PartType;
use App\Models\PartTypeQualifier;

class PartsLibrary
{
  // Master regualar expressions for parsing Library files
  public static $patterns = [
    'description' => '#^\s?0\s+(?P<description>.*?)[\r\n]#ius',
    'name' => '#\n\s?0\s+Name\:\s+(?P<name>.*?)[\r\n]#ius',
    'author' => '#\n\s?0\s+Author\:\s+(?P<author>.*?)[\r\n]#ius',
    'type' => '#\n\s?0\s+!LDRAW_ORG\s+(Unofficial_)?(?P<type>###PartTypes###)(\s+(?P<qual>###PartTypesQualifiers###))?(\s+((?P<releasetype>ORIGINAL|UPDATE)(\s+(?P<release>\d{4}-\d{2}))?))?[\r\n]#ius',
    'category' => '#\n\s?0\s+!CATEGORY\s+(?P<category>.*?)[\r\n]#ius',
    'history' => '#\s?0\s+!HISTORY\s+(?P<date>\d\d\d\d-\d\d-\d\d)\s+[\[{](?P<user>.*?)[}\]]\s+(?P<comment>.*?)\s?[\r\n\x]#ius',
    'textures' => '#\n\s?0\s+!TEXMAP\s+(START|NEXT)\s+(PLANAR|CYLINDRICAL|SPHERICAL)\s+([-\.\d]+\s+){9,11}(?P<texture1>.*?\.png)(\s+GLOSSMAP\s+(?P<texture2>.*?\.png)])?#ius',
    'subparts' => '#\n\s?(0\s+!\:\s+)?1\s+([-\.\d]+\s+){13}(?P<subpart>.*?\.dat)#ius',
  ];
  
  public static $default_texture_data = "0 TEXMAP Image <FILENAME>\r\n0 Author: [PTadmin]\r\n0 !LDRAW_ORG Unofficial_Texture\r\n";
  
  public static $known_author_aliases = [
    'unknown' => 'CA User',
    'LEGO Universe Team' => 'The LEGO Universe Team',
    'simlego' => 'Tore_Eriksson',
    'Valemar' => 'rhsexton',
  ];
  
  public static function officialParts($fresh = false) {
    if ($fresh) Cache::forget('official-parts-list');
    return Cache::remember('official-parts-list', 3600, function () {
      return Part::with(['type', 'officialPart'])->where('unofficial', false);
    });  
  }

  public static function unofficialParts($fresh = false) {
    if ($fresh) Cache::forget('unofficial-parts-list');
    return Cache::remember('unofficial-parts-list', 3600, function () {
      return Part::with(['type', 'officialPart'])->where('unofficial', true)->orderBy('filename')->get();
    });  
  }

  public static function unofficialStatusSummary($fresh = false) {
    if ($fresh) Cache::forget('unofficial-status-summary');
    return Cache::remember('unofficial-status-summary', 3600, function () {
      return Part::where('unofficial', true)->get()->pluck('vote_sort')->countBy()->sortKeys()->all();
    });  
  }
    
  public static function descriptionFromFilestring($file) {
    if (preg_match(self::$patterns['description'], $file, $matches)) {
      return trim($matches['description']);
    }
    else {
      return false;
    }
    
  }

  public static function nameFromFilestring($file) {
    if (preg_match(self::$patterns['name'], $file, $matches)) {
      return trim($matches['name']);
    }
    else {
      return false;
    }
  }

  public static function authorFromFilestring($file) {
    if (preg_match(self::$patterns['author'], $file, $matches)) {
      $author = trim($matches['author']);
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
      return ['name' => $uname, 'realname' => $rname];
    }
    else {
      return false;
    }
  }

  public static function typeFromFilestring($file) {
    $pattern = str_replace('###PartTypes###', implode('|', PartType::all()->pluck('type')->all()), self::$patterns['type']);
    $pattern = str_replace('###PartTypesQualifiers###', implode('|', PartTypeQualifier::all()->pluck('type')->all()), $pattern);
    if (preg_match($pattern, $file, $matches)) {
      //preg_match optional pattern bug workaround
      $matches = array_merge(['type' => '', 'qual' => ''], $matches);
      return ['type' => $matches['type'], 'qual' => $matches['qual']];
    }
    else {
      return false;
    }
  }

  public static function releaseFromFilestring($file) {
    $pattern = str_replace('###PartTypes###', implode('|', PartType::all()->pluck('type')->all()), self::$patterns['type']);
    $pattern = str_replace('###PartTypesQualifiers###', implode('|', PartTypeQualifier::all()->pluck('type')->all()), $pattern);
    if (preg_match($pattern, $file, $matches)) {
      $matches = array_merge(['releasetype' => '', 'release' => ''], $matches);
      return ['releasetype' => $matches['releasetype'], 'release' => $matches['release']];
    }
    else {
      return false;
    }
  }

  public static function categoryFromFilestring($file) {
    if (preg_match(self::$patterns['category'], $file, $matches)) {
      return $matches['category'];
    }
    elseif($description = self::descriptionFromFilestring($file)) {
      return str_replace(['~','|','=','_'], '', mb_strstr($description, " ", true));
    }
    else {
      return false;
    }
  }

  public static function historyFromFilestring($file) {
    if (preg_match_all(self::$patterns['history'], $file, $matches, PREG_SET_ORDER) > 0) {
      $history = [];
      foreach ($matches as $match) {
        $history[] = ['date' => $match['date'], 'user' => $match['user'], 'comment' => $match['comment']];
      }
      return $history;
    }
    else {
      return false;
    }
  }

  public static function subpartsFromFilestring($file) {
    $result = ['subparts' => [], 'textures' => []];
    if (preg_match_all(self::$patterns['subparts'], $file, $matches) > 0) {
      $result['subparts'] = array_unique($matches['subpart']);
    }
    if (preg_match_all(self::$patterns['textures'], $file, $matches) > 0) { 
      $result['textures'] = $matches['texture1'];
      if (isset($matches['texture2'])) $result['textures'] = array_merge($result['textures'], $matches['texture2']);
      $result['textures'] = array_unique($result['textures']);
    }
    if (isset($result['subparts']) || isset($result['textures'])) {
      return $result;
    }
    else {
      return false;
    }
  }
    
}
