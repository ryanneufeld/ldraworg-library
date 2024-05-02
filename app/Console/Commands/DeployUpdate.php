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
        $rb = app(Rebrickable::class);

        $sheets = [];
        Part::whereRelation('category', 'category', 'Sticker')
            ->whereRelation('type', 'type', 'Part')
            ->where('filename', 'NOT LIKE', 's%')
            ->each(function (Part $p) use (&$sheets) {
                preg_match('#parts\/([0-9]+)[a-z]+\.dat#iu', $p->filename, $m);
                if ($m  && !in_array($m[1], $sheets)) {
                    $sheets[$m[1]] = $m[1];
                }
            });
        foreach($sheets as $sheet) {
            $part = $rb->getPartBySearch($sheet);
            if (is_null($part)) {
                $part = $rb->getPart($sheet);
            }
            $sticker_sheet = StickerSheet::create([
                'number' => $sheet,
                'rebrickable_part_id' => null
            ]);
            if (!is_null($part)) {
                $rb_part = RebrickablePart::create([
                    'part_num' => $part['rb_part_number'],
                    'name' => $part['rb_part_name'],
                    'part_url' => $part['rb_part_url'],
                    'part_img_url' => $part['rb_part_img_url'],
                    'part_id' => null
                ]);
                $sticker_sheet->rebrickable_part()->associate($rb_part);
            }
            $sticker_sheet->save();
            $this->info($sticker_sheet->number . ' = ' . ($sticker_sheet->rebrickable_part->name ?? 'None'));
        }
    }
}
