<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Branches move from the settings.branches JSON column into their own table,
 * and orders link to a branch by id (orders.branch_id).
 *
 * order_client_information.order_details.order_placed_in keeps holding the branch name
 * (kept in sync by the app) so existing screens and the mobile app keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('modified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 1. Copy the branch list from settings.branches (keeping its order and active flags)
        $branches = [];
        if (Schema::hasColumn('settings', 'branches')) {
            $json = DB::table('settings')->orderBy('id')->value('branches');
            foreach (json_decode($json ?? '[]', true) ?: [] as $b) {
                $name = trim($b['name'] ?? '');
                if ($name !== '' && !isset($branches[mb_strtolower($name)])) {
                    $branches[mb_strtolower($name)] = ['name' => $name, 'is_active' => (bool) ($b['active'] ?? true)];
                }
            }
        }

        // 2. Add any branch names used on orders that are missing from the list (kept inactive)
        $usedNames = DB::table('order_client_information')
            ->selectRaw("DISTINCT TRIM(JSON_UNQUOTE(JSON_EXTRACT(order_details, '$.order_placed_in'))) AS name")
            ->whereRaw("JSON_EXTRACT(order_details, '$.order_placed_in') IS NOT NULL")
            ->pluck('name');
        foreach ($usedNames as $name) {
            if ($name !== null && $name !== '' && $name !== 'null' && !isset($branches[mb_strtolower($name)])) {
                $branches[mb_strtolower($name)] = ['name' => $name, 'is_active' => false];
            }
        }

        $sort = 1;
        foreach ($branches as $b) {
            DB::table('branches')->insert([
                'name' => $b['name'],
                'is_active' => $b['is_active'],
                'sort_order' => $sort++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. orders.branch_id
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('user_id')
                ->constrained('branches')->nullOnDelete();
        });

        // 4. Link existing orders by the branch name stored on their Client Information stage
        DB::statement("
            UPDATE orders o
            JOIN order_client_information ci ON ci.order_id = o.id AND ci.deleted_at IS NULL
            JOIN branches b ON b.name = TRIM(JSON_UNQUOTE(JSON_EXTRACT(ci.order_details, '$.order_placed_in')))
            SET o.branch_id = b.id
        ");

        // 5. The list now lives in the branches table
        if (Schema::hasColumn('settings', 'branches')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('branches');
            });
        }

        // 6. "Branch" permission module (Administration → Branches), granted to Admin and super-admin
        $moduleId = DB::table('modules')->where('name', 'Branch')->value('id')
            ?? DB::table('modules')->insertGetId(['name' => 'Branch', 'created_at' => now(), 'updated_at' => now()]);

        foreach (['List', 'View', 'Create', 'Edit', 'Delete'] as $actionName) {
            $actionId = DB::table('actions')->where('name', $actionName)->value('id')
                ?? DB::table('actions')->insertGetId(['name' => $actionName, 'created_at' => now(), 'updated_at' => now()]);

            $permissionId = DB::table('permissions')->where(['module_id' => $moduleId, 'action_id' => $actionId])->value('id')
                ?? DB::table('permissions')->insertGetId(['module_id' => $moduleId, 'action_id' => $actionId, 'created_at' => now(), 'updated_at' => now()]);

            foreach (DB::table('roles')->whereIn('name', ['Admin', 'super-admin'])->pluck('id') as $roleId) {
                DB::table('role_has_permission')->updateOrInsert(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $moduleId = DB::table('modules')->where('name', 'Branch')->value('id');
        if ($moduleId) {
            $permissionIds = DB::table('permissions')->where('module_id', $moduleId)->pluck('id');
            DB::table('role_has_permission')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
            DB::table('modules')->where('id', $moduleId)->delete();
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->json('branches')->nullable()->after('app_version');
        });

        $list = DB::table('branches')->whereNull('deleted_at')->orderBy('sort_order')->get()
            ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name, 'active' => (bool) $b->is_active])
            ->values();
        DB::table('settings')->update(['branches' => json_encode($list)]);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::dropIfExists('branches');
    }
};
