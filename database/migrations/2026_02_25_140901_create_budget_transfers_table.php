<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('budget_transfers')) {
            Schema::create('budget_transfers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('budget_item_id')->nullable();
                $table->string('item_name');
                $table->unsignedBigInteger('source_plan_id')->nullable();
                $table->string('source_io_number')->nullable();
                $table->string('source_project_name')->nullable();
                $table->unsignedBigInteger('target_plan_id')->nullable();
                $table->string('target_io_number')->nullable();
                $table->string('target_project_name')->nullable();
                $table->string('customer')->nullable();
                $table->string('business_category')->nullable();
                $table->string('fiscal_year', 4)->nullable();
                $table->text('reason');
                $table->string('berita_acara_path');
                $table->string('berita_acara_filename');
                $table->unsignedBigInteger('transferred_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_transfers');
    }
};
