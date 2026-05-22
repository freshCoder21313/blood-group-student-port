<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Application;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;

class ManualPaymentProcessor implements PaymentProcessorInterface
{
    /**
     * Process the manual payment by recording the transaction code and uploaded proof document.
     */
    public function process(Application $application, array $data): Payment
    {
        $path = null;
        if (isset($data['proof_document']) && $data['proof_document'] instanceof UploadedFile) {
            $path = $data['proof_document']->store('payment_proofs', 'private');
        }

        $payment = Payment::where('application_id', $application->id)
            ->where('status', Payment::STATUS_PENDING)
            ->first();

        if (! $payment) {
            $payment = new Payment;
            $payment->application_id = $application->id;
        }

        $payment->status = Payment::STATUS_PENDING_VERIFICATION;
        $payment->transaction_code = $data['transaction_code'];
        $payment->amount = $data['amount'] ?? $payment->amount ?? 0;
        $payment->proof_document_path = $path;
        $payment->manual_submission = true;
        $payment->save();

        return $payment;
    }
}
