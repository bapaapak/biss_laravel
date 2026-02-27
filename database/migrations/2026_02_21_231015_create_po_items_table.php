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
        if (!Schema::hasTable('po_items')) {
            Schema::create('po_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('po_id');
                $table->unsignedBigInteger('pr_item_id')->nullable();
                $table->string('item_description');
                $table->decimal('qty', 10, 2);
                $table->string('uom')->nullable();
                $table->decimal('unit_price', 15, 2);
                $table->decimal('total_price', 15, 2);
                $table->decimal('tax_percent', 5, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_items');
    }
};
