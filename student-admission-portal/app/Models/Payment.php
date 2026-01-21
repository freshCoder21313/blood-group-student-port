<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PENDING_VERIFICATION = 'pending_verification';

    protected $fillable = [
        'application_id',
        'transaction_code',
        'phone_number',
        'amount',
        'status',
        'merchant_request_id',
        'checkout_request_id',
        'mpesa_receipt_number',
        'result_desc',
        'proof_document_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
