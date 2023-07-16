<?php

namespace App\LDraw\Check;

use Illuminate\Http\UploadedFile;
use App\Models\PartType;
use App\Models\User;
use App\Models\Part;
use App\LDraw\Parse\ParsedPart;
use App\Models\PartCategory;

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
    public function check(ParsedPart $part): ?array
    {
        $errors = $this->checkFile($part);
        $herrors = $this->checkHeader($part);
        $errors = is_null($errors) ? $herrors : array_merge($errors, $herrors ?? []);

        return $errors;
    }

    public function checkCanRelease(Part $part): ?array
    {
        $errors = [];
        if (!$part->isTexmap()) {
            $errors = $this->check(ParsedPart::fromPart($part)) ?? [];
        }  
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

    public function checkFile(ParsedPart $part): ?array
    {
      if (! $this->checkLibraryApprovedName($part->name)) {
          $errors[] = __('partcheck.name.invalidchars' );
      } elseif ($part->name[0] == 'x') {
          $errors[] = __('partcheck.name.xparts' );
      }

      $text = explode("\n", $part->body);
      
      foreach ($text as $index => $line) {
        if (! $this->validLine($line)) {
            $errors[] = __('partcheck.line.invalid', ['value' => $index] );
        } elseif (! empty($line) && trim($line) != '0' && $line[0] == '0' && ! in_array(explode(' ', trim($line))[1], config('ldraw.allowed_metas.body'), true)) {
            $errors[] = __('partcheck.line.invalidmeta', ['value' => $index] );
        }
      }  
      return $errors ?? null;
    }
  
    public function checkHeader(ParsedPart $part): ?array
    {
      // Ensure header required metas are present
      $missing = [
          'description' => !empty($part->description),
          'name' => !empty($part->name),
          'author' => !empty($part->username) || !empty($part->realname),
          'ldraw_org' => !empty($part->type),
          'license' => !empty($part->license),
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
  
      $pt = PartType::firstWhere('type', $part->type);
      $name = str_replace('\\', '/', $part->name);
  
      // Description Checks
      if (! $this->checkLibraryApprovedDescription($part->description)) {
          $errors[] = __('partcheck.description.invalidchars' );
      }
  
      $isPattern = preg_match('#^[a-z0-9_-]+?p[a-z0-9]{2,3}\.dat$#i', $name, $matches);
      $hasPatternText = preg_match('#^.*?\sPattern(\s\((Obsolete|Needs Work)\))?$#ui', $part->description, $matches);
      if ($pt->folder == 'parts/' && $isPattern && !$hasPatternText) {
          $errors[] = __('partcheck.description.patternword' );
      }
      // Note: Name: checks are done in the LDrawFile rule
      // Author checks
      if (! $this->checkAuthorInUsers($part->username ?? '', $part->realname ?? '')) {
          $errors[] = __('partcheck.author.registered', ['value' => $part->realname ?? $part->username] );
      }
  
      // !LDRAW_ORG Part type checks
      if (! $this->checkNameAndPartType($part->name, $part->type)) {
          $errors[] = __('partcheck.type.path', ['name' => $name, 'type' => $pt->type] );
      }
      if ($pt->type == 'Subpart' && $part->description[0] != '~') {
          $errors[] = __('partcheck.type.subpartdesc' );
      }
  
      //Check qualifiers
      if (!empty($part->qual)) {
          $pq = \App\Models\PartTypeQualifier::firstWhere('type', $part->qual);
          switch ($pq->type) {
              case 'Physical_Colour':
                  $errors[] = __('partcheck.type.phycolor' );
                  break;
              case 'Alias':
                  if ($pt->type != 'Shortcut' && $pt->type != 'Part') {
                      $errors[] = __('partcheck.type.alias' );
                  }
                  if ($part->description[0] != '=') {
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
      if (! $this->checkLibraryApprovedLicense($part->license)) {
          $errors[] = __('partcheck.license.approved' );
      }
      // BFC CERTIFY CCW Check
      if (! $this->checkLibraryBFCCertify($part->bfcwinding)) {
          $errors[] = __('partcheck.bfc' );
      }
      // Category Check
      if (!empty($part->metaCategory)) {
        $validCategory = $this->checkCategory($part->metaCategory);
        $cat = $part->metaCategory;
      } else {
        $validCategory = $this->checkCategory($part->descriptionCategory);
        $cat = $part->descriptionCategory;
      }
      
      if (($pt->type == 'Part' || $pt->type == 'Shortcut') && !$validCategory) {
          $errors[] = __('partcheck.category.invalid', ['value' => $cat] );
      } elseif (($pt->type == 'Part' || $pt->type == 'Shortcut') && $cat == 'Moved' && ($part->description[0] != '~')) {
          $errors[] = __('partcheck.category.movedto' );
      }
      // Keyword Check
      $isPatternOrSticker = preg_match('#^[a-z0-9_-]+?[pd][a-z0-9]{2,3}\.dat$#i', $name, $matches);
      if ($pt->folder == 'parts/' && $isPatternOrSticker) {
        if (empty($part->keywords)) {
          $errors[] = __('partcheck.keywords' );
        } else {
          $setfound = false;
          foreach ($part->keywords as $word) {
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
      if (!is_null($part->history)) {
        $hcount = count($part->history);
        if ($hcount != mb_substr_count($part->rawText, '!HISTORY')) {
          $errors[] = __('partcheck.history.invalid' );
        }
        foreach ($part->history as $hist) {
          if (is_null(User::findByName($hist['user']))) {
              $errors[] = __('partcheck.history.author');
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
   * checkLibraryApprovedDescription
   *
   * @param string $file
   * 
   * @return bool
   */
  public function checkLibraryApprovedDescription(string $description): bool
  {
    return preg_match(config('ldraw.patterns.library_approved_description'), $description, $matches);
  }

  /**
   * checkLibraryApprovedName
   *
   * @param string $name
   * 
   * @return bool
   */
  public function checkLibraryApprovedName(string $name): bool
  {
    return preg_match(config('ldraw.patterns.library_approved_name'), $name, $matches);
  }

  /**
   * checkNameAndPartType
   *
   * @param string $file
   * 
   * @return bool
   */
  public function checkNameAndPartType(string $name, string $type): bool
  {
    $name = str_replace('\\', '/', $name);
    $pt = PartType::firstWhere('type', $type);
    // Automatic fail if no Name:, LDRAW_ORG line, or DAT file has TEXTURE type
    if (is_null($pt) || $pt->format == 'png') {
      return false;
    }

    // Construct the name implied by the part type
    $aname = str_replace(['p/', 'parts/'], '', $pt->folder . basename($name));

    return $name === $aname;
  }

  /**
   * checkAuthorInUsers
   *
   * @param string $file
   * 
   * @return bool
   */
  public function checkAuthorInUsers(string $username, string $realname): bool
  {
    return !is_null(User::findByName($username, $realname));
  }

  /**
   * checkLibraryApprovedLicense
   *
   * @param string $file
   * 
   * @return bool
   */
  public function checkLibraryApprovedLicense(string $license): bool
  {
    $liblic = \App\Models\PartLicense::firstWhere('text', $license);
    return !is_null($liblic) && $liblic->name !== 'NonCA';
  }

  /**
   * checkLibraryBFCCertify
   *
   * @param string $file
   * 
   * @return bool
   */
  public function checkLibraryBFCCertify(string $bfc): bool
  {
    return $bfc === 'CCW';
  }

  /**
   * checkCategory
   *
   * @param string $file
   * 
   * @return bool
   */
  public function checkCategory(string $category): bool
  {
    return !is_null(PartCategory::firstWhere('category', $category));
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