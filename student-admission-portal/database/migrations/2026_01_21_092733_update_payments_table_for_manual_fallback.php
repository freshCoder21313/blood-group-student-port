<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Rename proof_image_path if it exists, otherwise add proof_document_path
            if (Schema::hasColumn('payments', 'proof_image_path') && !Schema::hasColumn('payments', 'proof_document_path')) {
                $table->renameColumn('proof_image_path', 'proof_document_path');
            } elseif (!Schema::hasColumn('payments', 'proof_document_path')) {
                $table->string('proof_document_path')->nullable();
            }
        });

        // Modify status enum to include 'pending_verification'
        // We include all previous values + pending_verification
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'completed', 'failed', 'submitted', 'verified', 'rejected', 'pending_verification') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'proof_document_path') && !Schema::hasColumn('payments', 'proof_image_path')) {
                $table->renameColumn('proof_document_path', 'proof_image_path');
            }
        });
        
        // Remove pending_verification from enum
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'completed', 'failed', 'submitted', 'verified', 'rejected') DEFAULT 'pending'");
    }
};
