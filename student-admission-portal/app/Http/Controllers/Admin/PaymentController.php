<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::where('status', Payment::STATUS_PENDING_VERIFICATION)
            ->with('application.student')
            ->latest()
            ->get();

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        return view('admin.payments.show', compact('payment'));
    }

    public function downloadProof(Payment $payment)
    {
        if (! $payment->proof_document_path || ! \Illuminate\Support\Facades\Storage::disk('private')->exists($payment->proof_document_path)) {
            abort(404);
        }

        return \Illuminate\Support\Facades\Storage::disk('private')->response($payment->proof_document_path);
    }

    public function approve(Payment $payment)
    {
        $payment->update([
            'status' => Payment::STATUS_COMPLETED,
        ]);

        if ($payment->application) {
            // Ensure application is in the correct state for ASP sync
            $payment->application->update([
                'status' => 'pending_approval',
            ]);

            if ($payment->application->student && $payment->application->student->user) {
                $payment->application->student->user->notify(new \App\Notifications\PaymentVerified($payment));
            }
        }
        
        return redirect()->route('admin.payments.index')->with('status', 'Payment approved successfully.');
    }

    public function reject(Payment $payment)
    {
        $payment->update([
            'status' => Payment::STATUS_FAILED,
        ]);

        if ($payment->application) {
            $payment->application->update([
                'status' => 'draft', // Revert to draft so they can fix it
            ]);
            
            if ($payment->application->student && $payment->application->student->user) {
                $payment->application->student->user->notify(new \App\Notifications\PaymentRejected($payment));
            }
        }

        return redirect()->route('admin.payments.index')->with('status', 'Payment rejected.');
    }
}
