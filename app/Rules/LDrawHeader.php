<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use App\LDraw\FileUtils;
use App\LDraw\PartCheck;
use App\Models\PartType;

class LDrawHeader implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];
 
    // ...
 
    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // PNG file have no header.
        if ($value->getMimetype() == 'text/plain') {
            $text = FileUtils::cleanFileText($value->get());
        } else {
            return;
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
                $fail('partcheck.missing')->translate(['attribute' => $meta]);
                $exit = true;
            }
        }
        if ($exit) {
            return;
        }

        $type = FileUtils::getPartType($text);
        $pt = PartType::firstWhere('type', $type['type']);
        $name = str_replace('\\', '/', FileUtils::getName($text));
        $desc = FileUtils::getDescription($text);

        // Description Checks
        if (! PartCheck::checkLibraryApprovedDescription($text)) {
            $fail('partcheck.description.invalidchars')->translate();
        }

        $isPattern = preg_match('#^[a-z0-9_-]+?p[a-z0-9]{2,3}\.dat$#i', $name, $matches);
        $hasPatternText = preg_match('#^.*?\sPattern(\s\((Obsolete|Needs Work)\))?$#ui', $desc, $matches);
        if ($pt->folder == 'parts/' && $isPattern && !$hasPatternText) {
            $fail('partcheck.description.patternword')->translate();
        }
        // Note: Name: checks are done in the LDrawFile rule
        // Author checks
        $author = FileUtils::getAuthor($text);
        if (! PartCheck::checkAuthorInUsers($text)) {
            $fail('partcheck.author.registered')->translate(['value' => $author['realname']]);
        }

        // !LDRAW_ORG Part type checks
        $form_type = PartType::find($this->data['part_type_id']);
        $dtag = $desc[0];
    
        if (! PartCheck::checkNameAndPartType($text)) {
            $fail('partcheck.type.path')->translate(['name' => $name, 'type' => $pt->type]);
        }
        if ($form_type->folder != $pt->folder) {
            $fail('partcheck.folder')->translate(['attribute' => '!LDRAW_ORG', 'value' => $pt->type, 'folder' => $form_type->folder]);
        }
        if ($pt->type == 'Subpart' && $dtag != '~') {
            $fail('partcheck.type.subpartdesc')->translate();
        }

        //Check qualifiers
        if (!empty($type['qual'])) {
            $pq = \App\Models\PartTypeQualifier::firstWhere('type', $type['qual']);
            switch ($pq->type) {
                case 'Physical_Colour':
                    $fail('partcheck.type.phycolor')->translate();
                    break;
                case 'Alias':
                    if ($pt->type != 'Shortcut' && $pt->type != 'Part') {
                        $fail('partcheck.type.alias')->translate();
                    }
                    if ($dtag != '=') {
                        $fail('partcheck.type.aliasdesc')->translate();
                    }
                    break;
                case 'Flexible_Section':
                    if ($pt->type != 'Part') {
                        $fail('partcheck.type.flex')->translate();
                    }
                    if (! preg_match('#^[a-z0-9_-]+?k[a-z0-9]{2}(p[a-z0-9]{2,3})?\.dat#', $name, $matches)) {
                        $fail('partcheck.type.flexname')->translate();
                    }
                    break;
            }
        }
        // !LICENSE checks
        if (! PartCheck::checkLibraryApprovedLicense($text)) {
            $fail('partcheck.license.approved')->translate();
        }
        // BFC CERTIFY CCW Check
        if (! PartCheck::checkLibraryBFCCertify($text)) {
            $fail('partcheck.bfc')->translate();
        }
        // Category Check
        $cat = FileUtils::getCategory($text);
        if (($pt->type == 'Part' || $pt->type == 'Shortcut') && !PartCheck::checkCategory($text)) {
            $fail('partcheck.category.invalid')->translate(['value' => $cat['category']]);
        } elseif ($cat['category'] == 'Moved' && ($desc == false || $desc[0] != '~')) {
            $fail('partcheck.category.movedto')->translate();
        }
        // Keyword Check
        $keywords = FileUtils::getKeywords($text);
        $isPatternOrSticker = preg_match('#^[a-z0-9_-]+?[pd][a-z0-9]{2,3}\.dat$#i', $name, $matches);
        if ($pt->folder == 'parts/' && $isPatternOrSticker) {
          if (empty($keywords)) {
            $fail('partcheck.keywords')->translate();
          } else {
            $setfound = false;
            foreach ($keywords as $word) {
              if (mb_strtolower(explode(' ', trim($word))[0]) == 'set' || mb_strtolower($word) == 'cmf' || mb_strtolower($word) == 'build-a-minifigure') {
                $setfound = true;
                break;
              }
            }
            if (! $setfound) {
                $fail('partcheck.keywords')->translate();
            }
          }
        }
        // Check History
        $history = FileUtils::getHistory($text, true);
        $hcount = $history === false ? 0 : count($history);
        if ($hcount != mb_substr_count($value->get(), '!HISTORY')) {
          $fail('partcheck.history.invalid')->translate();
        }
        if (! empty($history)) {
          foreach ($history as $hist) {
            if ($hist['user'] == -1) {
                $fail('partcheck.history.author')->translate();
            }
          }
        }
    }
}
