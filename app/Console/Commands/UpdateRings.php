<?php

namespace App\Console\Commands;

use App\Events\PartReviewed;
use App\Events\PartSubmitted;
use App\Jobs\UpdateZip;
use App\LDraw\Check\PartChecker;
use Illuminate\Console\Command;
use App\LDraw\PartManager;
use App\LDraw\ZipFiles;
use App\Models\Part;
use App\Models\PartHistory;
use App\Models\PartType;
use App\Models\User;
use App\Models\VoteType;
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

        // Find all CC_BY_4 rings that haven't been converted
        $rings = Part::official()
            ->whereRelation('license', 'name', 'CC_BY_4')
            ->whereRelation('type', 'folder', 'LIKE', 'p/%')
            ->doesntHave('unofficial_part')
            ->whereRaw('filename REGEXP "' . $pattern . '"')
            ->where('description', 'NOT LIKE', '%(Obsolete)')
            ->where('description', 'NOT LIKE', '~Moved%')
            ->get();


        echo 'Fixing ' . $rings->count() . " Official Rings\n";
        foreach($rings as $ring) {
            $text = $ring->get();

            // Add correctly named rings to tracker
            $newring = $pm->addOrChangePartFromText($text);
            $newring->official_part->unofficial_part()->dissociate();
            $newring->official_part->save();
            $newname = preg_replace("#{$pattern}#", $newring->type->folder . '$2ring$3.dat', $newring->filename);
            $newring->filename = $newname;
            PartHistory::create([
                'part_id' => $newring->id,
                'user_id' => $u->id,
                'comment' => "Moved from {$ring->name()}",
            ]);
            $newring->save();
            $newring->refresh();
            $pm->updatePartImage($newring);
            $newring->generateHeader();
            PartSubmitted::dispatch($newring, $u, "Update ring primitive name via script. Do not hold.");

            // Fast track new ring
            $u->castVote($newring, $ftVote);
            PartReviewed::dispatch($newring, $u, 'T');

            // Obsolete and add old rings to tracker
            $oldring = $pm->addOrChangePartFromText($text);
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
        echo "Official Rings Fixed\n";

        $rings = Part::unofficial()
            ->whereRelation('type', 'folder', 'LIKE', 'p/%')
            ->whereRaw('filename REGEXP "' . $pattern . '"')
            ->where('description', 'LIKE', '%(Obsolete)')
            ->has('parents')
            ->get();
        
        $fixedparts = new Collection();
        $newfixes = new Collection();

        // Fix all parts that refer to the old rings
        foreach ($rings as $ring) {
            $newname = preg_replace("#{$pattern}#", $ring->type->folder . '$2ring$3.dat', $ring->filename);
            $newring = Part::unofficial()->firstWhere('filename', $newname);
            if (!is_null($newring)) {
                foreach($ring->parents as $p) {
                    if (!$p->isUnofficial() && is_null($p->unofficial_part)) {
                        $p = $pm->addOrChangePartFromText($p->get());
                        $newfixes->push($p);
                    } else {
                        $fixedparts->push($p);
                    }
                    $p->body->body = str_replace($ring->name(), $newring->name(), $p->body->body);
                    $p->body->save();
                                            
                }
            }
        }

        echo 'Fixing ring refs for ' . $fixedparts->count() . " parts on tracker\n";
        foreach ($fixedparts as $p) {
            $pm->loadSubpartsFromBody($p);
            PartHistory::create([
                'part_id' => $p->id,
                'user_id' => $u->id,
                'comment' => "Updated ring primitives",
            ]);
            $p->refresh();
            $p->generateHeader();
            PartSubmitted::dispatch($p, $u, "Update ring primitives via script. This will not reset the vote status of the part");
        }
        echo "Parts on tracker ring refs fixed\n";

        echo 'Fixing ring refs for ' . $newfixes->count() . " official parts\n";
        foreach($newfixes as $p) {
            PartSubmitted::dispatch($p, $u, "Update ring primitives via script. Do not hold.");            
            $u->castVote($p, $ftVote);
            PartReviewed::dispatch($p, $u, 'T');
        }
        echo "Official part ring refs fixed\n";

        // Reset the unofficial zip file
        Storage::disk('library')->delete('unofficial/ldrawunf.zip');
        ZipFiles::unofficialZip(Part::unofficial()->first());
        
    }
}
