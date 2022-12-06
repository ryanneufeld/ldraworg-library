<?php

namespace App\LDraw;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

use App\LDraw\MetaData;
use App\Models\User;

class FileUtils
{
  // Master regualer expressions for parsing Library files
  public static $patterns = [
    'description' => '#^\h*0\h+(?P<description>.*?)\s*$#um',
    'name' => '#^\h*0\h+Name:\h+(?P<name>.*?)\s*$#um',
    'author' => '#^\h*0\h+Author:\h+((\[(?P<user2>[a-zA-Z0-9_.-]+)\])|(?P<realname>[^\[\]\r\n]+?)(\h+\[(?P<user>[a-zA-Z0-9_.-]+)\])?)\s*$#um',
    'type' => '#^\h*0\h+!LDRAW_ORG\h+(?P<unofficial>Unofficial_)?(?P<type>###PartTypes###)(\h+(?P<qual>###PartTypesQualifiers###))?(\h+((?P<releasetype>ORIGINAL|UPDATE)(\h+(?P<release>\d{4}-\d{2}))?))?\s*$#um',
    'category' => '#^\h*0\h+!CATEGORY\h+(?P<category>.*?)\s*$#um',
    'license' => '#^\h*0\h+!LICENSE\h+(?P<license>.*?)\s*$#um',
    'help' => '#^\h*0\h+!HELP\h+(?P<help>.*?)\s*$#um',
    'keywords' => '#^\h*0\h+!KEYWORDS\h+(?P<keywords>.*?)\s*$#um',
    'bfc' => '#^\h*0\h+BFC\h+(?P<bfc>CERTIFY(\h+(?P<certwinding>CCW|CW))?|NOCERTIFY|(CCW|CW)|NOCLIP|CLIP(\h+(?<clipwinding>CCW|CW))?)\s*$#um',
    'cmdline' => '#^\h*0\h+!CMDLINE\h+(?P<cmdline>.*?)\s*$#um',
    'history' => '#^\h*0\h+!HISTORY\s+(?P<date>\d\d\d\d-\d\d-\d\d)\s+[\[{](?P<user>[\w\s\/\\.-]+)[}\]]\s+(?P<comment>.*?)\s*$#um',
    'textures' => '#^\s*0\s+!TEXMAP\s+(START|NEXT)\s+(PLANAR|CYLINDRICAL|SPHERICAL)\s+([-\.\d]+\s+){9,11}(?P<texture1>.*?\.png)(\s+GLOSSMAP\s+(?P<texture2>.*?\.png))?\s*$#um',
    'subparts' => '#^\s*(0\s+!\:\s+)?1\s+((0x)?\d+\s+){1}([-\.\d]+\s+){12}(?P<subpart>.*?\.(dat|ldr))\s*$#um',
  ];

  public static $line_patterns = [
    '0' => '#^\h*0(?:\h*)(.*)?\s*$#um',
    '1' => '#^\h*1\h+(?P<color>0x2\d{6}|\d+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+(?P<subpart>[\/a-z0-9_.\\\\-]+)\s*?$#um',
    '2' => '#^\h*2\h+(?P<color>0x2\d{6}|\d+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\s*#um',
    '3' => '#^\h*3\h+(?P<color>0x2\d{6}|\d+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\s*#um',
    '4' => '#^\h*4\h+(?P<color>0x2\d{6}|\d+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\s*#um',
    '5' => '#^\h*5\h+(?P<color>0x2\d{6}|\d+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\h+([\d.-]+)\s*#um',
  ];
  
  public static $allowed_header_metas = ['Name:', 'Author:', '!LDRAW_ORG', '!LICENSE', '!HELP', 'BFC', '!CATEGORY', '!KEYWORDS', '!CMDLINE', '!HISTORY'];
  public static $allowed_body_metas = ['!TEXTURE', '!:', 'BFC', '//'];

  // Trim all lines, remove multi-spaces (except the first line), and change to Unix line ending
  public static function storageFileText($file) {
    if (!empty($file)) {
      $file = preg_replace('#\R#us', "\n", $file);
      $file = preg_replace('#\n{3,}#us', "\n\n", $file);
      $file = explode("\n", $file);
      foreach($file as $index => &$line) {
        if ($index === array_key_first($file)) continue;
        $line = preg_replace('#\h+#u', ' ', trim($line));
        if (!empty($line) && $line[0] === '1') $line = strtolower($line);
      }
      return implode("\n", $file);
    }
    else {
      return false;
    }
  }
  
  // There are several badly encoded UTF-8 files in the library.
  // Hopefully this prevents this from happening
  public static function cleanFileText($text, $forceunofficial = false, $author_from_db = false) {
    $text = mb_convert_encoding($text, 'UTF-8', ['ASCII','ISO-8859-1','UTF-8']);
    $text = preg_replace('#\R#us', "\n", $text);
    $text = preg_replace('#\n{3,}#us', "\n\n", $text);
    $text = explode("\n", $text);
    foreach($text as $index => &$line) {
      if ($index === array_key_first($text)) continue;
      $line = preg_replace('#\h+#u', ' ', trim($line));
      if (!empty($line) && $line[0] === '1') $line = mb_strtolower($line);
    }
    $text = implode("\n", $text);
    $text = self::cleanHeader($text, $forceunofficial, $author_from_db);
    
    return $text;
  }
  
  // Change to DOS line endings
  public static function downloadFileText($file) {
    return str_replace("\n", "\r\n", $file);
  }

  public static function headerEndLine($file) {
    $file = preg_split("#\R#u", $file);
    $i = 1;
    while ($i < count($file)) {
      if (empty($file[$i]) || ($file[$i][0] === '0' && in_array(strtok(mb_substr($file[$i], 1), " "), self::$allowed_header_metas, true))) {
        $i++;
      }
      else {
        break;
      }
    }
    return $i-1;
  }

  public static function getHeader($file) {
    $filearr = preg_split("#\R#u", $file);
    return implode("\n", array_slice($filearr, 0, self::headerEndLine($file) + 1));
  }

  public static function setHeader($file, $header) {
    $filearr = preg_split("#\R#u", $file);
    return $header . "\n" . implode("\n", array_slice($filearr, self::headerEndLine($file) + 1));
  }

  // This function does no validation and can produce an invalid header
  public static function cleanHeader($file, $forceunofficial = false, $author_from_db = false) {
    $header = '0 ' . self::getDescription($file) . "\n";
    $header .= '0 Name: ' . mb_strtolower(self::getName($file)) . "\n";

    $aline = '';
    $author = self::getAuthor($file);
    if (!empty($author)) {
      if ($author_from_db) {
        $user = User::findByName($author['user'], $author['realname']);
        if (!empty($user)) $aline = $user->authorString();          
      }
      else {      
        if (!empty($author['realname'])) $aline = $author['realname'];
        if (!empty($author['user'])) $aline .= ' [' . $author['user'] . ']';
        $aline = trim($aline);
      }  
    }
    $header .= "0 Author: $aline\n" ;

    $parttype = self::getPartType($file);
    $release = self::getRelease($file);
    if(!empty($parttype)) {
      if ($forceunofficial) $parttype['unofficial'] = "Unofficial_";
      $ptline = $parttype['unofficial'] . $parttype['type'];
      if (!empty($parttype['qual'])) $ptline .= ' ' . $parttype['qual'];
    }
    else {
      $ptline = '';
    }
    if (!empty($ptline) && !empty($release) && !$forceunofficial) {
      $ptline .= ' ' . $release['releasetype'];
      if ($release['releasetype'] == 'UPDATE') $ptline .= ' ' . $release['release'];
    }
    $header .= "0 !LDRAW_ORG $ptline\n";

    $header .= '0 !LICENSE ' . self::getLicense($file) . "\n\n";

    $help = self::getHelp($file);
    if (!empty($help)) {
      foreach($help as $hline) {
        $header .= "0 !HELP $hline\n";
      }
      $header .= "\n";
    }

    $bfc = self::getBFC($file);
    if (!empty($bfc) && !empty($bfc['certwinding'])) {
      $header .= '0 BFC CERTIFY ' . $bfc['certwinding'] . "\n\n";
    }
    else {
      $header .= "0 BFC NOCERTIFY\n\n";
    }

    $category = self::getCategory($file);
    if (!empty($category) && $category['meta'] === true) 
      $header .= "0 !CATEGORY " . $category['category'] . "\n";

    $keywords = self::getKeywords($file);
    if (!empty($keywords)) {
      $kwline = "0 !KEYWORDS ";
      foreach ($keywords as $index => $kw) {
        $kwline .= $kw;
        if (mb_strlen($kwline) > 80) {
          $header .= "$kwline\n";
          $kwline = "0 !KEYWORDS ";
        }
        else {
          if ($index !== array_key_last($keywords)) $kwline .= ", ";
        }
      }
      $header .= "$kwline\n\n";
    }
    elseif (!empty($category) && $category['meta']) {
      $header .= "\n";
    }

    $cmdline = self::getCmdLine($file);
    if (!empty ($cmdline)) $header .= "0 !CMDLINE $cmdline\n\n";

    $history = self::getHistory($file);
    if (!empty($history)) {
      usort($history, function ($a, $b) {
        return strtotime($a['date']) <=> strtotime($b['date']);
      });
      foreach($history as $hist) {
        $histline = "0 !HISTORY " . $hist['date'] . " ";
        $user = User::findByName($hist['user'], $hist['user']);
        if (!is_null($user)) {
          $histline .= $user->historyString() . " ";
        }
        else {
          $histline .= '[' . $hist['user'] . "] ";
        }
        $header .= $histline . $hist['comment'] . "\n";
      }
    }

    return self::setHeader($file, $header);
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
//      Log::debug($file);
      return false;
    }
  }

  public static function getRelease($file) {
    $pattern = str_replace('###PartTypes###', implode('|', MetaData::getPartTypes(true)), self::$patterns['type']);
    $pattern = str_replace('###PartTypesQualifiers###', implode('|', MetaData::getPartTypeQualifiers(true)), $pattern);
    if (preg_match($pattern, $file, $matches)) {
      $matches = array_merge(['releasetype' => '', 'release' => ''], $matches);
      if ($matches['releasetype'] == 'ORIGINAL') $matches['release'] = 'original';
      return ['releasetype' => $matches['releasetype'], 'release' => $matches['release']];
    }
    else {
      return false;
    }
  }

  // Only returns the first valid BFC statement
  public static function getBFC($file) {
//    $file = self::storageFileText($file);
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
      empty(trim($matches['category'])) ? $cat = false : $cat = ['category' => trim($matches['category']), 'meta' => true];
    }
    elseif($description = self::getDescription($file)) {
      $cat = ['category' => str_replace(['~','|','=','_'], '', mb_strstr($description, " ", true)), 'meta' => false];
    }
    else {
      $cat = false;
    }
    return $cat;
  }

  public static function getHistory($file, $get_user_ids = false) {
    if (preg_match_all(self::$patterns['history'], $file, $matches, PREG_SET_ORDER) > 0) {
      $history = [];
      $aliases = MetaData::getAuthorAliases();
      foreach ($matches as $match) {
        if ($get_user_ids) {
          if (array_key_exists($match['user'], $aliases)) $match['user'] = $aliases[$match['user']];
          $user = User::findByName($match['user'], $match['user']);
          if (!empty($user)) {
            $uid = $user->id;
          }  
          else {
//            Log::debug($file);
//            Log::debug($match['user']);
            $uid = -1;
          }            
        }
        else {
          $uid = $match['user'];
        }
        $history[] = ['date' => $match['date'], 'user' => $uid, 'comment' => $match['comment']];
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
        $line = explode(',', $line);
        foreach($line as $word) {
          $word = preg_replace('#\h+#u', ' ', trim($word)); 
          $word = preg_replace('#^[\'"](.*)[\'"]$#u', '$1', trim($word));
          if (!empty($word)) $keywords[] = $word;
        }  
      }
      $keywords = array_unique($keywords);
      return empty($keywords) ? false : $keywords;
    }
    else {
      return false;
    }
  }

  public static function getSubparts($file) {
    $result = ['subparts' => [], 'textures' => []];
    if (preg_match_all(self::$patterns['subparts'], $file, $matches) > 0) {
      array_walk($matches['subpart'], function(&$arg){
        $arg = mb_strtolower($arg);
      });
      $result['subparts'] = array_unique($matches['subpart']);
    }
    if (preg_match_all(self::$patterns['textures'], $file, $matches) > 0) {
      $result['textures'] = $matches['texture1'];
      if (isset($matches['texture2'])) $result['textures'] = array_merge($result['textures'], $matches['texture2']);
      array_walk($result['textures'], function(&$arg){
        $arg = mb_strtolower($arg);
      });
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
