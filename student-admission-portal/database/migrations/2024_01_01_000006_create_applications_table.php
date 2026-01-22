<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained();
            $table->foreignId('block_id')->nullable()->constrained('academic_blocks');
            $table->string('application_number', 30)->unique();
            $table->string('student_code', 50)->nullable()->unique(); // Added student_code column
            $table->enum('status', [
                'draft',           // Drafting
                'pending_payment', // Waiting for payment
                'pending_approval',// Waiting for approval
                'request_info',    // Requesting info
                'approved',        // Approved
                'rejected'         // Rejected
            ])->default('draft');
            $table->tinyInteger('current_step')->default(1);
            $table->tinyInteger('total_steps')->default(4);
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('application_number');
            $table->index('status');
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
