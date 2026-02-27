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
        // MySQL-specific MODIFY COLUMN - skipped for SQLite compatibility
        // Status column already supports these values as a string column
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `budget_plans` MODIFY COLUMN `status` ENUM('Draft', 'Approved', 'Rejected') NOT NULL DEFAULT 'Draft'");
    }
};
