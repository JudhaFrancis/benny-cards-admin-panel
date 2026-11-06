<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $adminIds = \Illuminate\Support\Facades\DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereIn('roles.name', ['Admin', 'super-admin'])
            ->pluck('users.id')
            ->toArray();

        $tables = [
            'order_client_information',
            'order_designing',
            'order_printing',
            'order_packaging',
            'order_dispatch_delivery'
        ];

        foreach ($tables as $table) {
            \Illuminate\Support\Facades\DB::table($table)->update([
                'assigned_user_ids' => json_encode($adminIds)
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse data backfill
    }
};
