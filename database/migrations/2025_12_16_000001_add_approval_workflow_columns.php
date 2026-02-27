<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Columns already included in initial migration - no-op
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn([
                'current_approver_role',
                'dept_head_id', 'dept_head_approved_at',
                'finance_id', 'finance_approved_at',
                'div_head_id', 'div_head_approved_at',
                'purchasing_id', 'purchasing_executed_at'
            ]);
        });

        Schema::table('budget_plans', function (Blueprint $table) {
            $table->dropColumn([
                'current_approver_role',
                'dept_head_id', 'dept_head_approved_at',
                'div_head_id', 'div_head_approved_at',
                'finance_id', 'finance_approved_at'
            ]);
        });
    }
};
