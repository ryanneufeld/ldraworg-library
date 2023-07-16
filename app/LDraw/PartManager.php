<?php

namespace App\LDraw;

use App\LDraw\Parse\ParsedPart;
use App\LDraw\Parse\Parser;
use App\LDraw\Render\LDrawPng;
use App\LDraw\Render\LDView;
use App\Models\Part;
use App\Models\PartBody;
use App\Models\PartCategory;
use App\Models\PartHelp;
use App\Models\PartHistory;
use App\Models\PartKeyword;
use App\Models\PartLicense;
use App\Models\PartType;
use App\Models\PartTypeQualifier;
use App\Models\User;
use \GDImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class PartManager
{
    public function __construct(
        public Parser $parser,
        public LDView $render,
        public LDrawPng $png,
    ) {}
    
    public function addOrChangePart(string|GDImage $part, ?string $filename = null, ?User $user = null, ?PartType $type = null): Part
    {
        if ($part instanceof GDImage) {
            if (is_null($filename) || is_null($user) || is_null($type)) {
                // Runtime exception
            } else {
                return $this->addOrChangePartFromImage($part, $filename, $user, $type);
            }
        } else {
            return $this->addOrChangePartFromText($part);
        }
    }
    
    protected function addOrChangePartFromText(string $text): Part
    {
        $part = $this->parser->parse($text);
        
        $user = User::findByName($part->username, $part->realname);
        $type = PartType::firstWhere('type', $part->type);
        $qual = PartTypeQualifier::firstWhere('type', $part->qual);
        $cat = PartCategory::firstWhere('category', $part->metaCategory ?? $part->descriptionCategory);
        $lic = PartLicense::firstWhere('text', $part->license);
        $filename = $type->folder . basename(str_replace('\\', '/', $part->name));
        $values = [
            'description' => $part->description,
            'filename' => $filename,
            'user_id' => $user->id,
            'part_type_id' => $type->id,
            'part_type_qualifier_id' => $qual->id ?? null,
            'part_license_id' => $lic->id,
            'bfc' => $part->bfcwinding ?? null,
            'part_category_id' => $cat->id ?? null,
            'cmdline' => $part->cmdline,
            'header' => $part->header()
        ];
        $upart = Part::unofficial()->name($part->name)->first();
        $opart = Part::official()->name($part->name)->first();
        if (!is_null($upart)) {
            $upart->fill($values);
        } elseif (!is_null($opart)) {
            $values['official_part_id'] = $opart->id;
            $upart = Part::create($values);
        } else {
            $upart = Part::create($values);
        }
        $this->setKeywords($upart, $part->keywords ?? []);
        $this->setHelp($upart, $part->help ?? []);
        $this->setHistory($upart, $part->history ?? []);
        $this->setSubparts($upart, $part->subparts);
        if (is_null ($upart->body)) {
            PartBody::create([
                'part_id' => $upart->id,
                'body' => $part->body,
            ]);
        } else {
            $upart->body->body = $part->body;
        }
        
        $upart->save();
        $upart->refresh();
        $upart->updateVoteData();
        $this->updatePartImage($upart, true);
        return $upart;
    }

    protected function addOrChangePartFromImage(\GdImage $image, string $filename, User $user, PartType $type): Part
    {
        ob_start (); 
        imagepng($image);
        $image_data = ob_get_contents (); 
        ob_end_clean (); 
        $body = base64_encode($image_data);
        $values = [
            'user_id' => $user->id,
            'part_license_id' => $user->license->id,
            'filename' => $type->folder . $filename,
            'description' => "{$type->name} $filename",
            'part_type_id' => $type->id,
            'header' => '',
        ];
        $upart = Part::unofficial()->firstWhere('filename', $type->folder . $filename);
        $opart = Part::official()->firstWhere('filename', $type->folder . $filename);
        if (!is_null($upart)) {
            $upart->fill($values);
        } elseif (!is_null($opart)) {
            $values['official_part_id'] = $opart->id;
            $upart = Part::create($values);
            $opart->unofficial_part_id = $upart->id;
            $opart->save();
        } else {
            $upart = Part::create($values);
        }
        if (is_null ($upart->body)) {
            PartBody::create([
                'part_id' => $upart->id,
                'body' => $body,
            ]);
        } else {
            $upart->body->body = $body;
        }
        $upart->header = ParsedPart::fromPart($upart)->header();
        $upart->save();
        $upart->refresh();
        $upart->updateVoteData();
        $this->updatePartImage($upart, true);
        return $upart;
    }

    public function setKeywords(Part $part, array $keywords): void
    {
        $kws = PartKeyword::whereIn('keyword', $keywords)->get();
        $ids = $kws->pluck('id')->all();
        $new_keywords = collect($keywords)->diff($kws->pluck('keyword')->all());
        foreach($new_keywords as $kw) {
            $ids[] = PartKeyword::create(['keyword' => $kw])->id;
        }
        $part->keywords()->sync($ids);
    }

    public function setHelp(Part $part, array $help): void
    {
        $part->help()->delete();
        foreach($help as $index => $h) {
            PartHelp::create(['part_id' => $part->id, 'order' => $index, 'text' => $h]);
        }
    }

    public function setHistory(Part $part, array $history): void 
    {
        $part->history()->delete();
        foreach ($history as $hist) {
            $u = User::findByName($hist['user']);
            PartHistory::create([
                'user_id' => $u->id, 
                'part_id' => $part->id, 
                'created_at' => $hist['date'], 
                'comment' => $hist['comment']
            ]);
        }
    }

    public function setSubparts(Part $part, array $subparts): void 
    {
        $subs = [];
        foreach ($subparts['subparts'] ?? [] as $s) {
            $s = str_replace('\\', '/', $s);
            $subs[] = "parts/$s";
            $subs[] = "p/$s";
        }
        foreach ($subparts['textures'] ?? [] as $s) {
            $s = str_replace('\\', '/', $s);
            $subs[] = "parts/textures/$s";
            $subs[] = "p/textures/$s";
        }
        $subps = Part::whereIn('filename', $subs)->get();
        $part->subparts()->sync($subps->pluck('id')->all());

        $existing_subs = $subps->pluck('filename')->all();
        $esubs = [];
        foreach ($existing_subs ?? [] as $s) {
            $s = str_replace('textures/', '', $s);
            $s = str_replace(['parts/', 'p/'], '', $s);
            $esubs[] = str_replace('/', '\\', $s);
        }
        $missing = collect(array_merge($subparts['subparts'] ?? [], $subparts['textures'] ?? []))->diff(collect($esubs));
        $part->missing_parts = $missing;
        $part->save();
    }
    
    public function setHeader(Part $part) {
        $part->header = ParsedPart::fromPart($part)->header();
        $part->save();
    }

    public function allSubparts(Part $part): Collection
    {
        $parts = new Collection;
        if ($part->subparts->count() == 0) return $parts;
        $parts = $parts->concat($part->subparts);
        foreach ($part->subparts as $s) {
            $parts = $parts->concat($this->allSubparts($s));
        }
        return $parts->unique();
    }

    public function allParents(Part $part): Collection
    {
        $parts = new Collection;
        if ($part->parents->count() == 0) return $parts;
        $parts = $parts->concat($part->parents);
        foreach ($part->parents as $s) {
            $parts = $parts->concat($this->allParents($s));
        }
        return $parts->unique();
    }

    public function updatePartImage(Part $part, bool $updateParents = false): void
    {
        if ($part->isTexmap()) {
            $image = imagecreatefromstring($part->get());
        } else {
            $image = $this->render->render($part);
        }
        $lib = $part->isUnofficial() ? 'unofficial' : 'official';
        $imageFilename = substr($part->filename, 0, -4) . '.png';
        $imagePath = Storage::disk(config("ldraw.render.dir.image.$lib.disk"))->path(config("ldraw.render.dir.image.$lib.path") . "/$imageFilename");
        $imageThumbPath = substr($imagePath, 0, -4) . '_thumb.png';
        imagesavealpha($image, true);
        imagepng($this->png->optimize($image), $imagePath);
        imagepng($this->png->optimize($this->png->resizeImage($image, config('ldraw.image.thumb.height'), config('ldraw.image.thumb.width'))), $imageThumbPath);
        if ($updateParents === true) {
            foreach ($this->allParents($part) as $p) {
                $this->updatePartImage($p);
            }
        }
    }
}
