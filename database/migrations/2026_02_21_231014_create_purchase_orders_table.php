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
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('po_number')->unique();
                $table->date('po_date');
                $table->unsignedBigInteger('vendor_id');
                $table->date('expected_delivery_date')->nullable();
                $table->string('status')->default('Draft');
                $table->text('notes')->nullable();
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->unsignedBigInteger('created_by');
                $table->string('current_approver_role')->nullable();
                $table->string('plant')->nullable();
                $table->string('payment_terms')->nullable();
                $table->string('delivery_terms')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
