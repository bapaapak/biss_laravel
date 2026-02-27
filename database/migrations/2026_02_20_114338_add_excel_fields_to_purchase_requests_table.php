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
        Schema::table('purchase_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requests', 'uom')) {
                $table->string('uom', 50)->nullable()->after('qty_req');
            }
            if (!Schema::hasColumn('purchase_requests', 'pic')) {
                $table->string('pic', 255)->nullable()->after('storage_location');
            }
            if (!Schema::hasColumn('purchase_requests', 'item_code')) {
                $table->string('item_code', 100)->nullable()->after('budget_item_id');
            }
            if (!Schema::hasColumn('purchase_requests', 'io_number')) {
                $table->string('io_number', 100)->nullable()->after('pr_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['uom', 'pic', 'item_code']);
        });
    }
};
