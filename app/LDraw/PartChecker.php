<?php

namespace App\LDraw;

use Illuminate\Http\UploadedFile;
use App\Models\PartType;
use App\Models\User;
use App\Models\Part;
use App\LDraw\FileUtils;

class PartChecker
{
    /**
     * check
     *
     * @param UploadedFile|Part $file
     * @param int|null $user_part_type_id
     * 
     * @return array|null
     */
    public function check(UploadedFile|Part $file, ?int $user_part_type_id = null): ?array
    {
        if ($file instanceof UploadedFile) {
            $text = $file->get();
            $type = $file->getMimetype();
            $filename = basename($file->getClientOriginalName());
        } else {
            $type = $file->isTexmap() ? 'image/png' : 'text/plain';
            $text = $file->get();
            $filename = $file->filename;
            $errors['uncertsubparts'] = $this->hasUncertifiedSubparts($file);
        };

        $errors = $this->checkFile($text, $type, $filename);
        if ($type == 'text/plain') {
            $herrors = $this->checkHeader($text, $user_part_type_id);
            $errors = is_null($errors) ? $herrors : array_merge($errors, $herrors ?? []);
        }

        return $errors;
    }

    public function checkCanRelease(Part $part): ?array
    {
      $errors = $this->check($part) ?? [];
      $hascertparents = !is_null($part->official_part_id) || $part->type->folder == 'parts/' || $this->hasCertifiedParent($part);
      if (!$hascertparents) {
        $errors[] = 'No certified parents';
      }
      $hasuncertsubfiles = $this->hasUncertifiedSubparts($part);
      if ($hasuncertsubfiles) {
        $errors[] = 'Has uncertified subfiles';
      }
      if ($part->manual_hold_flag) {
        $errors[] = 'Manual hold back by admin';
      }
      $can_release = count($errors) == 0 && $hascertparents && !$hasuncertsubfiles && !$part->manual_hold_flag;
      return compact('can_release', 'hascertparents', 'hasuncertsubfiles', 'errors');
    }

    public function hasCertifiedParent(Part $part): bool
    {
      return $part->parents->where('vote_sort', 1)->count() > 0;
    }

    public function hasUncertifiedSubparts(Part $part): bool
    {
      return $part->subparts->where('vote_sort', '!=', 1)->count() > 0;
    }

    public function checkFile(string $text, string $type, string $filename): ?array
    {
      $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
      //Check valid file format
      if (!in_array($fileext, ['dat', 'png'])) {
          $errors[] = __('partcheck.fileformat', ['attribute' => 'extension', 'value' => $fileext]);
          return $errors;
      } elseif (!in_array($type, ['text/plain', 'image/png']) || ($fileext == 'dat' && $type != 'text/plain') || ($fileext == 'png' && $type != 'image/png')) {
          $errors[] = __('partcheck.fileformat', ['attribute' => 'format', 'value' => $fileext] );
          return $errors;
      }
  
      // PNG ends validation here
      if ($type == 'image/png') {
        return null;
      }

      $filename = basename($filename);
      $name = FileUtils::getName($text);
      if (! $this->checkLibraryApprovedName("0 Name: $filename")) {
          $errors[] = __('partcheck.name.invalidchars' );
      } elseif ($filename[0] == 'x') {
          $errors[] = __('partcheck.name.xparts' );
      }    
      if ($name && basename(str_replace('\\', '/', $name)) !== $filename) {
          $errors[] = __('partcheck.name.mismatch', ['value' => basename($name)] );
      }
  
      $headerend = FileUtils::headerEndLine($text);
      $text = explode("\n", $text);
      foreach ($text as $index => $line) {
        if (! $this->validLine($line)) {
            $errors[] = __('partcheck.line.invalid', ['value' => $index + 1] );
        } elseif (! empty($line) && trim($line) != '0' && $index > $headerend && $line[0] == '0' && ! in_array(explode(' ', trim($line))[1], config('ldraw.allowed_metas.body'), true)) {
            $errors[] = __('partcheck.line.invalidmeta', ['value' => $index + 1] );
        }
      }  
      return $errors ?? null;
    }
  
    public function checkHeader(string $text, ?int $user_part_type_id): ?array
    {
      $raw_text = $text;
      $text = FileUtils::cleanFileText($text);

      // Ensure header required metas are present
      $missing = [
          'description' => $this->checkDescription($text),
          'name' => $this->checkName($text),
          'author' => $this->checkAuthor($text),
          'ldraw_org' => $this->checkPartType($text),
          'license' => $this->checkLicense($text),
      ];
      $exit = false;
      foreach ($missing as $meta => $status) {
          if ($status == false) {
              $errors[] = __('partcheck.missing', ['attribute' => $meta] );
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
      if (! $this->checkLibraryApprovedDescription($text)) {
          $errors[] = __('partcheck.description.invalidchars' );
      }
  
      $isPattern = preg_match('#^[a-z0-9_-]+?p[a-z0-9]{2,3}\.dat$#i', $name, $matches);
      $hasPatternText = preg_match('#^.*?\sPattern(\s\((Obsolete|Needs Work)\))?$#ui', $desc, $matches);
      if ($pt->folder == 'parts/' && $isPattern && !$hasPatternText) {
          $errors[] = __('partcheck.description.patternword' );
      }
      // Note: Name: checks are done in the LDrawFile rule
      // Author checks
      $author = FileUtils::getAuthor($text);
      if (! $this->checkAuthorInUsers($text)) {
          $errors[] = __('partcheck.author.registered', ['value' => $author['realname']] );
      }
  
      // !LDRAW_ORG Part type checks
      if (! $this->checkNameAndPartType($text)) {
          $errors[] = __('partcheck.type.path', ['name' => $name, 'type' => $pt->type] );
      }
      if (!is_null($user_part_type_id)) {
        $form_type = PartType::find($user_part_type_id);
        if (!empty($form_type) && $form_type->folder != $pt->folder) {
            $errors[] = __('partcheck.folder', ['attribute' => '!LDRAW_ORG', 'value' => $pt->type, 'folder' => $form_type->folder] );
        }
      }
      if ($pt->type == 'Subpart' && $desc[0] != '~') {
          $errors[] = __('partcheck.type.subpartdesc' );
      }
  
      //Check qualifiers
      if (!empty($type['qual'])) {
          $pq = \App\Models\PartTypeQualifier::firstWhere('type', $type['qual']);
          switch ($pq->type) {
              case 'Physical_Colour':
                  $errors[] = __('partcheck.type.phycolor' );
                  break;
              case 'Alias':
                  if ($pt->type != 'Shortcut' && $pt->type != 'Part') {
                      $errors[] = __('partcheck.type.alias' );
                  }
                  if ($desc[0] != '=') {
                      $errors[] = __('partcheck.type.aliasdesc' );
                  }
                  break;
              case 'Flexible_Section':
                  if ($pt->type != 'Part') {
                      $errors[] = __('partcheck.type.flex' );
                  }
                  if (! preg_match('#^[a-z0-9_-]+?k[a-z0-9]{2}(p[a-z0-9]{2,3})?\.dat#', $name, $matches)) {
                      $errors[] = __('partcheck.type.flexname' );
                  }
                  break;
          }
      }
      // !LICENSE checks
      if (! $this->checkLibraryApprovedLicense($text)) {
          $errors[] = __('partcheck.license.approved' );
      }
      // BFC CERTIFY CCW Check
      if (! $this->checkLibraryBFCCertify($text)) {
          $errors[] = __('partcheck.bfc' );
      }
      // Category Check
      $cat = FileUtils::getCategory($text);
      if (($pt->type == 'Part' || $pt->type == 'Shortcut') && !$this->checkCategory($text)) {
          $errors[] = __('partcheck.category.invalid', ['value' => $cat['category']] );
      } elseif (($pt->type == 'Part' || $pt->type == 'Shortcut') && $cat['category'] == 'Moved' && ($desc == false || $desc[0] != '~')) {
          $errors[] = __('partcheck.category.movedto' );
      }
      // Keyword Check
      $keywords = FileUtils::getKeywords($text);
      $isPatternOrSticker = preg_match('#^[a-z0-9_-]+?[pd][a-z0-9]{2,3}\.dat$#i', $name, $matches);
      if ($pt->folder == 'parts/' && $isPatternOrSticker) {
        if (empty($keywords)) {
          $errors[] = __('partcheck.keywords' );
        } else {
          $setfound = false;
          foreach ($keywords as $word) {
            if (mb_strtolower(explode(' ', trim($word))[0]) == 'set' || mb_strtolower($word) == 'cmf' || mb_strtolower($word) == 'build-a-minifigure') {
              $setfound = true;
              break;
            }
          }
          if (! $setfound) {
              $errors[] = __('partcheck.keywords' );
          }
        }
      }
      // Check History
      $history = FileUtils::getHistory($text, true);
      $hcount = $history === false ? 0 : count($history);
      if ($hcount != mb_substr_count($raw_text, '!HISTORY')) {
        $errors[] = __('partcheck.history.invalid' );
      }
      if (! empty($history)) {
        foreach ($history as $hist) {
          if ($hist['user'] == -1) {
              $errors[] = __('partcheck.history.author' );
          }
        }
      }
      return $errors ?? null;  
    }

  /**
   * validLine
   *
   * @param string $line
   * 
   * @return bool
   */
  public function validLine(string $line): bool
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
  public function checkDescription(string $file): bool
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
  public function checkLibraryApprovedDescription(string $file): bool
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
  public function checkName(string $file): bool
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
  public function checkLibraryApprovedName(string $file): bool
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
  public function checkNameAndPartType(string $file): bool
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
  public function checkAuthor(string $file): bool
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
  public function checkAuthorInUsers(string $file): bool
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
  public function checkPartType(string $file): bool
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
  public function checkLicense(string $file): bool
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
  public function checkLibraryApprovedLicense(string $file): bool
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
  public function checkLibraryBFCCertify(string $file): bool
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
  public function checkCategory(string $file): bool
  {
    $cat = FileUtils::getCategory($file);

    return $cat !== false && in_array($cat['category'], config('ldraw.categories'), true);
  }

  /**
   * historyEventsCrossCheck
   *
   * @param Part $part
   * 
   * @return array
   */
  public function historyEventsCrossCheck(Part $part): array
  {
    $id = $part->id;
    $eusers = User::whereNotIn('name', ['OrionP', 'cwdee', 'sbliss', 'PTadmin'])->
      whereHas('part_events', function (\Illuminate\Database\Eloquent\Builder $query) use ($id) {
      $query->whereRelation('part_event_type', 'slug', 'submit')->whereRelation('release', 'short', 'unof')->where('part_id', $id);
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