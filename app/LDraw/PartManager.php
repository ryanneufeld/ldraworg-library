<?php

namespace App\LDraw;

use App\LDraw\Parse\Parser;
use App\LDraw\Render\LDrawPng;
use App\LDraw\Render\LDView;
use App\Models\Part;
use App\Models\PartBody;
use App\Models\PartCategory;
use App\Models\PartType;
use App\Models\PartTypeQualifier;
use App\Models\User;
use GDImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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
                throw new \RuntimeException("Images must have a non-null user and type");
            }
            
            return $this->addOrChangePartFromImage($part, $filename, $user, $type);
        }

        return $this->addOrChangePartFromText($part);
    }
    
    public function copyOfficialToUnofficialPart(Part $part): Part
    {
        $values = [
            'description' => $part->description,
            'filename' => $part->filename,
            'user_id' => $part->user_id,
            'part_type_id' => $part->part_type_id,
            'part_type_qualifier_id' => $part->part_type_qualifier_id,
            'part_license_id' => $part->part_license_id,
            'bfc' => $part->bfc,
            'part_category_id' => $part->part_category_id,
            'cmdline' => $part->cmdline,
            'header' => $part->header,
        ];
        $upart = Part::create($values);
        $this->updateUnofficialWithOfficialFix($part);
        $upart->setSubparts($part->subparts);
        $upart->setKeywords($part->keywords);
        $upart->setHelp($part->help);
        $upart->setHistory($part->history);
        $upart->setBody($part->body);
        $upart->save();
        $upart->refresh();
        $this->finalizePart($upart);
        return $upart;
    }

    protected function addOrChangePartFromText(string $text): Part
    {
        $part = $this->parser->parse($text);
        
        $user = User::fromAuthor($part->username, $part->realname)->first();
        $type = PartType::firstWhere('type', $part->type);
        $qual = PartTypeQualifier::firstWhere('type', $part->qual);
        $cat = PartCategory::firstWhere('category', $part->metaCategory ?? $part->descriptionCategory);
        $filename = $type->folder . basename(str_replace('\\', '/', $part->name));
        $values = [
            'description' => $part->description,
            'filename' => $filename,
            'user_id' => $user->id,
            'part_type_id' => $type->id,
            'part_type_qualifier_id' => $qual->id ?? null,
            'part_license_id' => $user->license->id,
            'bfc' => $part->bfcwinding ?? null,
            'part_category_id' => $cat->id ?? null,
            'cmdline' => $part->cmdline,
            'header' => ''
        ];
        $upart = $this->makePart($values);
        $upart->setKeywords($part->keywords ?? []);
        $upart->setHelp($part->help ?? []);
        $upart->setHistory($part->history ?? []);
        $upart->setSubparts($part->subparts ?? []);
        $upart->setBody($part->body);       
        $upart->save();
        $upart->refresh();
        $this->finalizePart($upart);
        return $upart;
    }

    protected function addOrChangePartFromImage(\GdImage $image, string $filename, User $user, PartType $type): Part
    {
        $image = $this->png->optimize($image);
        imagesavealpha($image, true);
        ob_start(); 
        imagepng($image);
        $image_data = ob_get_clean();
        $values = [
            'user_id' => $user->id,
            'part_license_id' => $user->license->id,
            'filename' => $type->folder . $filename,
            'description' => "{$type->name} {$filename}",
            'part_type_id' => $type->id,
            'header' => '',
        ];
        $upart = $this->makePart($values);
        $upart->setBody(base64_encode($image_data));       
        $upart->save();
        $upart->refresh();
        $this->finalizePart($upart);
        return $upart;
    }
    
    protected function makePart(array $values): Part
    {
        $upart = Part::unofficial()->firstWhere('filename', $values['filename']);
        $opart = Part::official()->firstWhere('filename', $values['filename']);
        if (!is_null($upart)) {
            $upart->votes()->delete();
            $upart->fill($values);
        } elseif (!is_null($opart)) {
            $values['official_part_id'] = $opart->id;
            $upart = Part::create($values);
            $opart->unofficial_part_id = $upart->id;
            $opart->save();
            $this->updateUnofficialWithOfficialFix($opart);
        } else {
            $upart = Part::create($values);
        }
        return $upart;
    }

    public function finalizePart(Part $part): void
    {
        $part->generateHeader();
        $part->updateVoteData();
        $this->updatePartImage($part, true);
        $this->updateMissing($part->name());
        $part->refresh();
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
        $imagePath = Storage::disk(config("ldraw.render.dir.image.{$lib}.disk"))->path(config("ldraw.render.dir.image.{$lib}.path") . "/{$imageFilename}");
        $imageThumbPath = substr($imagePath, 0, -4) . '_thumb.png';
        $image = $this->png->optimize($image);
        imagepng($image, $imagePath);
        $thumb = $this->png->resizeImage($image, config('ldraw.image.thumb.height'), config('ldraw.image.thumb.width'));
        $thumb = $this->png->optimize($thumb);
        imagepng($thumb, $imageThumbPath);
        if ($updateParents === true) {
            foreach ($part->allParents() as $p) {
                $this->updatePartImage($p);
            }
        }
    }

    protected function updateMissing(string $filename): void
    {
        Part::unofficial()->whereJsonContains('missing_parts', $filename)->each(function(Part $p) {
            $p->setSubparts($this->parser->getSubparts($p->get(false)));
            $this->updatePartImage($p, true);
        });
    }

    protected function updateUnofficialWithOfficialFix(Part $officialPart): void
    {
        Part::unofficial()->whereHas('subparts', function (Builder $query) use ($officialPart) {
            return $query->where('id', $officialPart->id);
        })->each(function (Part $p) {
            $p->setSubparts($this->parser->getSubparts($p->get(false)));
            $this->updatePartImage($p, true);
        });    
    }

    public function addMovedTo(Part $oldPart, Part $newPart): ?Part {
        if (
            $oldPart->isUnofficial() || 
            !$newPart->isUnofficial() || 
            !is_null($oldPart->unofficial_part_id) || 
            $oldPart->type->folder != 'parts/'
        ) {
            return null;
        }

        $values = [
            'description' => "~Moved To " . str_replace(['.dat', '.png'], '', $newPart->name()),
            'filename' => $oldPart->filename,
            'user_id' => Auth::user()->id,
            'part_type_id' => $oldPart->type->id,
            'part_type_qualifier_id' => $oldPart->qualifier->id ?? null,
            'part_license_id' => Auth::user()->license->id,
            'bfc' => $newPart->bfc,
            'part_category_id' => PartCategory::firstWhere('category', 'Moved')->id,
            'header' => '',
        ];
        $upart = Part::create($values);
        $upart->setBody("1 16 0 0 0 1 0 0 0 1 0 0 0 1 {$newPart->name()}\n");
        $upart->subparts()->sync([$newPart->id]);
        $upart->refresh();
        $upart->generateHeader();
        $this->updatePartImage($upart);
        $oldPart->unofficial_part_id = $upart->id;
        $oldPart->save();
        return $upart;    
    }

    public function movePart(Part $part, string $newName, PartType $newType): bool 
    {
        $oldname = $part->name();
        $newName = $part->type->folder . $newName;
        $upart = Part::unofficial()->where('filename', $newName)->first();
        if (!$part->isUnofficial() || !is_null($upart))
        {
            return false;
        }
        if ($part->type->folder !== $newType->folder) {
            $part->type()->associate($newType);
        }
        $part->filename = $newName;
        $part->save();
        $part->generateHeader();
        $this->updatePartImage($part);
        foreach ($part->parents()->unofficial()->get() as $p) {
            if ($p->type->folder === 'parts/' && $p->category->category === "Moved") {
                $p->description = str_replace($oldname, $part->name(), $p->description);
                $p->save();
            }
            $p->body->body = str_replace($oldname, $part->name(), $p->body->body);
            $p->body->save();
        }
        $this->updateMissing($part->name());
        return true;
    }
}
