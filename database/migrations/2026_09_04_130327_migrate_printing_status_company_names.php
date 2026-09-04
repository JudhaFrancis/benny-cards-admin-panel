<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::table('order_printing')
            ->orderBy('id')
            ->chunk(100, function ($records) {
                foreach ($records as $record) {
                    if (empty($record->printing_status)) {
                        continue;
                    }

                    $data = json_decode($record->printing_status, true);
                    
                    if (is_array($data) && isset($data['company_name']) && !isset($data['company_names'])) {
                        $data['company_names'] = [$data['company_name']];
                        unset($data['company_name']);

                        \Illuminate\Support\Facades\DB::table('order_printing')
                            ->where('id', $record->id)
                            ->update([
                                'printing_status' => json_encode($data)
                            ]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration as data loss would occur
    }
};
