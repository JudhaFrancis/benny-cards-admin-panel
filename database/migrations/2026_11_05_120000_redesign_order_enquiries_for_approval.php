<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Module;
use App\Models\Action;
use App\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Enquiry redesign: sales staff create enquiries with exactly the Create Order form,
 * and an admin approves (creates the order) or rejects them.
 *
 * Starting point: the tables created by the 2026_10_29_14000x migrations.
 *
 * order_enquiries
 *   + address_1, address_2, remarks, discount, extra_charges,
 *     approved_by, approved_at, rejected_by, rejected_at, rejection_reason
 *   ~ quoted_amount  -> total_amount (grand total, NOT NULL default 0)
 *   ~ status         -> ENUM('pending','approved','rejected') default 'pending'
 *   - source, reference, customer_address, occasion, order_placed_in, card_type, card_size,
 *     specifications, expected_delivery_date, message, attachments, priority, assigned_user_ids,
 *     assigned_to, next_follow_up_at, lost_reason, converted_at
 *   ~ enquiry_number prefix ENQ- -> EQ- (EQ-NOV26001, same format as OD-NOV26001)
 * order_enquiry_items
 *   + discount_amount   - specifications
 * order_enquiry_activities  -> dropped (approval/rejection details live on order_enquiries)
 * permissions
 *   + Enquiry → Approve, granted to Admin and super-admin
 *
 * Every step checks the current schema first, so it is safe to run on a database in any
 * state between the 2026_10_29 migrations and the final schema.
 */
return new class extends Migration
{
    protected array $droppedEnquiryColumns = [
        'source', 'reference', 'customer_address', 'occasion', 'order_placed_in', 'card_type',
        'card_size', 'specifications', 'expected_delivery_date', 'message', 'attachments',
        'priority', 'assigned_user_ids', 'assigned_to', 'next_follow_up_at', 'lost_reason',
        'converted_at',
    ];

    public function up(): void
    {
        $has = fn (string $column) => Schema::hasColumn('order_enquiries', $column);

        // 1. New columns
        Schema::table('order_enquiries', function (Blueprint $table) use ($has) {
            if (!$has('address_1')) {
                $table->text('address_1')->nullable()->after('customer_email');
            }
            if (!$has('address_2')) {
                $table->text('address_2')->nullable()->after('address_1');
            }
            if (!$has('remarks')) {
                $table->text('remarks')->nullable()->after('budget_tier');
            }
            if (!$has('discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after($has('quoted_amount') ? 'quoted_amount' : 'total_amount');
            }
            if (!$has('extra_charges')) {
                $table->decimal('extra_charges', 10, 2)->default(0)->after('discount');
            }
            if (!$has('approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('converted_order_id');
            }
            if (!$has('approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!$has('rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            }
            if (!$has('rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!$has('rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });

        if (!Schema::hasColumn('order_enquiry_items', 'discount_amount')) {
            Schema::table('order_enquiry_items', function (Blueprint $table) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('unit_price');
            });
        }

        // 2. Carry existing data into the new columns before anything is dropped
        if ($has('customer_address')) {
            DB::table('order_enquiries')->whereNull('address_1')->whereNotNull('customer_address')
                ->update(['address_1' => DB::raw('customer_address')]);
        }

        $noteColumns = array_values(array_filter(['specifications', 'message'], $has));
        if ($noteColumns) {
            $parts = array_merge(["NULLIF(remarks, '')"], array_map(fn ($c) => "NULLIF({$c}, '')", $noteColumns));
            DB::statement("UPDATE order_enquiries SET remarks = NULLIF(TRIM(CONCAT_WS('\n', " . implode(', ', $parts) . ")), '')");
        }

        if ($has('lost_reason')) {
            DB::table('order_enquiries')->whereNull('rejection_reason')->whereNotNull('lost_reason')
                ->update(['rejection_reason' => DB::raw('lost_reason')]);
        }

        if ($has('converted_at')) {
            DB::table('order_enquiries')->whereNull('approved_at')->whereNotNull('converted_at')
                ->update(['approved_at' => DB::raw('converted_at')]);
        }

        // 3. Old statuses → pending / approved / rejected
        DB::table('order_enquiries')->whereNotNull('converted_order_id')->update(['status' => 'approved']);
        DB::table('order_enquiries')->whereNull('converted_order_id')
            ->whereIn('status', ['confirmed', 'approved'])->update(['status' => 'pending']);
        DB::table('order_enquiries')->where('status', 'not_approved')->update(['status' => 'rejected']);
        DB::table('order_enquiries')->whereNotIn('status', ['pending', 'approved', 'rejected'])
            ->update(['status' => 'pending']);

        DB::statement("ALTER TABLE order_enquiries MODIFY status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");

        // 4. quoted_amount → total_amount
        if ($has('quoted_amount')) {
            DB::table('order_enquiries')->whereNull('quoted_amount')->update(['quoted_amount' => 0]);
            DB::statement('ALTER TABLE order_enquiries CHANGE quoted_amount total_amount DECIMAL(10,2) NOT NULL DEFAULT 0');
        }

        // 5. Drop columns that are no longer used
        $toDrop = array_values(array_filter($this->droppedEnquiryColumns, $has));
        if ($toDrop) {
            Schema::table('order_enquiries', function (Blueprint $table) use ($toDrop) {
                $table->dropColumn($toDrop);
            });
        }

        if (Schema::hasColumn('order_enquiry_items', 'specifications')) {
            Schema::table('order_enquiry_items', function (Blueprint $table) {
                $table->dropColumn('specifications');
            });
        }

        Schema::dropIfExists('order_enquiry_activities');

        // 6. Enquiry number prefix ENQ- → EQ-
        DB::statement("UPDATE order_enquiries SET enquiry_number = CONCAT('EQ-', SUBSTRING(enquiry_number, 5)) WHERE enquiry_number LIKE 'ENQ-%'");

        // 7. Enquiry → Approve permission for Admin and super-admin
        $module = Module::firstOrCreate(['name' => 'Enquiry']);
        $action = Action::firstOrCreate(['name' => 'Approve']);
        $permission = Permission::firstOrCreate([
            'module_id' => $module->id,
            'action_id' => $action->id,
        ]);

        foreach (DB::table('roles')->whereIn('name', ['Admin', 'super-admin'])->pluck('id') as $roleId) {
            DB::table('role_has_permission')->updateOrInsert([
                'permission_id' => $permission->id,
                'role_id' => $roleId,
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Restores the column layout of the 2026_10_29 migrations. Data in dropped columns cannot be recovered.
     */
    public function down(): void
    {
        $module = Module::where('name', 'Enquiry')->first();
        $action = Action::where('name', 'Approve')->first();
        if ($module && $action) {
            $permission = Permission::where('module_id', $module->id)->where('action_id', $action->id)->first();
            if ($permission) {
                DB::table('role_has_permission')->where('permission_id', $permission->id)->delete();
                $permission->delete();
            }
        }

        DB::statement("UPDATE order_enquiries SET enquiry_number = CONCAT('ENQ-', SUBSTRING(enquiry_number, 4)) WHERE enquiry_number LIKE 'EQ-%'");

        if (!Schema::hasTable('order_enquiry_activities')) {
            Schema::create('order_enquiry_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('enquiry_id')->index();
                $table->string('type', 50)->default('note');
                $table->string('from_status', 50)->nullable();
                $table->string('to_status', 50)->nullable();
                $table->decimal('quoted_amount', 10, 2)->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
                $table->foreign('enquiry_id')->references('id')->on('order_enquiries')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        Schema::table('order_enquiry_items', function (Blueprint $table) {
            $table->text('specifications')->nullable()->after('total_price');
            $table->dropColumn('discount_amount');
        });

        DB::statement('ALTER TABLE order_enquiries CHANGE total_amount quoted_amount DECIMAL(10,2) NULL');
        DB::statement("ALTER TABLE order_enquiries MODIFY status VARCHAR(50) NOT NULL DEFAULT 'new'");
        DB::table('order_enquiries')->where('status', 'pending')->update(['status' => 'new']);
        DB::table('order_enquiries')->where('status', 'approved')->update(['status' => 'confirmed']);
        DB::table('order_enquiries')->where('status', 'rejected')->update(['status' => 'not_approved']);

        Schema::table('order_enquiries', function (Blueprint $table) {
            $table->string('source', 50)->default('walk_in')->after('enquiry_number');
            $table->string('reference', 191)->nullable()->after('source');
            $table->text('customer_address')->nullable()->after('customer_email');
            $table->string('occasion', 191)->nullable()->after('customer_address');
            $table->string('order_placed_in', 191)->nullable()->after('occasion');
            $table->string('card_type', 50)->nullable()->after('order_placed_in');
            $table->string('card_size', 50)->nullable()->after('card_type');
            $table->text('specifications')->nullable()->after('card_size');
            $table->date('expected_delivery_date')->nullable()->after('budget_tier');
            $table->text('message')->nullable()->after('expected_delivery_date');
            $table->json('attachments')->nullable()->after('message');
            $table->string('priority', 20)->default('P4')->after('status');
            $table->json('assigned_user_ids')->nullable()->after('priority');
            $table->string('assigned_to', 191)->nullable()->after('assigned_user_ids');
            $table->dateTime('next_follow_up_at')->nullable()->after('assigned_to');
            $table->text('lost_reason')->nullable()->after('quoted_amount');
            $table->timestamp('converted_at')->nullable()->after('converted_order_id');
        });

        DB::statement('UPDATE order_enquiries SET customer_address = address_1, message = remarks, lost_reason = rejection_reason, converted_at = approved_at');

        Schema::table('order_enquiries', function (Blueprint $table) {
            $table->dropColumn([
                'address_1', 'address_2', 'remarks', 'discount', 'extra_charges',
                'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason',
            ]);
        });
    }
};
