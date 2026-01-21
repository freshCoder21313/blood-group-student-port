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
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->change();
            $table->foreignId('block_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // We can't easily revert to non-nullable if there are null values.
            // But for structure:
            $table->foreignId('program_id')->nullable(false)->change();
            $table->foreignId('block_id')->nullable(false)->change();
        });
    }
};
