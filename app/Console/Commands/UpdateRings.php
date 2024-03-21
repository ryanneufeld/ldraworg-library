<?php

namespace App\Console\Commands;

use App\Events\PartReviewed;
use App\Events\PartSubmitted;
use App\Jobs\UpdateZip;
use App\LDraw\Check\PartChecker;
use App\LDraw\Parse\Parser;
use Illuminate\Console\Command;
use App\LDraw\PartManager;
use App\LDraw\ZipFiles;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\PartHistory;
use App\Models\PartType;
use App\Models\PartTypeQualifier;
use App\Models\User;
use App\Models\VoteType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class UpdateRings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lib:update-rings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Rings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pm = app(PartManager::class);
        $u = User::firstWhere('name', 'OrionP');
        $ftVote = VoteType::find('T');

        $pattern = 'p(\/4?8)?\/([0-9]{1,2}-[0-9]{1,2})rin?([0-9]{1,2})\.dat';
        $namepattern = '(4?8\\\\)?([0-9]{1,2}-[0-9]{1,2})rin?([0-9]{1,2})\.dat';
        // Find all CC_BY_4 rings that haven't been converted
        $rings = Part::official()
            ->whereRelation('license', 'name', 'CC_BY_4')
            ->whereRelation('type', 'folder', 'LIKE', 'p/%')
            ->doesntHave('unofficial_part')
            ->whereRaw('filename REGEXP "' . $pattern . '"')
            ->where('description', 'NOT LIKE', '%(Obsolete)')
            ->where('description', 'NOT LIKE', '~Moved%')
            ->get();


        $headerpattern = '#^\h*0\h+Name:\h+'. $namepattern . '\h*$#um';
        $this->info("Fixing {$rings->count()} Official Rings");
        foreach($rings as $ring) {
            $text = preg_replace($headerpattern, '0 Name: $1$2ring$3.dat', $ring->header);
            // Add correctly named rings to tracker
            $newring = $this->addpart("{$text}\n{$ring->body->body}");
            PartHistory::create([
                'part_id' => $newring->id,
                'user_id' => $u->id,
                'comment' => "Moved from {$ring->name()}",
            ]);
            $newring->save();
            $newring->refresh();
            $newring->generateHeader();
            PartSubmitted::dispatch($newring, $u, "Update ring primitive name via script. Do not hold.");

            // Fast track new ring
            $u->castVote($newring, $ftVote);
            PartReviewed::dispatch($newring, $u, 'T');

            // Obsolete and add old rings to tracker
            $oldring = $this->addpart($ring->get());
            $oldring->description = "~{$oldring->description} (Obsolete)";
            PartHistory::create([
                'part_id' => $oldring->id,
                'user_id' => $u->id,
                'comment' => "Obsolete, use {$newring->name()}",
            ]);
            $oldring->save();
            $oldring->refresh();
            $oldring->generateHeader();
            PartSubmitted::dispatch($oldring, $u, "Obsolete old ring primitive via script. Do not hold.");

            // Fast track old rings
            $u->castVote($oldring, $ftVote);
            PartReviewed::dispatch($oldring, $u, 'T');
        }
        $this->info('Official Rings Fixed');

        $rings = Part::with('parents')
            ->unofficial()
            ->whereRelation('type', 'folder', 'LIKE', 'p/%')
            ->whereRaw('filename REGEXP "' . $pattern . '"')
            ->where('description', 'LIKE', '%(Obsolete)')
            ->has('parents')
            ->get();
        
        $official = [];
        $unofficial = [];

        foreach ($rings as $ring) {
            $newname = preg_replace("#{$pattern}#", $ring->type->folder . '$2ring$3.dat', $ring->filename);
            $newring = Part::unofficial()->firstWhere('filename', $newname);
            if (!is_null($newring)) {
                foreach($ring->parents as $p) {
                    if (in_array($p->id, $official) || in_array($p->id, $unofficial)) {
                        continue;
                    }
                    if (!$p->isUnofficial() && is_null($p->unofficial_part)) {
                        $official[] = $p->id;
                    }
                    elseif ($p->isUnofficial()) {
                        $unofficial[] = $p->id;
                    }
                    else {
                        $this->error("Error. Unmatched official part fix {$p->filename}");
                    }
                }
            }
            else {
                $this->error("Error. {$ring->filename} has unmatched {$newname}");
            }
        }

        $pattern = '(4?8\\\\)?([0-9]{1,2}-[0-9]{1,2})rin?([0-9]{1,2})\.dat';

        $oparts = Part::whereIn('id', $official)->get();
        $this->info("Fixing {$oparts->count()} official parts");
        foreach ($oparts as $p) {
            $text = $p->body->body;
            $text = preg_replace("#{$pattern}#", '$1$2ring$3.dat', $text);
            $np = $this->addpart("{$p->header}\n{$text}");
            PartHistory::create([
                'part_id' => $np->id,
                'user_id' => $u->id,
                'comment' => "Updated ring primitives",
            ]);
            $np->refresh();
            $np->generateHeader();
            if (!is_null($np->part_release_id)) {
                $this->error("Error with {$np->filename}");
                return;
            }
            PartSubmitted::dispatch($np, $u, "Update ring primitives via script. Do not hold.");
            $u->castVote($np, $ftVote);
            PartReviewed::dispatch($np, $u, 'T');
        }

        $uparts = Part::whereIn('id', $unofficial)->get();
        $this->info("Fixing {$uparts->count()} unofficial parts");
        foreach ($uparts as $p) {
            $p->body->body = preg_replace("#{$pattern}#", '$1$2ring$3.dat', $p->body->body);
            $p->body->save();
            $pm->loadSubpartsFromBody($p);
            PartHistory::create([
                'part_id' => $p->id,
                'user_id' => $u->id,
                'comment' => "Updated ring primitives",
            ]);
            $p->refresh();
            $p->generateHeader();
            PartSubmitted::dispatch($p, $u, "Update ring primitives via script. This will not reset the vote status of the part.");            
        }

        // Reset the unofficial zip file
        Storage::disk('library')->delete('unofficial/ldrawunf.zip');
        ZipFiles::unofficialZip(Part::unofficial()->first());
        
    }

    protected function addPart($text)
    {
        $pm = app(PartManager::class);
        $part = app(Parser::class)->parse($text);
        
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
        $upart = Part::unofficial()->firstWhere('filename', $values['filename']);
        $opart = Part::official()->firstWhere('filename', $values['filename']);
        if (!is_null($upart)) {
            $upart->fill($values);
        } elseif (!is_null($opart)) {
            $upart = Part::create($values);
            $opart->unofficial_part()->associate($upart);
            $opart->save();
            Part::unofficial()
                ->whereHas('subparts', fn (Builder $query) => $query->where('id', $opart->id))
                ->each(fn (Part $p) => $pm->loadSubpartsFromBody($p));    
        } else {
            $upart = Part::create($values);
        }
        $upart->setKeywords($part->keywords ?? []);
        $upart->setHelp($part->help ?? []);
        $upart->setHistory($part->history ?? []);
        $upart->setSubparts($part->subparts ?? []);
        $upart->setBody($part->body);       
        $upart->save();
        $upart->refresh();
        $upart->generateHeader();
        $upart->updateVoteData();
        $pm->updatePartImage($upart);
        $upart->refresh();
        if (!is_null($upart->official_part)) {
            foreach ($upart->official_part->parents()->official()->get() as $p) {
                $pm->loadSubpartsFromBody($p);
            }
        }
        return $upart;
    }
}
