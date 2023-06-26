<?php

namespace App\LDraw;

use App\Models\PartType;
use App\Models\User;
use App\Models\Part;

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


  public static function checkFile(\Illuminate\Http\UploadedFile $file): ?array
  {
    $fileext = strtolower($file->getClientOriginalExtension());
    $filemime = $file->getMimetype();
    //Check valid file format
    if (!in_array($fileext, ['dat', 'png'])) {
        $errors[] = ['error' => 'partcheck.fileformat', 'args' => ['attribute' => 'extension', 'value' => $fileext]];
        return $errors;
    } elseif (!in_array($filemime, ['text/plain', 'image/png']) || ($fileext == 'dat' && $filemime != 'text/plain') || ($fileext == 'png' && $filemime != 'image/png')) {
        $errors[] = ['error' => 'partcheck.fileformat', 'args' => ['attribute' => 'format', 'value' => $fileext]];
        return $errors;
    }

    // PNG ends validation here, otherwise clean file text.
    if ($filemime == 'text/plain') {
        $text = FileUtils::cleanFileText($file->get(), false, true);
    } else {
        $text = '';
    }
    
    $filename = basename($file->getClientOriginalName());
    $name = FileUtils::getName($text);
    if (! PartCheck::checkLibraryApprovedName("0 Name: $filename")) {
        $errors[] = ['error' => 'partcheck.name.invalidchars'];
    } elseif ($filename[0] == 'x') {
        $errors[] = ['error' => 'partcheck.name.xparts'];
    }    
    if ($name && basename(str_replace('\\', '/', $name)) !== $filename) {
        $errors[] = ['error' => 'partcheck.name.mismatch', 'args' => ['value' => basename($name)]];
    }

    $headerend = FileUtils::headerEndLine($text);
    $text = explode("\n", $text);
    foreach ($text as $index => $line) {
      if (! PartCheck::validLine($line)) {
          $errors[] = ['error' => 'partcheck.line.invalid', 'args' => ['value' => $index + 1]];
      } elseif (! empty($line) && $index > $headerend && $line[0] == '0' && ! in_array(explode(' ', trim($line))[1], config('ldraw.allowed_metas.body'), true)) {
          $errors[] = ['error' => 'partcheck.line.invalidmeta', 'args' => ['value' => $index + 1]];
      }
    }  
    return $errors ?? null;
  }

  public static function checkHeader(\Illuminate\Http\UploadedFile $file, array $data): ?array
  {
    // PNG file have no header.
    if ($file->getMimetype() == 'text/plain') {
        $text = FileUtils::cleanFileText($file->get());
    } else {
        return null;
    }

    // Ensure header required metas are present
    $missing = [
        'description' => PartCheck::checkDescription($text),
        'name' => PartCheck::checkName($text),
        'author' => PartCheck::checkAuthor($text),
        'ldraw_org' => PartCheck::checkPartType($text),
        'license' => PartCheck::checkLicense($text),
    ];
    $exit = false;
    foreach ($missing as $meta => $status) {
        if ($status == false) {
            $errors[] = ['error' => 'partcheck.missing', 'args' => ['attribute' => $meta]];
            $exit = true;
        }
    }
    if ($exit) {
        return $errors;
    }

    $type = FileUtils::getPartType($text);
    $pt = PartType::firstWhere('type', $type['type']);
    $name = str_replace('\\', '/', FileUtils::getName($text));
    $desc = FileUtils::getDescription($text);

    // Description Checks
    if (! PartCheck::checkLibraryApprovedDescription($text)) {
        $errors[] = ['error' => 'partcheck.description.invalidchars'];
    }

    $isPattern = preg_match('#^[a-z0-9_-]+?p[a-z0-9]{2,3}\.dat$#i', $name, $matches);
    $hasPatternText = preg_match('#^.*?\sPattern(\s\((Obsolete|Needs Work)\))?$#ui', $desc, $matches);
    if ($pt->folder == 'parts/' && $isPattern && !$hasPatternText) {
        $errors[] = ['error' => 'partcheck.description.patternword'];
    }
    // Note: Name: checks are done in the LDrawFile rule
    // Author checks
    $author = FileUtils::getAuthor($text);
    if (! PartCheck::checkAuthorInUsers($text)) {
        $errors[] = ['error' => 'partcheck.author.registered', 'args' => ['value' => $author['realname']]];
    }

    // !LDRAW_ORG Part type checks
    $form_type = PartType::find($data['part_type_id']);
    $dtag = $desc[0];

    if (! PartCheck::checkNameAndPartType($text)) {
        $errors[] = ['error' => 'partcheck.type.path', 'args' => ['name' => $name, 'type' => $pt->type]];
    }
    if ($form_type->folder != $pt->folder) {
        $errors[] = ['error' => 'partcheck.folder', 'args' => ['attribute' => '!LDRAW_ORG', 'value' => $pt->type, 'folder' => $form_type->folder]];
    }
    if ($pt->type == 'Subpart' && $dtag != '~') {
        $errors[] = ['error' => 'partcheck.type.subpartdesc'];
    }

    //Check qualifiers
    if (!empty($type['qual'])) {
        $pq = \App\Models\PartTypeQualifier::firstWhere('type', $type['qual']);
        switch ($pq->type) {
            case 'Physical_Colour':
                $errors[] = ['error' => 'partcheck.type.phycolor'];
                break;
            case 'Alias':
                if ($pt->type != 'Shortcut' && $pt->type != 'Part') {
                    $errors[] = ['error' => 'partcheck.type.alias'];
                }
                if ($dtag != '=') {
                    $errors[] = ['error' => 'partcheck.type.aliasdesc'];
                }
                break;
            case 'Flexible_Section':
                if ($pt->type != 'Part') {
                    $errors[] = ['error' => 'partcheck.type.flex'];
                }
                if (! preg_match('#^[a-z0-9_-]+?k[a-z0-9]{2}(p[a-z0-9]{2,3})?\.dat#', $name, $matches)) {
                    $errors[] = ['error' => 'partcheck.type.flexname'];
                }
                break;
        }
    }
    // !LICENSE checks
    if (! PartCheck::checkLibraryApprovedLicense($text)) {
        $errors[] = ['error' => 'partcheck.license.approved'];
    }
    // BFC CERTIFY CCW Check
    if (! PartCheck::checkLibraryBFCCertify($text)) {
        $errors[] = ['error' => 'partcheck.bfc'];
    }
    // Category Check
    $cat = FileUtils::getCategory($text);
    if (($pt->type == 'Part' || $pt->type == 'Shortcut') && !PartCheck::checkCategory($text)) {
        $errors[] = ['error' => 'partcheck.category.invalid', 'args' => ['value' => $cat['category']]];
    } elseif (($pt->type == 'Part' || $pt->type == 'Shortcut') && $cat['category'] == 'Moved' && ($desc == false || $desc[0] != '~')) {
        $errors[] = ['error' => 'partcheck.category.movedto'];
    }
    // Keyword Check
    $keywords = FileUtils::getKeywords($text);
    $isPatternOrSticker = preg_match('#^[a-z0-9_-]+?[pd][a-z0-9]{2,3}\.dat$#i', $name, $matches);
    if ($pt->folder == 'parts/' && $isPatternOrSticker) {
      if (empty($keywords)) {
        $errors[] = ['error' => 'partcheck.keywords'];
      } else {
        $setfound = false;
        foreach ($keywords as $word) {
          if (mb_strtolower(explode(' ', trim($word))[0]) == 'set' || mb_strtolower($word) == 'cmf' || mb_strtolower($word) == 'build-a-minifigure') {
            $setfound = true;
            break;
          }
        }
        if (! $setfound) {
            $errors[] = ['error' => 'partcheck.keywords'];
        }
      }
    }
    // Check History
    $history = FileUtils::getHistory($text, true);
    $hcount = $history === false ? 0 : count($history);
    if ($hcount != mb_substr_count($file->get(), '!HISTORY')) {
      $errors[] = ['error' => 'partcheck.history.invalid'];
    }
    if (! empty($history)) {
      foreach ($history as $hist) {
        if ($hist['user'] == -1) {
            $errors[] = ['error' => 'partcheck.history.author'];
        }
      }
    }
    return $errors ?? null;  
  }

  public static function historyEventsCrossCheck(Part $part)
  {
    $id = $part->id;
    $eusers = User::whereNotIn('name', ['OrionP', 'cwdee', 'sbliss', 'PTadmin'])->
      whereHas('part_events', function (\Illuminate\Database\Eloquent\Builder $query) use ($id) {
      $query->whereRelation('part_event_type', 'slug', 'submit')->unofficial()->where('part_id', $id);
      })->
      get();
    $husers = $part->editHistoryUsers();
    if (! $husers->find($part->user->id)) {
    $husers->add($part->user);
    }
    $ediff = $eusers->diff($husers);
    if ($ediff->count() > 0) {
      return [__('partcheck.history.eventmismatch', ['users' => implode(', ', $ediff->pluck('name')->all())])];
    } else {
      return [];
    }
  }

}
