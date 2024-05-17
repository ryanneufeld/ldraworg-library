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
use App\Models\PartRenderView;
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
        $settings = app(\App\Settings\LibrarySettings::class);
        
        $settings->ldview_options = config('ldraw.render.options');
        $settings->max_render_height = config('ldraw.image.normal.height');
        $settings->max_render_width = config('ldraw.image.normal.width');
        $settings->max_thumb_height = config('ldraw.image.thumb.height');
        $settings->max_thumb_width = config('ldraw.image.thumb.width');
        
        $settings->allowed_header_metas = config('ldraw.allowed_metas.header');
        $settings->allowed_body_metas = config('ldraw.allowed_metas.body');
        
        $settings->default_part_license_id = PartLicense::firstWhere('name', 'CC_BY_4')->id;
        $settings->quick_search_limit = config('ldraw.search.quicksearch.limit');
        
        $settings->pattern_codes = config('ldraw.pattern-codes');
        
        $settings->save();
    }
}
