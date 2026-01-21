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
        // 1. Rename columns if they exist and target doesn't
        if (Schema::hasColumn('payments', 'mpesa_phone') && !Schema::hasColumn('payments', 'phone_number')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->renameColumn('mpesa_phone', 'phone_number');
            });
        }
        
        if (Schema::hasColumn('payments', 'mpesa_code') && !Schema::hasColumn('payments', 'transaction_code')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->renameColumn('mpesa_code', 'transaction_code');
            });
        }
        
        if (Schema::hasColumn('payments', 'failure_reason') && !Schema::hasColumn('payments', 'result_desc')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->renameColumn('failure_reason', 'result_desc');
            });
        }

        // 2. Add columns if they still don't exist
        Schema::table('payments', function (Blueprint $table) {
             if (!Schema::hasColumn('payments', 'phone_number')) {
                $table->string('phone_number')->nullable();
            }
             if (!Schema::hasColumn('payments', 'transaction_code')) {
                $table->string('transaction_code')->nullable();
            }
             if (!Schema::hasColumn('payments', 'result_desc')) {
                $table->text('result_desc')->nullable();
            }
        });

        // Modify status enum
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'completed', 'failed', 'submitted', 'verified', 'rejected') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'phone_number')) {
                $table->renameColumn('phone_number', 'mpesa_phone');
            }
            if (Schema::hasColumn('payments', 'transaction_code')) {
                $table->renameColumn('transaction_code', 'mpesa_code');
            }
            if (Schema::hasColumn('payments', 'result_desc')) {
                $table->renameColumn('result_desc', 'failure_reason');
            }
        });
        
        // Revert enum status is tricky without losing data, keeping extended enum is safer or reverting to original if strictly needed.
        // For 'down', we usually try to revert.
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'submitted', 'verified', 'rejected') DEFAULT 'pending'");
    }
};
