<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Assign a super admin. Permissions are hard coded.
        $role = Role::create(['name' => 'Super Admin']);
        
        $role = Role::create(['name' => 'Site Admin']);
        $role->givePermissionTo('user');

        $role = Role::create(['name' => 'Library Admin']);
        $role->givePermissionTo('part');
        $role->givePermissionTo('user.modify.email');
        $role->givePermissionTo('user.modify.role.nonadmin');
        $role->givePermissionTo('user.add.nonadmin');

        $role = Role::create(['name' => 'Part Admin']);
        $role->givePermissionTo('part');

        $role = Role::create(['name' => 'Part Header Admin']);
        $role->givePermissionTo('part.edit.header');

        $role = Role::create(['name' => 'Part Reviewer']);
        $role->givePermissionTo('part.vote.hold');
        $role->givePermissionTo('part.vote.certify');
        $role->givePermissionTo('part.comment');

        $role = Role::create(['name' => 'Part Author']);
        $role->givePermissionTo('part.submit.regular');
        $role->givePermissionTo('part.own.vote.hold');
        $role->givePermissionTo('part.own.comment');

        $role = Role::create(['name' => 'Legacy User']);
        $role = Role::create(['name' => 'Synthetic User']);

    }
}
