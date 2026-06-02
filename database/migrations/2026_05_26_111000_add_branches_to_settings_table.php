<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('branches')->nullable()->after('app_version');
        });

        // Seed initial branches to avoid breaking existing functionality
        $initialBranches = [
            ['id' => 1, 'name' => 'NGL', 'active' => true],
            ['id' => 2, 'name' => 'MTM', 'active' => true],
            ['id' => 3, 'name' => 'TVL', 'active' => true],
            ['id' => 4, 'name' => 'Chennai', 'active' => true],
            ['id' => 5, 'name' => 'Online', 'active' => true],
        ];

        DB::table('settings')->update(['branches' => json_encode($initialBranches)]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('branches');
        });
    }
};
