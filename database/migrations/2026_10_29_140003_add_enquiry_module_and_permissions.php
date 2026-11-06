<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Module;
use App\Models\Action;
use App\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure Module 'Enquiry' exists
        $module = Module::firstOrCreate(['name' => 'Enquiry']);

        // 2. Ensure standard Actions exist
        $actions = ['List', 'View', 'Create', 'Edit', 'Delete', 'Export'];
        $permissionIds = [];

        foreach ($actions as $actionName) {
            $action = Action::firstOrCreate(['name' => $actionName]);
            $perm = Permission::firstOrCreate([
                'module_id' => $module->id,
                'action_id' => $action->id,
            ]);
            $permissionIds[] = $perm->id;
        }

        // 3. Grant permissions to Admin and super-admin roles
        $roles = DB::table('roles')->whereIn('name', ['Admin', 'super-admin'])->pluck('id');

        foreach ($roles as $roleId) {
            foreach ($permissionIds as $permId) {
                DB::table('role_has_permission')->updateOrInsert([
                    'permission_id' => $permId,
                    'role_id' => $roleId,
                ]);
            }
        }

        // Clear Spatie cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $module = Module::where('name', 'Enquiry')->first();
        if ($module) {
            $permIds = Permission::where('module_id', $module->id)->pluck('id');
            DB::table('role_has_permission')->whereIn('permission_id', $permIds)->delete();
            Permission::where('module_id', $module->id)->delete();
            $module->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
