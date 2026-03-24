<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tables = [
        'order_client_information',
        'order_designing',
        'order_printing',
        'order_packaging',
        'order_dispatch_delivery'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'audit_details')) {
                    $table->dropColumn('audit_details');
                }
                $table->foreignId('added_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('modified_by')->nullable()->constrained('users')->onDelete('set null');
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->json('audit_details')->nullable();
                $table->dropForeign(['added_by']);
                $table->dropColumn('added_by');
                $table->dropForeign(['modified_by']);
                $table->dropColumn('modified_by');
                $table->dropSoftDeletes();
            });
        }
    }
};
