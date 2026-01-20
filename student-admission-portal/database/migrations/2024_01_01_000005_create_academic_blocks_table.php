<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Sep 2024 Intake"
            $table->integer('year');
            $table->string('intake'); // e.g. "September"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_blocks');
    }
};
