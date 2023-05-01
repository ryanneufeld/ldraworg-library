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
            $text = FileUtils::cleanFileText($value->get(), false, true);
        } else {
            return;
        }

        // Ensure header required metas are present
        $missing = [
            'description' => PartCheck::checkDescription($text),
            'name' => PartCheck::checkName($text),
            'author' => PartCheck::checkAuthor($text),
            'type' => PartCheck::checkPartType($text),
            'license' => PartCheck::checkLicense($text),
            'BFC' => FileUtils::getBFC($text),
            'category' => FileUtils::getCategory($text),
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
        $isPattern = $pt->folder == 'parts/' && ((substr($name, strrpos($name, '.dat') - 3, 1) == 'p' || substr($name, strrpos($name, '.dat') - 2, 1) == 'p' || substr($name, strrpos($name, '.dat') - 2, 1) == 'd'));

        // Description Checks
        if (! PartCheck::checkLibraryApprovedDescription($text)) {
            $fail('partcheck.description.invalidchars')->translate();
        }
        if ($isPattern && 
            (mb_substr($desc, mb_strrpos($desc, ' ') + 1) != 'Pattern' && mb_strpos($desc, 'Pattern (Obsolete)') === false || mb_strpos($desc, 'Pattern (Needs Work)' === false))) {
            $fail('partcheck.description.patternword')->translate();
        }
        // Note: Name: checks are done in the LDrawFile rule
        // Author checks
        if (! PartCheck::checkAuthorInUsers($text)) {
            $fail('partcheck.author.registered')->translate();
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
                case 'Physical_Color':
                    $fail('partcheck.type.phycolor');
                    break;
                case 'Alias':
                    if ($pt->type != 'Shortcut' && $pt->type != 'Part') {
                        $fail('partcheck.type.alias');
                    }
                    if ($dtag != '=') {
                        $fail('partcheck.type.aliasdesc');
                    }
                    break;
                case 'Flexible_Section':
                    if ($pt->type != 'Part') {
                        $fail('partcheck.type.alias');
                    }
                    if (! preg_match('#^[a-z0-9_-]+?k[a-z0-9]{2}(p[a-z0-9]{2,3})?\.dat#', $name, $matches)) {
                        $fail('partcheck.type.aliasdesc');
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
            $fail('partcheck.category.movedto');
        }
        // Keyword Check
        $keywords = FileUtils::getKeywords($text);
        if ($isPattern) {
          if (empty($keywords)) {
            $fail('partcheck.keywords');
          } else {
            $setfound = false;
            foreach ($keywords as $word) {
              if (mb_strtolower(explode(' ', trim($word))[0]) == 'set' || mb_strtolower($word) == 'cmf') {
                $setfound = true;
                break;
              }
            }
            if (! $setfound) {
                $fail('partcheck.keywords');
            }
          }
        }
        // Check History
        $history = FileUtils::getHistory($text, true);
        $hcount = $history === false ? 0 : count($history);
        if ($hcount != mb_substr_count($text, '!HISTORY')) {
          $fail('partcheck.history.invalid');
        }
        if (! empty($history)) {
          foreach ($history as $hist) {
            if ($hist['user'] == -1) {
                $fail('partcheck.history.author');
            }
          }
        }
    }
}
