<?php

namespace App\Console\Commands;

use App\Models\MybbUser;
use App\Models\PartLicense;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use App\Models\Part;
use App\Models\VoteType;
use Spatie\Permission\Models\Role;
use Illuminate\Console\Command;
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
        /*;
        Permission::create(['name' => 'omr.create']);
        Permission::create(['name' => 'omr.update']);
        Permission::create(['name' => 'omr.delete']);
        $role = Role::create(['name' => 'OMR Admin']);
        $role->givePermissionTo('omr.create');
        $role->givePermissionTo('omr.update');
        $role->givePermissionTo('omr.delete');
        $role = Role::create(['name' => 'OMR Author']);

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
        foreach (DB::connection('sqlite')->table('omr_file')->orderBy('id')->get() as $model) {
            $omruser = DB::connection('sqlite')->table('omr_author')->find($model->author_id);
            $fname = trim($omruser->first_name);
            $fname = $fname === '-' ? '' : $fname;
            $lname = trim($omruser->last_name);
            $lname = $lname === '-' ? '' : $lname;
            $omrrealname = trim("{$fname} {$lname}");
            $omrrealname = $omrrealname === '' ? null : $omrrealname;
            $nname = trim(str_replace('-', '', $omruser->nickname));
            $libuser = User::fromAuthor($nname, $omrrealname)->first();
            $mybbuser = MybbUser::where('loginname', $nname)->orWhere('username', $omrrealname)->first();
            if (!is_null($libuser)) {
                $uid = $libuser->id ;
                $libuser->assignRole('OMR Author');
            } elseif (!is_null($mybbuser)) {
                $newuser = User::create([
                    'name' => $mybbuser->loginname, 
                    'email' => $mybbuser->email,
                    'realname' => $mybbuser->username,
                    'password' => bcrypt(Str::random(40)),
                    'forum_user_id' => $mybbuser->uid,
                    'part_license_id' => PartLicense::default()->id,
                ]);
                $uid = $newuser->id;
                $newuser->assignRole('OMR Author');
            } else {
                Log::debug("user not found: {$omrrealname} [{$nname}]");
                $rname = is_null($omrrealname) ? $nname : $omrrealname;
                $uname = $nname === '' ? str_replace(' ', '-', $omrrealname) : $nname;
                $newuser = User::create([
                    'name' => $uname, 
                    'email' => str_replace(' ', '', strtolower($uname)) . '@ldraw.org',
                    'realname' => $rname,
                    'password' => bcrypt(Str::random(40)),
                    'part_license_id' => PartLicense::default()->id,
                ]);
                $uid = $newuser->id;
                $newuser->assignRole('OMR Author');
            }
            $omodel = \App\Models\Omr\OmrModel::updateOrCreate(
                ['id' => $model->id],
                [
                    'user_id' => $uid,
                    'set_id' => \App\Models\Omr\Set::firstWhere('number', $model->model_number)->id ?? 1,
                    'part_license_id' => 1,
                    'missing_parts' => $model->missing_parts,
                    'missing_stickers' => $model->missing_stickers,
                    'missing_patterns' => $model->missing_patterns,
                    'approved' => true,
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
        }
    */        
    }
}
