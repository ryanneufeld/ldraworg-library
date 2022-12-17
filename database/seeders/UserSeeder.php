<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\PartLicense;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $lic = PartLicense::defaultLicense();
        $users = DB::connection('mybb')->table('mybb_users')
          ->select('uid','username', 'email', 'loginname', 'additionalgroups')
          ->where(function($query) {
            $query->whereRaw("instr(concat(',', additionalgroups, ','), ',8,') <> 0")
            ->orWhereRaw("instr(concat(',', additionalgroups, ','), ',9,') <> 0")
            ->orWhereRaw("instr(concat(',', additionalgroups, ','), ',10,') <> 0")
            ->orWhereRaw("instr(concat(',', additionalgroups, ','), ',20,') <> 0");
          })
          ->orderBy('uid')
          ->get();
        foreach($users as $user) {
          $newuser = User::create([
          	'name' => $user->loginname, 
          	'email' => $user->email,
          	'realname' => $user->username,
          	'password' => bcrypt(Str::random(40)),
            'forum_user_id' => $user->uid,
            'part_license_id' => $lic->id,
          ]);
          $newuser->assignRole('Part Author');
          $g = explode(',', $user->additionalgroups);
          if (in_array(10, $g)) {
            $newuser->assignRole('Library Admin');
          }
          if (in_array(20, $g)) {
            $newuser->assignRole('Part Header Admin');
          }
          if (in_array(9, $g)) {
            $newuser->assignRole('Part Reviewer');
          }
          if ($newuser->name == "OrionP") {
            $newuser->assignRole('Super Admin');
          }
        }

        $legacy_users = [
          'Adriano Aicardi',
          'Arne Hackstein',
          'Axel Poque',
          'Bernd Munding',
          'Bert J. Giesen',
          'Bram Lambrecht',
          'Chris Moseley',
          'Damien Duquennoy',
          'David Olofsson',
          'Dennis Osborn',
          'Duane Hess',
          'Frits Blankenzee',
          'Heather Patey',
          'Ishino Keiichiro',
          'James Jessiman',
          'Jeff Stembel',
          'Joachim Probst',
          'John Jensen',
          'Jonathan P. Brown',
          'Joseph H. Cardana',
          'Karim Nassar',
          'Manfred Moolhuysen',
          'Martin G Cormier',
          'Martyn Boogaarts',
          'Nathan Wright',
          'Richard Finegold',
          'Ryan Dennett',
          'Sascha Broich',
        ];
        foreach ($legacy_users as $u) {
          $user = User::create([
          	'name' => $u, 
          	'email' => str_replace(' ', '', strtolower($u)) . '@ldraw.org',
          	'realname' => $u,
          	'password' => bcrypt(Str::random(40)),
            'part_license_id' => $lic->id,
          ]);
          $user->assignRole('Legacy User');
        }

        $virtual_users = [
          'LEGO Digital Designer',
          'LEGO Instructions App',
          'LEGO MINDSTORMS Team',
          'LEGO Technic Team',
          'LEGO Universe Team',
          'LEGO/Unity Microgame',
          'Mecabricks',
          'PTadmin',
          'Non-CA User',
          'CA User',
          'unknown',
        ];
        foreach ($virtual_users as $u) {
          $user = User::create([
          	'name' => $u, 
          	'email' => str_replace(['/',' '], '', strtolower($u)) . '@ldraw.org',
          	'realname' => $u,
          	'password' => bcrypt(Str::random(40)),
            'part_license_id' => $lic->id,
          ]);
          if ($u == 'PTadmin') {
            $user->realname = ' ';
            $user->save();
            $user->assignRole('Part Author');
          }
          else {
            $user->assignRole('Synthetic User');
          }
        }
    }
}
