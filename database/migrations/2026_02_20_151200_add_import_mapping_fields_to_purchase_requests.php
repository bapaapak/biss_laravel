<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requests', 'department')) {
                $table->string('department', 100)->nullable()->after('pr_number');
            }
            if (!Schema::hasColumn('purchase_requests', 'cost_center')) {
                $table->string('cost_center', 100)->nullable()->after('io_number');
            }
            if (!Schema::hasColumn('purchase_requests', 'plant')) {
                $table->string('plant', 100)->nullable()->after('storage_location');
            }
            if (!Schema::hasColumn('purchase_requests', 'business_category')) {
                $table->string('business_category', 100)->nullable()->after('department');
            }
            if (!Schema::hasColumn('purchase_requests', 'periode')) {
                $table->string('periode', 50)->nullable()->after('business_category');
            }
            if (!Schema::hasColumn('purchase_requests', 'total_price')) {
                $table->decimal('total_price', 15, 2)->nullable()->after('estimated_price');
            }
            if (!Schema::hasColumn('purchase_requests', 'budget_link')) {
                $table->string('budget_link', 255)->nullable()->after('budget_item_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['department', 'cost_center', 'plant', 'business_category', 'periode', 'total_price', 'budget_link']);
        });
    }
};
