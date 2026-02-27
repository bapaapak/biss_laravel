<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Column already created as nullable in initial migration
        // MySQL-specific FK operations skipped for SQLite compatibility
    }

    public function down(): void
    {
        // no-op
    }
};
