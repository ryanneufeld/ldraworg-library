<?php

namespace App\LDraw\Parse;

use App\Models\Part;

class ParsedPart
{
    public function __construct(
        public ?string $description,
        public ?string $name,
        public ?string $username,
        public ?string $realname,
        public ?bool $unofficial,
        public ?string $type,
        public ?string $qual,
        public ?string $releasetype,
        public ?string $release,
        public ?string $license,
        public ?array $help,
        public ?string $bfcwinding,
        public ?string $metaCategory,
        public ?string $descriptionCategory,
        public ?array $keywords,
        public ?string $cmdline,
        public ?array $history,
        public ?array $subparts,
        public ?string $body,
        public ?string $rawText,
    ) {}
    
    public static function fromPart(Part $part): self
    {
        if (!is_null($part->release) && $part->release->name == 'original') {
            $releasetype = 'original';
        } elseif (!is_null($part->release)) {
            $releasetype = 'update';
        } else {
            $releasetype = '';
        }
        if (!is_null($part->category)) {
            $cat = str_replace(['~','|','=','_'], '', mb_strstr($part->description, " ", true));
            if ($cat != $part->category->category) {
                $metaCategory = $part->category->category;
            } else {
                $descriptionCategory = $part->category->category;
            }
        }
        $history = [];
        foreach ($part->history as $h) {
            $history[] = [
                'user' => $h->user->name,
                'date' => date_format(date_create($h->created_at), "Y-m-d"), 
                'comment' => $h->comment
            ];
        }
        $subs = [];
        foreach ($part->subparts as $s) {
            $s = str_replace('textures/', '', $s);
            $s = str_replace(['parts/', 'p/'], '', $s);
            $subs[] = str_replace('/', '\\', $s);
        }
        $subs = array_unique($subs);

        return new self(
            $part->description,
            $part->name(),
            $part->user->name,
            $part->user->realname,
            is_null($part->release),
            $part->type->type,
            $part->type_qualifier->type ?? null,
            $releasetype,
            $part->release->short ?? null,
            $part->license->text,
            $part->help->pluck('text')->all(),
            $part->bfc,
            $metaCategory ?? null,
            $descriptionCategory ?? null,
            $part->keywords->pluck('keyword')->all(),
            $part->cmdline,
            $history,
            $subs,
            $part->body->body,
            $part->get(),
        );
    }

    protected function isEmptyString(?string $s): bool
    {
        return ($s === null || trim($s) === "");
    }

    public function header(): string
    {
        $header = [];
        $header[] = $this->description ?? '' ;
        $header[] = '0 Name: ' . $this->name ?? '';

        if (!$this->isEmptyString($this->username) && !$this->isEmptyString($this->realname)) {
            $author = "{$this->realname} [{$this->username}]";
        } elseif (!$this->isEmptyString($this->realname)) {
            $author = $this->realname;
        } elseif (!$this->isEmptyString($this->username)) {
            $author = "[{$this->username}]";
        } else {
            $author = '';
        }   

        $header[] = "0 Author: $author";

        if (!is_null($this->type)) {
            $pt = $this->type;
            if (!$this->isEmptyString($this->qual)) {
                $pt .= " {$this->type['qual']}";
            }
            if (!$this->isEmptyString($this->unofficial)) {
                $pt = "Unofficial_{$pt}";
            } elseif (!$this->isEmptyString($this->releasetype)) {
                $pt .= " {$this->releasetype}";
                if ($this->releasetype === 'UPDATE' && !$this->isEmptyString($this->release)) {
                    $pt .= " {$this->release}";
                }
            }
        }

        $header[] = "0 !LDRAW_ORG $pt";
        $header[] = "0 !LICENSE " . $this->license ?? '';
        $header[] = '';

        if (!is_null($this->help)) {
            foreach($this->help as $h) {
                $header[] = "0 !HELP $h";
            }
            $header[] = '';
        }

        if (!$this->isEmptyString($this->bfcwinding)) {
            $header[] = "0 BFC CERTIFY {$this->bfcwinding}";
            $header[] = '';
        } elseif (pathinfo(str_replace('\\', '/', $this->name), PATHINFO_EXTENSION) == '.dat') {
            $header[] = "0 BFC NOCERTIFY";
            $header[] = '';
        }

        $addBlank = false;
        if (!is_null($this->metaCategory)) {
            $header[] = "0 !CATEGORY {$this->metaCategory}";
            $addBlank = true;
        }
        if (!is_null($this->keywords)) {
            foreach ($this->keywords as $index => $kw) {
                if (array_key_first($this->keywords) == $index) {
                    $kwline = "0 !KEYWORDS ";
                }
                if ($kwline !== "0 !KEYWORDS " && mb_strlen("{$kwline}, {$kw}") > 80) {
                    $header[] = $kwline;
                    $kwline = "0 !KEYWORDS ";
                }
                if ($kwline !== "0 !KEYWORDS ") {
                    $kwline .= ", ";
                }
                $kwline .= $kw;
                if (array_key_last($this->keywords) == $index) {
                    $header[] = $kwline;
                    $addBlank = true;
                }
            }  
        }
        if ($addBlank === true) {
            $header[] = '';
        }

        if (!is_null($this->cmdline)) {
            $header[] = "0 !CMDLINE {$this->cmdline}";
            $header[] = '';
        }

        if (!is_null($this->history)) {
            foreach($this->history as $h) {
                $header[] = "0 !HISTORY {$h['date']} [{$h['user']}] {$h['comment']}";
            }
        }

        return implode("\n", $header);
    }
}