@props(['payment'])

@if($payment->status === 'pending_verification')
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
        <div class="flex items-center mb-2">
            <svg class="w-6 h-6 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-medium text-yellow-800">Payment Under Verification</h3>
        </div>
        
        <div class="text-sm text-yellow-700 space-y-1">
            <p><span class="font-semibold">Transaction Code:</span> {{ $payment->transaction_code }}</p>
            <p><span class="font-semibold">Amount:</span> KES {{ number_format($payment->amount, 2) }}</p>
            <p><span class="font-semibold">Date:</span> {{ $payment->updated_at->format('d M Y, H:i') }}</p>
            <p class="mt-2 text-xs">This payment is pending admin verification.</p>
        </div>
    </div>
@else
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
        <div class="flex items-center mb-2">
            <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <h3 class="text-lg font-medium text-green-800">Payment Successful</h3>
        </div>
        
        <div class="text-sm text-green-700 space-y-1">
            <p><span class="font-semibold">Transaction Code:</span> {{ $payment->transaction_code }}</p>
            <p><span class="font-semibold">Amount:</span> KES {{ number_format($payment->amount, 2) }}</p>
            <p><span class="font-semibold">Date:</span> {{ $payment->updated_at->format('d M Y, H:i') }}</p>
        </div>
    </div>
@endif
