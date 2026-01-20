<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // M-Pesa specific fields
            $table->string('checkout_request_id')->nullable()->after('mpesa_phone')->index();
            $table->string('merchant_request_id')->nullable()->after('checkout_request_id');
            $table->string('transaction_ref')->nullable()->after('application_id')->index();
            $table->string('payment_method')->default('mpesa')->after('amount'); // mpesa, bank_transfer, cash
            $table->decimal('paid_amount', 10, 2)->nullable()->after('amount');
            $table->string('mpesa_receipt_number')->nullable()->after('mpesa_code');
            $table->string('failure_reason')->nullable()->after('status');
            $table->json('callback_data')->nullable()->after('verification_notes');
            $table->timestamp('initiated_at')->nullable()->after('created_at');
            $table->timestamp('completed_at')->nullable()->after('initiated_at');
            $table->boolean('manual_submission')->default(false);
            $table->string('proof_image_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'checkout_request_id',
                'merchant_request_id',
                'transaction_ref',
                'payment_method',
                'paid_amount',
                'mpesa_receipt_number',
                'failure_reason',
                'callback_data',
                'initiated_at',
                'completed_at',
                'manual_submission',
                'proof_image_path'
            ]);
        });
    }
};
