<?php

namespace App\LDraw;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

use App\LDraw\MetaData;

class FileUtils
{
  // Master regualer expressions for parsing Library files
  public static $patterns = [
    'description' => '#^\s*0\s+(?P<description>.*?)\s*$#um',
    'name' => '#^\s*0\s+Name:\s+(?P<name>.*?)\s*$#um',
    'author' => '#^\s*0\s+Author:\h+((\[(?P<user2>[a-zA-Z0-9_.-]+)\])|(?P<realname>[^\[\]\r\n]+?)(\s+\[(?P<user>[a-zA-Z0-9_.-]+)\])?)\s*$#um',
    'type' => '#^(\s+)?0\s+!LDRAW_ORG\s+(?P<unofficial>Unofficial_)?(?P<type>###PartTypes###)(\s+(?P<qual>###PartTypesQualifiers###))?(\s+((?P<releasetype>ORIGINAL|UPDATE)(\s+(?P<release>\d{4}-\d{2}))?))?(\s+)?$#um',
    'category' => '#^\s*0\s+!CATEGORY\s+(?P<category>.*?)\s*$#um',
    'license' => '#^\s*0\s+!LICENSE\s+(?P<license>.*?)\s*$#um',
    'help' => '#^\s*0\s+!HELP\s+(?P<help>.*?)\s*$#um',
    'keywords' => '#^\s*0\s+!KEYWORDS\s+(?P<keywords>.*?)\s*$#um',
    'bfc' => '#^\s*0\s+BFC\s+(?P<bfc>CERTIFY(\s+(?P<certwinding>CCW|CW))?|NOCERTIFY|(CCW|CW)|NOCLIP|CLIP(\s+(?<clipwinding>CCW|CW))?)\s*$#um',
    'cmdline' => '#^\s*0\s+!CMDLINE\s+(?P<cmdline>.*?)\s*$#um',
    'history' => '#^\s*0\s+!HISTORY\s+(?P<date>\d\d\d\d-\d\d-\d\d)\s+[\[{](?P<user>[\w\s\/\\.-]+)[}\]]\s+(?P<comment>.*?)\s*$#um',
    'textures' => '#^\s*0\s+!TEXMAP\s+(START|NEXT)\s+(PLANAR|CYLINDRICAL|SPHERICAL)\s+([-\.\d]+\s+){9,11}(?P<texture1>.*?\.png)(\s+GLOSSMAP\s+(?P<texture2>.*?\.png))?\s*$#um',
    'subparts' => '#^\s*(0\s+!\:\s+)?1\s+((0x)?\d+\s+){1}([-\.\d]+\s+){12}(?P<subpart>.*?\.(dat|ldr))\s*$#um',
  ];

  // Trim all lines, remove multi-spaces (except the first line), and change to Unix line ending
  public static function storageFileText($file) {
    if (!empty($file)) {
      $file = preg_replace('#[\n\r]+#us', '\n', $file);
      $file = explode("\n", $file);
      $first_line = trim($file[0]);
      foreach($file as $line) {
        $line = trim($line);
        $line = preg_replace('#[ \t]+#u', ' ', $line);
      }
      $file[0] = $first_line;
    }
    return $file;
  }

  // Change to DOS line endings
  public static function downloadFileText($file) {
    return str_replace("\n", "\r\n", $file);
  }

  public static function getHeader($file) {
    $file = preg_split("/\r\n|\n|\r/", $file);
    $i = 0;
    while ($i < count($file) && (empty($file[$i]) || trim($file[$i]) == '' || (trim($file[$i])[0] == '0' && strpos($file[$i],'!TEXMAP') === false))) $i++;
    return implode("\n", array_slice($file, 0, $i));
  }

  public static function getDescription($file) {
    if (preg_match(self::$patterns['description'], $file, $matches)) {
      return empty(trim($matches['description'])) ? false : trim($matches['description']);
    }
    else {
      return false;
    }
  }

  public static function getName($file) {
    if (preg_match(self::$patterns['name'], $file, $matches)) {
      return empty(trim($matches['name'])) ? false : trim($matches['name']);
    }
    else {
      return false;
    }
  }

  public static function getLicense($file) {
    if (preg_match(self::$patterns['license'], $file, $matches)) {
      return empty(trim($matches['license'])) ? false : trim($matches['license']);
    }
    else {
      return false;
    }
  }

  public static function getCmdLine($file) {
    if (preg_match(self::$patterns['cmdline'], $file, $matches)) {
      return empty(trim($matches['cmdline'])) ? false : trim($matches['cmdline']);
    }
    else {
      return false;
    }
  }

  public static function getAuthor($file) {
    if (preg_match(self::$patterns['author'], $file, $matches)) {
      //preg_match optional pattern bug workaround
      $matches = array_merge(['user2' => '', 'realname' => '', 'user' => ''], $matches);
      Log::debug($matches);
      if (empty(trim($matches['user2'])) && empty(trim($matches['realname'])) && empty(trim($matches['user']))) return false;
      if (empty($matches['realname'])) $matches['user'] = $matches['user2'];
      return ['realname' => $matches['realname'], 'user' => $matches['user']];
    }
    else {
      return false;
    }
  }

  public static function getPartType($file) {
    $pattern = str_replace('###PartTypes###', implode('|', MetaData::getPartTypes(true)), self::$patterns['type']);
    $pattern = str_replace('###PartTypesQualifiers###', implode('|', MetaData::getPartTypeQualifiers(true)), $pattern);
    if (preg_match($pattern, $file, $matches)) {
      //preg_match optional pattern bug workaround
      $matches = array_merge(['unofficial' => '', 'type' => '', 'qual' => ''], $matches);
      return ['unofficial' => $matches['unofficial'], 'type' => $matches['type'], 'qual' => $matches['qual']];
    }
    else {
      return false;
    }
  }

  public static function getRelease($file) {
    $pattern = str_replace('###PartTypes###', implode('|', MetaData::getPartTypes(true)), self::$patterns['type']);
    $pattern = str_replace('###PartTypesQualifiers###', implode('|', MetaData::getPartTypeQualifiers(true)), $pattern);
    if (preg_match($pattern, $file, $matches)) {
      $matches = array_merge(['releasetype' => '', 'release' => ''], $matches);
      return ['releasetype' => $matches['releasetype'], 'release' => $matches['release']];
    }
    else {
      return false;
    }
  }

  public static function getBFC($file) {
    if (preg_match(self::$patterns['bfc'], $file, $matches)) {
      //preg_match optional pattern bug workaround
      $matches = array_merge(['bfc' => '', 'certwinding' => '', 'clipwinding' => ''], $matches);
      return ['bfc' => preg_replace('#\s+#u', ' ', $matches['bfc']), 'certwinding' => $matches['certwinding'], 'clipwinding' => $matches['clipwinding']];
    }
    else {
      return false;
    }
  }

  public static function getCategory($file) {
    if (preg_match(self::$patterns['category'], $file, $matches)) {
      return empty(trim($matches['category'])) ? false : trim($matches['category']);
    }
    elseif($description = self::getDescription($file)) {
      return str_replace(['~','|','=','_'], '', mb_strstr($description, " ", true));
    }
    else {
      return false;
    }
  }

  public static function getHistory($file) {
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

  public static function getHelp($file) {
    if (preg_match_all(self::$patterns['help'], $file, $matches) > 0) {
      return $matches['help'];
    }
    else {
      return false;
    }
  }

  public static function getKeywords($file) {
    if (preg_match_all(self::$patterns['keywords'], $file, $matches) > 0) {
      $keywords = [];
      foreach ($matches['keywords'] as $line) {
        $line = explode(',',preg_replace('#\s+#u', ' ', trim()));
        $keywords = array_unique(array_merge($line, $keywords));
      }
      return empty($keywords) ? false : $keywords;
    }
    else {
      return false;
    }
  }

  public static function getSubparts($file) {
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
