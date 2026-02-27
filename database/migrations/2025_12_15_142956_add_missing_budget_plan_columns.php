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
        Schema::table('budget_plans', function (Blueprint $table) {
            // Only add columns that don't exist yet
            if (!Schema::hasColumn('budget_plans', 'end_year')) {
                $table->integer('end_year')->nullable()->after('start_year');
            }
            if (!Schema::hasColumn('budget_plans', 'department')) {
                $table->string('department', 100)->nullable()->after('end_year');
            }
            if (!Schema::hasColumn('budget_plans', 'io_number')) {
                $table->string('io_number', 50)->nullable()->after('department');
            }
            if (!Schema::hasColumn('budget_plans', 'cc_code')) {
                $table->string('cc_code', 50)->nullable()->after('io_number');
            }
            if (!Schema::hasColumn('budget_plans', 'investment_type')) {
                $table->string('investment_type', 50)->nullable()->after('cc_code');
            }
            if (!Schema::hasColumn('budget_plans', 'customer')) {
                $table->string('customer', 100)->nullable()->after('investment_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_plans', function (Blueprint $table) {
            $columns = ['end_year', 'department', 'io_number', 'cc_code', 'investment_type', 'customer'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('budget_plans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
