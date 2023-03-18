<?php

namespace App\LDraw;

use App\LDraw\FileUtils;
use App\LDraw\MetaData;

use App\Models\User;
use App\Models\PartType;

class PartCheck {
  public static function validLine($line) {
    $line = trim(preg_replace('#\h{2,}#u', ' ', $line));
    if (empty($line)) return true;
    if (!array_key_exists($line[0], FileUtils::$line_patterns)) return false;
    return preg_match(FileUtils::$line_patterns[$line[0]], $line, $matches) > 0;
 }

  public static function checkDescription($file = '') {
    return FileUtils::getDescription($file) !== false;
  }

  public static function checkLibraryApprovedDescription($file = '') {
    $desc = FileUtils::getDescription($file);
    return $desc !== false && preg_match('#^[\x20-\x7E\p{Latin}\p{Han}\p{Hiragana}\p{Katakana}\pS]+$#', $desc, $matches);
  }

  public static function checkName($file = '') {
    return FileUtils::getName($file) !== false;
  }

  public static function checkLibraryApprovedName($file = '') {
    $name = FileUtils::getName($file);
    return $name !== false && preg_match('#^[a-z0-9_-]+(\.dat|\.png)$#', $name, $matches);
  }

  public static function checkNameAndPartType($file = '') {
    $name = FileUtils::getName($file);
    $name = str_replace('\\','/', $name);
    
    $type = FileUtils::getPartType($file);

    // Automatic fail if no Name:, LDRAW_ORG line, or DAT file has TEXTURE type
    if ($name === false || $type === false || stripos('Texture', $type['type']) !== false) return false;

    // Construct the name implied by the part type
    $pt = PartType::firstWhere('type', $type['type']);
    $folder = $pt->folder;
    if (strpos($folder, 'p/') !== false) {
      $f = substr($folder, strpos($folder, 'p/') + 2);
    }
    else {
      $f = substr($folder, strpos($folder, 'parts/') + 6);
    }
    $aname = $f . basename($name);
    
    return $name === $aname;
  }

  public static function checkAuthor($file = '') {
    return FileUtils::getAuthor($file) !== false;
  }

  public static function checkAuthorInUsers($file = '') {
    $author = FileUtils::getAuthor($file);
    return $author !== false && !empty(User::firstWhere('name',$author['user']) ?? User::firstWhere('realname',$author['realname']));
  }

  public static function checkPartType($file = '') {
    return FileUtils::getPartType($file) !== false;
  }

  public static function checkLicense($file = '') {
    return FileUtils::getLicense($file) !== false;
  }

  public static function checkLibraryApprovedLicense($file = '') {
    $license = FileUtils::getLicense($file);
    $liblic = array_flip(MetaData::getLibraryLicenses());
    return $license !== false && isset($liblic[$license]) && $liblic[$license] !== 'NonCA';
  }

  public static function checkLibraryBFCCertify($file = '') {
    $bfc = FileUtils::getBFC($file);
    return $bfc !== false && !empty($bfc['certwinding'] && $bfc['certwinding'] === 'CCW');
  }

  public static function checkCategory($file = '') {
    $cat = FileUtils::getCategory($file);
    return $cat !== false && in_array($cat['category'], MetaData::getCategories(), true);
  }

}
