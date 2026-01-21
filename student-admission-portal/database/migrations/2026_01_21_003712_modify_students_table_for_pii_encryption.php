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
        Schema::table('students', function (Blueprint $table) {
            $table->text('national_id')->nullable()->change();
            $table->text('passport_number')->nullable()->change();
            
            $table->string('national_id_index')->nullable()->after('national_id')->index();
            $table->string('passport_number_index')->nullable()->after('passport_number')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['national_id_index', 'passport_number_index']);
            
            // Revert to text instead of string(50) to prevent data truncation of encrypted values
            $table->text('national_id')->nullable()->change();
            $table->text('passport_number')->nullable()->change();
        });
    }
};
