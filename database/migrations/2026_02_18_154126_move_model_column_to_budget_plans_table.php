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
        // Drop model from budget_items if somehow exists
        if (Schema::hasColumn('budget_items', 'model')) {
            Schema::table('budget_items', function (Blueprint $table) {
                $table->dropColumn('model');
            });
        }

        // Add model to budget_plans only if missing
        if (!Schema::hasColumn('budget_plans', 'model')) {
            Schema::table('budget_plans', function (Blueprint $table) {
                $table->string('model')->nullable()->after('customer');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove from budget_plans
        Schema::table('budget_plans', function (Blueprint $table) {
            $table->dropColumn('model');
        });

        // Add back to budget_items
        Schema::table('budget_items', function (Blueprint $table) {
            $table->string('model')->nullable()->after('item_name');
        });
    }
};
