<?php

namespace App\Console\Commands;

use App\Models\Part;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeployUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update';

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
        Config::set('database.connections.sqlite.database', '/var/www/librarydev.ldraw.org/ldraworg-library/storage/app/library/db.sqlite3');
        DB::connection('sqlite')->table('omr_theme')->orderBy('id')->each(function ($theme) {
            \App\Models\Omr\Theme::updateOrCreate(
                ['id' => $theme->id],
                ['theme' => $theme->name],
            );
        });
        DB::connection('sqlite')->table('omr_set')->orderBy('id')->each(function ($set) {
            \App\Models\Omr\Set::updateOrCreate(
                ['id' => $set->id],
                [
                    'name' => $set->name,
                    'year' => $set->year,
                    'number' => $set->set_num,
                    'rb_url' => $set->set_img_url,
                    'theme_id' => $set->theme_id,
                ],
            );
        });
        DB::connection('sqlite')->table('omr_file')->orderBy('id')->each(function ($model) {
            $omodel = \App\Models\Omr\OmrModel::updateOrCreate(
                ['id' => $model->id],
                [
                    'user_id' => \App\Models\User::firstWhere('name', DB::connection('sqlite')->table('omr_author')->find($model->author_id)->nickname)->id ?? 290,
                    'set_id' => \App\Models\Omr\Set::firstWhere('number', $model->model_number)->id ?? 1,
                    'part_license_id' => 1,
                    'missing_parts' => $model->missing_parts,
                    'missing_stickers' => $model->missing_stickers,
                    'missing_patterns' => $model->missing_patterns,
                    'alt_model' => !$model->is_main_model,
                    'alt_model_name' => $model->is_main_model ? null : trim($model->alternate_model),
                    'notes' => ['notes' => trim($model->notes)],
                    'created_at' => $model->added,
                ],
            );
            $omodel->refresh();
            if (! Storage::disk('library')->exists("omr/{$omodel->filename()}")) {
                try {
                    $file = file_get_contents("https://omr.ldraw.org/media/" . str_replace(' ', '%20', $model->file));
                } catch (\Exception $e) {
                    Log::debug("Tried URL: https://omr.ldraw.org/media/" . str_replace(' ', '%20', $model->file));
                    Log::debug("Missing file: {$omodel->filename()}");
                    $file = false;
                }
                
                if ($file !== false) {
                    Storage::disk('library')->put("omr/{$omodel->filename()}", $file);
                }    
            }
        });
    }    
}
