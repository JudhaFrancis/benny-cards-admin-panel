<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\Permission;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // 1. Create or Find Super Admin Role
        // Explicitly set guard_name to 'web' to match the permissions we will force.
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        // 2. Get all permissions
        $permissions = Permission::all();

        // 3. Force guard_name on permission objects strictly for the sync check.
        // The database schema refactor removed/ignored guard_name, but Spatie requires it in memory.
        $permissions->each(function ($permission) {
            $permission->guard_name = 'web';
        });

        // 4. Sync Permissions
        // We pass the Collection of modified models directly.
        $role->syncPermissions($permissions);

        // 5. Find User
        $user = User::where('email', 'admin@gmail.com')->first();

        if ($user) {
            // 6. Assign Role to User
            $user->assignRole($role);
            $this->command->info("Assigned 'super-admin' role to user: {$user->email}");
        } else {
            $this->command->error("User with email 'admin@gmail.com' not found.");
        }
    }
}
