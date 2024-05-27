<?php

namespace App\Console\Commands;

use App\Events\PartHeaderEdited;
use App\Events\PartReviewed;
use App\Events\PartSubmitted;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\User;
use Illuminate\Console\Command;

class DeployUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lib:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the app after update deployments';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $parts = Part::has('sticker_sheet')
            ->whereRelation('category', 'category', '<>', 'Sticker')
            ->get();
        
        $scat = PartCategory::firstWhere('category', 'Sticker Shortcut');
        foreach($parts as $p) {
            if ($p->category->category != 'Sticker Shortcut' && $p->category->category != 'Moved' && $p->category->category != 'Obsolete') {
                if ($p->isUnofficial()) {
                    $ch = ['old' => ['category' => $p->category->category], 'new' => ['category' => 'Sticker Shortcut']];
                    $p->category()->associate($scat);
                    $p->save();
                    $p->refresh();
                    $p->generateHeader();
                    $p->save();
                    PartHeaderEdited::dispatch($p, User::find(1), $ch);
                } elseif (is_null($p->unofficial_part)) {
                    $up = app(\App\LDraw\PartManager::class)->copyOfficialToUnofficialPart($p);
                    $up->category()->associate($scat);
                    $up->history()->create([
                        'user_id' => 1,
                        'comment' => 'Change category to Sticker Shortcut'
                    ]);
                    $p->unofficial_part()->associate($up);
                    $p->save();
                    $up->save();
                    $up->refresh();
                    $up->generateHeader();
                    $up->save();
                    PartSubmitted::dispatch($up, User::find(1), 'Change category to Sticker Shortcut');
                    app(\App\LDraw\VoteManager::class)->postVote($up, User::find(1), 'T');
                 }
            }
        }
    }
}
