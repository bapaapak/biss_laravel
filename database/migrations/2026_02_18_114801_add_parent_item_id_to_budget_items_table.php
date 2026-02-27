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
        if (!Schema::hasColumn('budget_items', 'parent_item_id')) {
            Schema::table('budget_items', function (Blueprint $table) {
                $table->integer('parent_item_id')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_items', function (Blueprint $table) {
            $table->dropForeign(['parent_item_id']);
            $table->dropColumn('parent_item_id');
        });
    }
};
