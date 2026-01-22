<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('admin.payments.index') }}" class="text-primary-600 hover:text-primary-900">&larr; Back to List</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium mb-4">Transaction Details</h3>
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Transaction Code</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $payment->transaction_code }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($payment->amount, 2) }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Student</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($payment->application && $payment->application->student)
                                            {{ $payment->application->student->first_name }} {{ $payment->application->student->last_name }}
                                        @else
                                            N/A
                                        @endif
                                    </dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $payment->created_at->format('M d, Y H:i') }}</dd>
                                </div>
                            </dl>

                            <div class="mt-8">
                                <h3 class="text-lg font-medium mb-4">Actions</h3>
                                <div class="flex space-x-4">
                                    <form action="{{ route('admin.payments.approve', $payment) }}" method="POST">
                                        @csrf
                                        <x-ui.primary-button class="bg-green-600 hover:bg-green-700">
                                            Approve Payment
                                        </x-ui.primary-button>
                                    </form>

                                    <form action="{{ route('admin.payments.reject', $payment) }}" method="POST">
                                        @csrf
                                        <x-ui.danger-button>
                                            Reject Payment
                                        </x-ui.danger-button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium mb-4">Proof of Payment</h3>
                            @if($payment->proof_document_path)
                                @php
                                    $extension = pathinfo($payment->proof_document_path, PATHINFO_EXTENSION);
                                    $isPdf = strtolower($extension) === 'pdf';
                                @endphp

                                <div class="border rounded-lg overflow-hidden bg-gray-50">
                                    @if($isPdf)
                                        <iframe src="{{ route('admin.payments.proof', $payment) }}" class="w-full h-96"></iframe>
                                        <div class="p-2 text-center border-t">
                                            <a href="{{ route('admin.payments.proof', $payment) }}" target="_blank" class="text-primary-600 hover:underline text-sm">Open PDF in new tab</a>
                                        </div>
                                    @else
                                        <img src="{{ route('admin.payments.proof', $payment) }}" alt="Proof of Payment" class="w-full h-auto object-contain max-h-96">
                                        <div class="p-2 text-center border-t">
                                            <a href="{{ route('admin.payments.proof', $payment) }}" target="_blank" class="text-primary-600 hover:underline text-sm">View Full Size</a>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="bg-gray-100 p-4 rounded text-center text-gray-500">
                                    No proof document uploaded.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
