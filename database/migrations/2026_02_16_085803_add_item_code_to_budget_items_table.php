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
        if (!Schema::hasColumn('budget_items', 'item_code')) {
            Schema::table('budget_items', function (Blueprint $table) {
                $table->string('item_code')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_items', function (Blueprint $table) {
            if (Schema::hasColumn('budget_items', 'item_code')) {
                $table->dropColumn('item_code');
            }
        });
    }
};
