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
        Schema::table('budget_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('budget_plans', 'purpose')) {
                $table->string('purpose', 100)->nullable()->after('description');
            }
        });

        Schema::table('budget_items', function (Blueprint $table) {
            if (!Schema::hasColumn('budget_items', 'brand_spec')) {
                $table->string('brand_spec', 255)->nullable()->after('item_name');
            }
            if (!Schema::hasColumn('budget_items', 'application_process')) {
                $table->string('application_process', 255)->nullable()->after('process');
            }
            if (!Schema::hasColumn('budget_items', 'condition_status')) {
                $table->string('condition_status', 50)->nullable()->after('application_process');
            }
            if (!Schema::hasColumn('budget_items', 'condition_notes')) {
                $table->text('condition_notes')->nullable()->after('condition_status');
            }
            if (!Schema::hasColumn('budget_items', 'target_schedule')) {
                $table->string('target_schedule', 50)->nullable()->after('total_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_plans', function (Blueprint $table) {
            $table->dropColumn('purpose');
        });

        Schema::table('budget_items', function (Blueprint $table) {
            $table->dropColumn(['brand_spec', 'application_process', 'condition_status', 'condition_notes', 'target_schedule']);
        });
    }
};
