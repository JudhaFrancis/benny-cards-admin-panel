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
        if (!Schema::hasTable('order_enquiries')) {
            Schema::create('order_enquiries', function (Blueprint $table) {
                $table->id();
                $table->string('enquiry_number', 191)->unique();
                $table->string('source', 50)->default('walk_in'); // walk_in, website, instagram, phone, whatsapp, other
                $table->string('reference', 191)->nullable();
                $table->unsignedBigInteger('user_id')->nullable()->index(); // website customer if logged in
                
                // Customer details
                $table->string('customer_name', 191);
                $table->string('customer_phone', 50)->index();
                $table->string('customer_email', 191)->nullable();
                $table->text('customer_address')->nullable();
                $table->string('occasion', 191)->nullable();
                $table->string('order_placed_in', 191)->nullable(); // branch/location

                // Requirement details
                $table->string('card_type', 50)->nullable(); // customize / ready_made
                $table->string('card_size', 50)->nullable();
                $table->text('specifications')->nullable();
                $table->string('budget_tier', 50)->nullable();
                $table->date('expected_delivery_date')->nullable();
                $table->text('message')->nullable();
                $table->json('attachments')->nullable();

                // Workflow & Tracking
                $table->string('status', 50)->default('new')->index(); // new, under_review, quoted, approved, confirmed, not_approved
                $table->string('priority', 20)->default('P4'); // P1, P2, P3, P4
                $table->json('assigned_user_ids')->nullable();
                $table->string('assigned_to', 191)->nullable();
                $table->dateTime('next_follow_up_at')->nullable()->index();
                $table->decimal('quoted_amount', 10, 2)->nullable();
                $table->text('lost_reason')->nullable();

                // Conversion link
                $table->unsignedBigInteger('converted_order_id')->nullable()->index();
                $table->timestamp('converted_at')->nullable();

                // Audit fields
                $table->unsignedBigInteger('added_by')->nullable()->index();
                $table->unsignedBigInteger('modified_by')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('converted_order_id')->references('id')->on('orders')->onDelete('set null');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_enquiries');
    }
};
