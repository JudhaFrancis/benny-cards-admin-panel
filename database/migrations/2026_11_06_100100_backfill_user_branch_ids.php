<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fill users.branch_ids for existing users:
 *  - super-admin / admin  -> every branch
 *  - User role            -> [] (no branches)
 *  - every other role     -> branches of the orders they are assigned to in any stage table
 *                            (assigned_user_ids JSON on the 5 stage tables, joined to orders.branch_id)
 */
return new class extends Migration
{
    private array $stageTables = [
        'order_client_information',
        'order_designing',
        'order_printing',
        'order_packaging',
        'order_dispatch_delivery',
    ];

    public function up(): void
    {
        $allBranchIds = DB::table('branches')->whereNull('deleted_at')->orderBy('id')->pluck('id')
            ->map(fn ($id) => (int) $id)->values()->all();

        // user_id => [branch_id => true]
        $assigned = [];
        foreach ($this->stageTables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'assigned_user_ids')) {
                continue;
            }
            DB::table($table)
                ->join('orders', 'orders.id', '=', "{$table}.order_id")
                ->whereNotNull('orders.branch_id')
                ->whereNotNull("{$table}.assigned_user_ids")
                ->select("{$table}.id as row_id", "{$table}.assigned_user_ids", 'orders.branch_id')
                ->orderBy("{$table}.id")
                ->chunk(1000, function ($rows) use (&$assigned) {
                    foreach ($rows as $row) {
                        $ids = json_decode($row->assigned_user_ids, true);
                        if (!is_array($ids)) {
                            continue;
                        }
                        foreach ($ids as $userId) {
                            if (is_numeric($userId)) {
                                $assigned[(int) $userId][(int) $row->branch_id] = true;
                            }
                        }
                    }
                });
        }

        $validBranches = array_flip($allBranchIds);
        $noBranch = [];

        DB::table('users')
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->select('users.id', 'users.name', 'roles.name as role_name')
            ->orderBy('users.id')
            ->chunk(500, function ($users) use ($allBranchIds, $assigned, $validBranches, &$noBranch) {
                foreach ($users as $user) {
                    $role = strtolower(trim($user->role_name ?? ''));

                    if (in_array($role, ['super-admin', 'admin'], true)) {
                        $ids = $allBranchIds;
                    } elseif ($role === 'user' || $role === '') {
                        $ids = [];
                    } else {
                        $ids = array_keys(array_intersect_key($assigned[$user->id] ?? [], $validBranches));
                        sort($ids);
                        if (!$ids) {
                            $noBranch[] = "#{$user->id} {$user->name} ({$user->role_name})";
                        }
                    }

                    DB::table('users')->where('id', $user->id)->update(['branch_ids' => json_encode(array_values($ids))]);
                }
            });

        if ($noBranch && app()->runningInConsole()) {
            echo PHP_EOL . '  Staff users with no branch (assign in Users > Edit):' . PHP_EOL;
            foreach ($noBranch as $line) {
                echo "    - {$line}" . PHP_EOL;
            }
        }
    }

    public function down(): void
    {
        DB::table('users')->update(['branch_ids' => null]);
    }
};
