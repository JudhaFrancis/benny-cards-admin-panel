<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add role_id column
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('password');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });

        // 2. Migrate existing data from 'role' to 'role_id'
        $users = DB::table('users')->select('id', 'role')->get();
        foreach ($users as $user) {
            $roleId = null;
            $roleName = strtolower($user->role);

            if ($roleName === 'admin' || $roleName === 'super-admin') {
                // Prefer super-admin (1) for 'admin' if available, or just map to super-admin
                $roleId = 1;
            } elseif ($roleName === 'staff') {
                $roleId = 4;
            } elseif ($roleName === 'user') {
                $roleId = 5;
            }

            if ($roleId) {
                DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
            }
        }

        // 3. Drop the 'role' column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('password');
        });

        // Optional: migrate data back if possible
        $users = DB::table('users')->select('id', 'role_id')->get();
        foreach ($users as $user) {
            $roleName = null;
            if ($user->role_id == 1)
                $roleName = 'admin';
            elseif ($user->role_id == 4)
                $roleName = 'staff';
            elseif ($user->role_id == 5)
                $roleName = 'user';

            if ($roleName) {
                DB::table('users')->where('id', $user->id)->update(['role' => $roleName]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
