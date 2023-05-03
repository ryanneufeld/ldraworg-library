<?php

namespace App\LDraw;

use App\Models\PartType;
use App\Models\User;

class PartCheck
{
  /**
   * validLine
   *
   * @param string $line
   * 
   * @return bool
   */
  public static function validLine(string $line): bool
  {
    $line = trim(preg_replace('#\h{2,}#u', ' ', $line));
    if (empty($line)) {
      return true;
    }
    if (is_null(config('ldraw.patterns.line_type_' . $line[0]))) {
      return false;
    }

    return preg_match(config('ldraw.patterns.line_type_' . $line[0]), $line, $matches) > 0;
  }

  /**
   * checkDescription
   *
   * @param string $file
   * 
   * @return bool
   */
  public static function checkDescription(string $file): bool
  {
    return FileUtils::getDescription($file) !== false;
  }

  /**
   * checkLibraryApprovedDescription
   *
   * @param string $file
   * 
   * @return bool
   */
  public static function checkLibraryApprovedDescription(string $file): bool
  {
    $desc = FileUtils::getDescription($file);

    return $desc !== false && preg_match(config('ldraw.patterns.library_approved_description'), $desc, $matches);
  }

  /**
   * checkName
   *
   * @param string $file
   * 
   * @return bool
   */
  public static function checkName(string $file): bool
  {
    return FileUtils::getName($file) !== false;
  }

  /**
   * checkLibraryApprovedName
   *
   * @param string $file
   * 
   * @return bool
   */
  public static function checkLibraryApprovedName(string $file): bool
  {
    $name = FileUtils::getName($file);

    return $name !== false && preg_match(config('ldraw.patterns.library_approved_name'), $name, $matches);
  }

  /**
   * checkNameAndPartType
   *
   * @param string $file
   * 
   * @return bool
   */
  public static function checkNameAndPartType(string $file): bool
  {
    $name = FileUtils::getName($file);
    $name = str_replace('\\', '/', $name);
    $type = FileUtils::getPartType($file);
    $pt = PartType::firstWhere('type', $type['type'] ?? '');
    // Automatic fail if no Name:, LDRAW_ORG line, or DAT file has TEXTURE type
    if ($name === false || $type === false || empty($pt) || $pt->format == 'png') {
      return false;
    }

    // Construct the name implied by the part type
    $aname = str_replace(['p/', 'parts/'], '', $pt->folder . basename($name));

    return $name === $aname;
  }

  /**
   * checkAuthor
   *
   * @param string $file
   * 
   * @return bool
   */
  public static function checkAuthor(string $file): bool
  {
    return FileUtils::getAuthor($file) !== false;
  }

  /**
   * checkAuthorInUsers
   *
   * @param string $file
   * 
   * @return bool
   */
  public static function checkAuthorInUsers(string $file): bool
  {
    $author = FileUtils::getAuthor($file);

    return $author !== false && ! empty(User::firstWhere('name', $author['user']) ?? User::firstWhere('realname', $author['realname']));
  }

  /**
   * checkPartType
   *
   * @param string $file
   * 
   * @return bool
   */
  public static function checkPartType(string $file): bool
  {
    return FileUtils::getPartType($file) !== false;
  }

  /**
   * checkLicense
   *
   * @param string $file
   * 
   * @return bool
   */
  public static function checkLicense(string $file): bool
  {
    return FileUtils::getLicense($file) !== false;
  }

  /**
   * checkLibraryApprovedLicense
   *
   * @param string $file
   * 
   * @return bool
   */
  public static function checkLibraryApprovedLicense(string $file): bool
  {
    $license = FileUtils::getLicense($file);
    $liblic = \App\Models\PartLicense::firstWhere('text', $license);
    return $license !== false && ! empty($liblic) && $liblic->name !== 'NonCA';
  }

  /**
   * checkLibraryBFCCertify
   *
   * @param string $file
   * 
   * @return bool
   */
  public static function checkLibraryBFCCertify(string $file): bool
  {
    $bfc = FileUtils::getBFC($file);

    return $bfc !== false && ! empty($bfc['certwinding'] && $bfc['certwinding'] === 'CCW');
  }

  /**
   * checkCategory
   *
   * @param string $file
   * 
   * @return bool
   */
  public static function checkCategory(string $file): bool
  {
    $cat = FileUtils::getCategory($file);

    return $cat !== false && in_array($cat['category'], config('ldraw.categories'), true);
  }
}
