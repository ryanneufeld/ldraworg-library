<?php

namespace App\LDraw;

use App\Events\PartReleased;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use App\Models\PartRelease;
use App\Models\Part;
use App\Models\PartEvent;
use App\Models\PartEventType;
use App\Models\PartHistory;
use App\Models\PartType;
use App\Models\User;

class PartsUpdateService
{
    public function __construct(
        public \App\LDraw\Check\PartChecker $checker
    ) {}
    
    public function createRelease(Collection $parts, User $user): void {
        //Figure out next update number
        list($name, $short) = $this->getNextUpdateNumber();
        // create release
        $release = PartRelease::create([
            'name' => $name,
            'short' => $short,
            'part_data' => $this->getReleaseData($parts),
        ]);

        $sdisk = config('ldraw.staging_dir.disk');
        $spath = config('ldraw.staging_dir.path');
        if (!Storage::disk($sdisk)->exists($spath)) {
            Storage::disk($sdisk)->makeDirectory($spath);
        }
        Storage::disk($sdisk)->put("{$spath}/ldraw/models/Note{$release->short}CA.txt", $this->notes($release));

        $partslist = [];
        foreach ($parts as $part) {
            // Update parts list for new parts
            if (is_null($part->official_part_id) && $part->type->folder == 'parts/') {
                $partslist[] = [$part->description, $part->filename];
                $f = substr($part->filename, 0, -4);
                if ($part->isTexmap()) {
                    Storage::disk('images')->put("library/updates/view{$release->short}/" . $part->filename, $part->get());
                } elseif (Storage::disk('images')->exists("library/unofficial/{$f}.png")) {
                    Storage::disk('images')->copy("library/unofficial/{$f}.png", "library/updates/view{$release->short}/$f.png");
                }
                if (Storage::disk('images')->exists("library/unofficial/{$f}_thumb.png")) {
                    Storage::disk('images')->copy("library/unofficial/{$f}_thumb.png", "library/updates/view{$release->short}/{$f}_thumb.png");
                }
            }
            $part->releasePart($release, $user);
        }
        usort($partslist, function (array $a, array $b) { 
            return $a[0] <=> $b[0]; 
        });
        $release->part_list = $partslist;
        $release->save();
        $this->makeZip($release);  
    }
    
    protected function getNextUpdateNumber(): array
    {
        $current = PartRelease::latest()->first();
        $now = now();
        if ($now->format('Y') !== $current->created_at->format('Y')) {
            $update = '01';
        }
        else {
            $num = substr($current->name, -2) + 1;
            if ((int) $num <= 9) {
                $update = "0{$num}";
            } else {
                $update = $num;
            }
        }
        $name = $now->format('Y')."-{$update}";
        $short = $now->format('y')."{$update}";
        return compact('name', 'short');  
    }
    
    protected function getReleaseData(Collection $parts): array {
        $data = [];
        $data['total_files'] = $parts->count();
        $data['new_files'] = $parts->whereNull('official_part_id')->count();
        $data['new_types'] = [];
        foreach (PartType::all() as $type) {
            if ($type->folder == 'parts/') {
                $count = $parts
                    ->whereNull('official_part_id')
                    ->where('type.folder', 'parts/')
                    ->count();
            }
            else {
                $count = $parts
                    ->whereNull('official_part_id')
                    ->where('part_type_id', $type->id)
                    ->count();
            } 
            if ($count > 0) {
                $data['new_types'][] = ['name' => $type->name, 'count' => $count];
            }
        }
        $data['moved_parts'] = [];
        $moved = $parts->where('category.category', 'Moved');
        foreach ($moved as $part) {
            $data['moved_parts'][] = ['name' => $part->name(),  'movedto' => $part->description]; 
        }
        $data['fixes'] = [];
        $data['rename'] = [];
        $notMoved = $parts
            ->whereNotNull('official_part_id')
            ->where('category.category', '!=', 'Moved');
        foreach ($notMoved as $part) {
            $op = Part::find($part->official_part_id);
            if ($part->description != $op->description) {
                $data['rename'][] = ['name' => $part->name(), 'decription' => $part->description, 'old_description' => $op->description];
            }
            else {
                $data['fixed'][] = ['name' => $part->name(), 'decription' => $part->description];
            }
        }
        $data['minor_edits']['license'] = Part::official()->whereJsonLength('minor_edit_data->license', '>', 0)->count();
        $data['minor_edits']['name'] = Part::official()->where(function($q) {
            $q->orWhereJsonLength('minor_edit_data->name', '>', 0)->orWhereJsonLength('minor_edit_data->realname', '>', 0);
        })->count();
        $data['minor_edits']['keywords'] = Part::official()->whereJsonLength('minor_edit_data->keywords', '>', 0)->count();
        foreach(Part::official()->whereJsonLength('minor_edit_data->description', '>', 0)->get() as $p) {
            $data['rename'][] = ['name' => $p->name(), 'decription' => $part->description, 'old_description' => $p->minor_edit_data['description']];
        }
        return $data;
    }

    protected function releasePart(Part $part, PartRelease $release, User $user): void {
        if (!$part->isUnofficial()) {
            return;
        }
        // Add history line
        PartHistory::create([
            'user_id' => $user->id,
            'part_id' => $part->id,
            'comment' => "Official Update {$release->name}"
        ]);

        PartEvent::unofficial()->where('part_id', $part->id)->update(['part_release_id' => $part->id]);

        PartReleased::dispatch($part, $user, $release);

        $part->release()->associate($release);
        $part->refresh();
        $part->generateHeader();
        $part->save();
 
        if (!is_null($part->official_part_id)) {
            $opart = $this->updateOfficialWithUnofficial($part, Part::find($part->official_part_id));
            // Update events with official part id
            PartEvent::where('part_release_id', $release->id)
                ->where('part_id', $part->id)
                ->update(['part_id' => $opart->id]);
            $part->deleteRelationships();
            \App\Models\ReviewSummaryItem::where('part_id', $part->id)->delete();
            $part->deleteQuietly();
        }
    }

    protected function updateOfficialWithUnofficial(Part $upart, Part $opart): Part
    {
        $values = [
            'description' => $upart->description,
            'filename' => $upart->filename,
            'user_id' => $upart->user_id,
            'part_type_id' => $upart->part_type_id,
            'part_type_qualifier_id' => $upart->part_type_qualifier_id,
            'part_release_id' => $upart->part_release_id,
            'part_license_id' => $upart->part_license_id,
            'bfc' => $upart->bfc,
            'part_category_id' => $upart->part_category_id,
            'cmdline' => $upart->cmdline,
            'header' => $upart->header,
        ];
        $opart->fill($values);
        $opart->setSubparts($upart->subparts);
        $opart->setKeywords($upart->keywords);
        $opart->setHelp($upart->help);
        $opart->setHistory($upart->history);
        $opart->setBody($upart->body);
        $opart->save();
        $opart->refresh();
        $opart->generateHeader();
        $opart->save();
        return $opart;
    }
    protected function notes(PartRelease $release): string {
        $data = $release->part_data;
      $notes = "ldraw.org Parts Update " . $release->name . "\n" . 
        str_repeat('-', 76) . "\n\n" .
        "Redistributable Parts Library - Core Library\n" . 
        str_repeat('-', 76) . "\n\n" .
        "Notes created " . $release->created_at->format("r"). " by the Parts Tracker\n\n" .
        "Release statistics:\n" . 
        "   Total files: " . $data['total_files'] . "\n" . 
        "   New files: " . $data['new_files'] . "\n";
      foreach ($data['new_types'] as $t) {
        $notes .= "   New " . $t['name'] . "s: " . $t['count'] . "\n";
      }
      $notes .= "\n" . 
        "Moved Parts\n";
      foreach ($data['moved_parts'] as $m) {
        $notes .= '   ' . $m['name'] . str_repeat(' ', 27 - strlen($m['name'])) . $m['movedto']. "\n"; 
      }
      $notes .= "\n" . 
        "Renamed Parts\n";
      foreach ($data['rename'] as $m) {
        $notes .= '   ' . $m['name'] . str_repeat(' ', 27 - strlen($m['name'])) . $m['old_description'] . "\n" .
        "   changed to    ". $m['decription'] ."\n";    }  
      $notes .= "\n" . 
        "Other Fixed Parts\n";
      foreach ($data['fixes'] as $m) {
        $notes .= '   ' . $m['name'] . str_repeat(' ', 27 - strlen($m['name'])) . $m['decription'] . "\n";
      }
      if ($data['minor_edits']['license'] > 0 || $data['minor_edits']['name'] > 0 || $data['minor_edits']['keywords'] > 0) {
        $notes .= "\n" . "Minor Edits\n";
        if ($data['minor_edits']['license'] > 0) {
          $notes .=  '   ' . $data['minor_edits']['license'] . " Part licenses changed\n";
        }
        if ($data['minor_edits']['name'] > 0) {
          $notes .=  '   ' . $data['minor_edits']['license'] . " Part licenses changed\n";
        }
        if ($data['minor_edits']['keywords'] > 0) {
          $notes .=  '   ' . $data['minor_edits']['license'] . " Part licenses changed\n";
        }
      }
      return $notes;      
    }

    public function makeZip(PartRelease $release): void {
        $sdisk = config('ldraw.staging_dir.disk');
        $spath = config('ldraw.staging_dir.path');
        if (!Storage::disk($sdisk)->exists($spath))
            Storage::disk($sdisk)->makeDirectory($spath);
        $sfullpath = realpath(config("filesystems.disks.{$sdisk}.root") . "/{$spath}");
        $uzipname = "{$sfullpath}/lcad{$release->short}.zip";
        $zipname = "{$sfullpath}/complete.zip";
        
        $uzip = new \ZipArchive();
        $uzip->open($uzipname, \ZipArchive::CREATE);
  
        $zip = new \ZipArchive();
        $zip->open($zipname, \ZipArchive::CREATE);
  
        // Add non-part files to complete zip
        foreach (Storage::disk('library')->allFiles('official') as $filename) {
            $zipfilename = str_replace('official/', '', $filename);
            $content = Storage::disk('library')->get($filename);
            $zip->addFromString('ldraw/' . $zipfilename, $content);
        }
        $zip->close();
  
        // Add new/updated non-part files to complete and update zip
        $zip->open($zipname);
        foreach (Storage::disk($sdisk)->allFiles("{$spath}/ldraw") as $filename) {
            $zipfilename = str_replace("{$spath}/", '', $filename);
            $content = Storage::disk($sdisk)->get($filename);
            $uzip->addFromString($zipfilename, $content);
            if ($zip->getFromName($zipfilename) !== false)
                $zip->deleteName($zipfilename);
            $zip->addFromString($zipfilename, $content);
        }
        $zip->close();
        $uzip->close();
        
        // These have to be chunked because php doesn't write the file to disk immediately
        // Trying to hold the entire library in memory will cause an OOM error
        Part::official()->chunk(500, function (Collection $parts) use ($zip, $zipname, $uzip, $uzipname, $release) {
            $zip->open($zipname);
            $uzip->open($uzipname);
            foreach($parts as $part) {
                $content = $part->get();
                $zip->addFromString('ldraw/' . $part->filename, $content);
                if ($part->part_release_id == $release->id || ($part->part_release_id != $release->id && !is_null($part->minor_edit_data))) 
                $uzip->addFromString('ldraw/' . $part->filename, $content);
            }
            $zip->close();
            $uzip->close();
        });

        // Copy the new archives to updates
        Storage::disk('library')->copy('updates/complete.zip', "updates/complete-{$release->short}.zip");
        Storage::disk('library')->writeStream("updates/lcad{$release->short}.zip", Storage::disk($sdisk)->readStream("{$spath}/lcad{$release->short}.zip"));
        Storage::disk('library')->writeStream("updates/complete.zip", Storage::disk($sdisk)->readStream("{$spath}/complete.zip"));
    }
}