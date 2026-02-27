<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('master_storage_locations')) {
            Schema::create('master_storage_locations', function (Blueprint $table) {
                $table->id();
                $table->string('sloc', 50)->unique();
                $table->string('description', 255)->nullable();
                $table->string('status', 20)->default('Active');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('master_storage_locations');
    }
};
