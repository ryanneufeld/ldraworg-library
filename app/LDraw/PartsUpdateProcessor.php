<?php

namespace App\LDraw;

use App\Events\PartReleased;
use App\Jobs\UpdatePartImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use App\Models\PartRelease;
use App\Models\Part;
use App\Models\PartEvent;
use App\Models\PartHistory;
use App\Models\PartType;
use App\Models\User;
use App\LDraw\PartManager;
use Illuminate\Support\Facades\Log;

class PartsUpdateProcessor
{

    protected PartRelease $release;
    protected string $tmpDisk;
    protected string $tmpPath;
    protected PartManager $manager;
    
    public function __construct(
        public Collection $parts,
        public User $user,
    ) {
        $this->tmpDisk = config('ldraw.staging_dir.disk');
        $this->tmpPath = config('ldraw.staging_dir.path');
        if (!Storage::disk($this->tmpDisk)->exists($this->tmpPath)) {
            Storage::disk($this->tmpDisk)->makeDirectory($this->tmpPath);
        }
        $this->manager = app(PartManager::class);
    }

    public function createRelease(): void 
    {
        $this->makeNextRelease();
        $this->releaseParts();
        $this->makeReleaseZips();
        $this->copyReleaseFiles();
        $this->postReleaseCleanup();
        $this->regenerateImages();
    }
    
    protected function releaseParts(): void
    {
        foreach ($this->parts as $part) {
            $this->updatePartsList($part);
            $this->releasePart($part);
        }

        $partslist = $this->release->part_list;
        usort($partslist, function (array $a, array $b) { 
            return $a[0] <=> $b[0]; 
        });
        $this->release->part_list = $partslist;
        $this->release->save();
    }
    
    protected function copyReleaseFiles(): void
    {
        $previousRelease = PartRelease::where('id', '<>', $this->release->id)->latest()->first();
        
        // Archive the previous complete zip
        Storage::disk('library')->copy('updates/complete.zip', "updates/complete-{$previousRelease->short}.zip");
        // Copy the new archives to the library
        Storage::disk('library')->writeStream("updates/lcad{$this->release->short}.zip", Storage::disk($this->tmpDisk)->readStream("{$this->tmpPath}/lcad{$this->release->short}.zip"));
        Storage::disk('library')->writeStream("updates/complete.zip", Storage::disk($this->tmpDisk)->readStream("{$this->tmpPath}/complete.zip"));
        
        // Copy the new non-Part files to the library
        foreach (Storage::disk($this->tmpDisk)->allFiles("{$this->tmpPath}/ldraw") as $filename) {
            $content = Storage::disk($this->tmpDisk)->get($filename);
            $f = str_replace("{$this->tmpPath}/ldraw/", 'official/', $filename);
            Storage::disk('library')->put($f, $content);
        }
        
        // Copy the part preview images to images
        foreach (Storage::disk($this->tmpDisk)->allFiles("{$this->tmpPath}/view{$this->release->short}") as $filename) {
            $content = Storage::disk($this->tmpDisk)->get($filename);
            $f = str_replace("{$this->tmpPath}", 'library/updates', $filename);
            Storage::disk('images')->put($f, $content);
        }

        // Remove all the temp files
        Storage::disk($this->tmpDisk)->deleteDirectory($this->tmpPath);
        Storage::disk($this->tmpDisk)->makeDirectory($this->tmpPath);

    }
    
    protected function regenerateImages()
    {
        $affectedParts = new Collection;
        foreach($this->release->parts as $part){
            $affectedParts = $affectedParts->concat($part->ancestorsAndSelf)->unique();
        }
        $affectedParts->each(function (Part $p){
            UpdatePartImage::dispatch($p);
        });
    }
    
    protected function makeNextRelease(): void
    {
        //Figure out next update number
        extract($this->getNextUpdateNumber());
        // create release
        $this->release = PartRelease::create([
            'name' => $name,
            'short' => $short,
            'part_data' => $this->getReleaseData(),
        ]);
        Storage::disk($this->tmpDisk)->put("{$this->tmpPath}/ldraw/models/Note{$this->release->short}CA.txt", $this->makeNotes());
    }

    protected function updatePartsList(Part $part): void
    {
        if (is_null($part->official_part_id) && $part->type->folder == 'parts/') {
            $pl = $this->release->part_list ?? [];
            $pl[] = [$part->description, $part->filename];
            $f = substr($part->filename, 0, -4);
            if ($part->isTexmap()) {
                Storage::disk($this->tmpDisk)->put("{$this->tmpPath}/view{$this->release->short}/{$part->filename}", $part->get());
            } elseif (Storage::disk('images')->exists("library/unofficial/{$f}.png")) {
                Storage::disk($this->tmpDisk)->writeStream("{$this->tmpPath}/view{$this->release->short}/{$f}.png", Storage::disk('images')->readStream("library/unofficial/{$f}.png"));
            }
            if (Storage::disk('images')->exists("library/unofficial/{$f}_thumb.png")) {
                Storage::disk($this->tmpDisk)->writeStream("{$this->tmpPath}/view{$this->release->short}/{$f}_thumb.png", Storage::disk('images')->readStream("library/unofficial/{$f}_thumb.png"));
            }
            $this->release->part_list = $pl;
        }
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
    
    protected function getReleaseData(): array {
        $data = [];
        $data['total_files'] = $this->parts->count();
        $data['new_files'] = $this->parts->whereNull('official_part_id')->count();
        $data['new_types'] = [];
        foreach (PartType::where('type', '!=', 'Shortcut')->get() as $type) {
            if ($type->folder == 'parts/') {
                $count = $this->parts
                    ->whereNull('official_part_id')
                    ->where('type.folder', 'parts/')
                    ->count();
            }
            else {
                $count = $this->parts
                    ->whereNull('official_part_id')
                    ->where('part_type_id', $type->id)
                    ->count();
            } 
            if ($count > 0) {
                $data['new_types'][] = ['name' => $type->name, 'count' => $count];
            }
        }
        $data['moved_parts'] = [];
        $moved = $this->parts->where('category.category', 'Moved');
        foreach ($moved as $part) {
            $data['moved_parts'][] = ['name' => $part->name(),  'movedto' => $part->description]; 
        }
        $data['fixes'] = [];
        $data['rename'] = [];
        $notMoved = $this->parts
            ->whereNotNull('official_part_id')
            ->where('category.category', '!=', 'Moved');
        foreach ($notMoved as $part) {
            if ($part->description != $part->official_part->description) {
                $data['rename'][] = ['name' => $part->name(), 'decription' => $part->description, 'old_description' => $part->official_part->description];
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

    protected function releasePart(Part $part): void 
    {
        if (!$part->isUnofficial()) {
            return;
        }
        // Add history line
        PartHistory::create([
            'user_id' => $this->user->id,
            'part_id' => $part->id,
            'comment' => "Official Update {$this->release->name}"
        ]);


        PartEvent::unofficial()->where('part_id', $part->id)->update(['part_release_id' => $this->release->id]);

        PartReleased::dispatch($part, $this->user, $this->release);
 
        if (!is_null($part->official_part_id)) {
            $opart = $this->updateOfficialWithUnofficial($part, $part->official_part);
            // Update events with official part id
            PartEvent::where('part_release_id', $this->release->id)
                ->where('part_id', $part->id)
                ->update(['part_id' => $opart->id]);
            $part->deleteRelationships();
            \App\Models\ReviewSummaryItem::where('part_id', $part->id)->delete();
            $part->deleteQuietly();
        } else {
            $part->part_release_id = $this->release->id;
            $part->save();
            $part->refresh();
            $part->generateHeader();
            $part->save();
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
            'part_release_id' => $this->release->id,
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

    protected function makeNotes(): string {
        $data = $this->release->part_data;
        $notes = "ldraw.org Parts Update {$this->release->name}\n" . 
            str_repeat('-', 76) . "\n\n" .
            "Redistributable Parts Library - Core Library\n" . 
            str_repeat('-', 76) . "\n\n" .
            "Notes created " . $this->release->created_at->format("r") . " by the Parts Tracker\n\n" .
            "Release statistics:\n" . 
            "   Total files: {$data['total_files']}\n" . 
            "   New files: {$data['new_files']}\n";
        foreach ($data['new_types'] as $t) {
            $notes .= "   New {$t['name']}s: {$t['count']}\n";
        }
        $notes .= "\n" . 
            "Moved Parts\n";
        foreach ($data['moved_parts'] as $m) {
            $notes .= "   {$m['name']}" . str_repeat(' ', max(27 - strlen($m['name']), 0)) . "{$m['movedto']}\n"; 
        }
        $notes .= "\n" . 
            "Renamed Parts\n";
        foreach ($data['rename'] as $m) {    
            $notes .= "   {$m['name']}" . str_repeat(' ', max(27 - strlen($m['name']), 0)) . "{$m['old_description']}\n" .
            "   changed to    {$m['decription']}\n";    }  
        $notes .= "\n" . 
            "Other Fixed Parts\n";
        foreach ($data['fixed'] as $m) {
            $notes .= "   {$m['name']}" . str_repeat(' ', max(27 - strlen($m['name']), 0)) . "{$m['decription']}\n";
        }
        if ($data['minor_edits']['license'] > 0 || $data['minor_edits']['name'] > 0 || $data['minor_edits']['keywords'] > 0) {
            $notes .= "\nMinor Edits\n";
            if ($data['minor_edits']['license'] > 0) {
                $notes .=  "   {$data['minor_edits']['license']} Part licenses changed\n";
            }
            if ($data['minor_edits']['name'] > 0) {
                $notes .=  "   {$data['minor_edits']['license']} Part licenses changed\n";
            }
            if ($data['minor_edits']['keywords'] > 0) {
                $notes .=  "   {$data['minor_edits']['license']} Part licenses changed\n";
            }
        }
        return $notes;      
    }

    public function makeReleaseZips(): void {
        $sfullpath = realpath(config("filesystems.disks.{$this->tmpDisk}.root") . "/{$this->tmpPath}");
        $uzipname = "{$sfullpath}/lcad{$this->release->short}.zip";
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
        foreach (Storage::disk($this->tmpDisk)->allFiles("{$this->tmpPath}/ldraw") as $filename) {
            $zipfilename = str_replace("{$this->tmpPath}/", '', $filename);
            $content = Storage::disk($this->tmpDisk)->get($filename);
            $uzip->addFromString($zipfilename, $content);
            if ($zip->getFromName($zipfilename) !== false)
                $zip->deleteName($zipfilename);
            $zip->addFromString($zipfilename, $content);
        }
        $zip->close();
        $uzip->close();
        
        // These have to be chunked because php doesn't write the file to disk immediately
        // Trying to hold the entire library in memory will cause an OOM error
        Part::official()->chunk(500, function (Collection $parts) use ($zip, $zipname, $uzip, $uzipname) {
            $zip->open($zipname);
            $uzip->open($uzipname);
            foreach($parts as $part) {
                $content = $part->get();
                $zip->addFromString('ldraw/' . $part->filename, $content);
                if ($part->part_release_id == $this->release->id || ($part->part_release_id != $this->release->id && !is_null($part->minor_edit_data))) 
                $uzip->addFromString('ldraw/' . $part->filename, $content);
            }
            $zip->close();
            $uzip->close();
        });
    }

    public function postReleaseCleanup()
    {
        // Zero/null out vote and flag data
        Part::official()->update([
            'uncertified_subpart_count' => 0, 
            'vote_summary' => null, 
            'vote_sort' => 1, 
            'delete_flag' => 0, 
            'minor_edit_data' => null,
            'missing_parts' => null,
            'manual_hold_flag' => 0
        ]);
        Part::official()->each(function (Part $p) {
            $p->votes()->delete();
            $p->notification_users()->sync([]);
        });

        // Reset the unofficial zip file
        Storage::disk('library')->delete('unofficial/ldrawunf.zip');
        ZipFiles::unofficialZip(Part::unofficial()->first());
    }
}