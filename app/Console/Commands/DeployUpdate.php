<?php

namespace App\Console\Commands;

use App\LDraw\Parse\Parser;
use App\LDraw\PartManager;
use App\LDraw\Rebrickable;
use App\Models\MybbUser;
use App\Models\PartLicense;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use App\Models\Part;
use App\Models\PartRelease;
use App\Models\PartType;
use App\Models\Rebrickable\RebrickablePart;
use App\Models\StickerSheet;
use App\Models\VoteType;
use Spatie\Permission\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        StickerSheet::all()->each(function (StickerSheet $sheet) {
            Part::where('filename', 'LIKE', "parts/{$sheet->number}%.dat")
                ->whereRelation('category', 'category', 'Sticker')
                ->whereRelation('type', 'folder', 'parts/')
                ->each(fn (Part $part) => 
                    $part->ancestorsAndSelf()->update(['sticker_sheet_id' => $sheet->id])
                );  
        });

        Part::unofficial()->whereJsonLength('missing_parts', '>', 0)->each(fn (Part $p) =>
            app(PartManager::class)->loadSubpartsFromBody($p)
        );
    }
}
