<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900" x-data="paymentHandler({{ $application->id }}, '{{ $payment?->status ?? 'none' }}')">
                <h2 class="text-xl font-bold mb-4">Step 4: Payment</h2>

                <!-- Success/Verification State -->
                <template x-if="status === 'completed' || status === 'pending_verification'">
                    <div>
                        @if($payment && $payment->status === 'completed')
                            <x-mpesa-receipt :payment="$payment" />
                        @elseif($payment && $payment->status === 'pending_verification')
                             <div class="p-4 mb-4 bg-yellow-100 text-yellow-700 rounded border border-yellow-200">
                                <h3 class="font-bold text-lg mb-2">Payment Under Verification</h3>
                                <p>We have received your manual payment details. Please submit your application below to proceed.</p>
                             </div>
                        @else
                            <div class="p-4 mb-4 bg-green-100 text-green-700 rounded">
                                Payment Completed! Please refresh the page if receipt is not shown.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('application.submit', $application) }}">
                            @csrf
                            <x-ui.primary-button class="mt-4">Submit Application</x-ui.primary-button>
                        </form>
                    </div>
                </template>

                <!-- Payment Form -->
                <template x-if="status !== 'completed' && status !== 'pending_verification'">
                    <div>
                        <div x-show="!manualPayment">
                            <p class="mb-4">Please pay the admission fee of <strong>KES 1,000</strong> via M-Pesa.</p>

                            <div class="mb-4 max-w-md">
                                <x-ui.input-label for="phone_number" value="M-Pesa Phone Number" />
                                <x-ui.text-input id="phone_number" class="block mt-1 w-full" type="text" x-model="phoneNumber" placeholder="0712345678" />
                                <p class="text-sm text-red-600 mt-1" x-text="error"></p>
                            </div>

                            <div class="flex items-center space-x-4">
                                <x-ui.primary-button @click="initiatePayment" ::disabled="loading">
                                    <span x-show="!loading">Pay Now</span>
                                    <span x-show="loading">Processing...</span>
                                </x-ui.primary-button>
                                
                                <a href="{{ route('application.documents', $application) }}" class="text-gray-600 hover:text-gray-900">Back</a>
                            </div>

                            <div x-show="waiting" class="mt-4 p-4 bg-blue-50 text-blue-700 rounded border border-blue-200">
                                <div class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <p>Waiting for payment confirmation... Please check your phone and enter PIN.</p>
                                </div>
                            </div>
                            
                            <button @click="manualPayment = true" class="text-sm text-blue-600 hover:underline mt-6 block">Problems paying? Use Paybill</button>
                        </div>

                        <!-- Manual Payment Form -->
                        <div x-show="manualPayment" class="border-t pt-4 mt-4" x-cloak>
                             <h3 class="font-bold mb-4 text-lg">Manual Payment via Paybill</h3>
                             <div class="bg-gray-50 p-4 rounded mb-4">
                                 <p class="mb-2">Paybill Number: <strong>{{ config('mpesa.paybill', '888888') }}</strong></p>
                                 <p>Account Number: <strong>{{ $application->application_number }}</strong></p>
                             </div>

                             <form method="POST" action="{{ route('payment.manual.store', $application) }}" enctype="multipart/form-data">
                                 @csrf
                                 <div class="mb-4 max-w-md">
                                     <x-ui.input-label for="transaction_code" value="M-Pesa Transaction Code" />
                                    <x-ui.text-input id="transaction_code" name="transaction_code" class="block mt-1 w-full" type="text" placeholder="e.g. QDH..." required value="{{ old('transaction_code') }}" pattern="[A-Z0-9]{10}" title="10-character uppercase alphanumeric code" x-on:input="$el.value = $el.value.toUpperCase()" />
                                    <p class="text-xs text-gray-500 mt-1">Format: 10 characters, Uppercase (e.g. QDH1234567)</p>
                                     @error('transaction_code') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                                 </div>
                                 <div class="mb-4 max-w-md">
                                     <x-ui.input-label for="proof_document" value="Upload Payment Message/Receipt (Image/PDF)" />
                                     <input id="proof_document" name="proof_document" type="file" class="block mt-1 w-full border border-gray-300 rounded p-2" required accept=".jpg,.jpeg,.png,.pdf" />
                                     @error('proof_document') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                                 </div>
                                 <div class="flex items-center space-x-4">
                                     <x-ui.primary-button>Confirm Payment</x-ui.primary-button>
                                     <button type="button" @click="manualPayment = false" class="text-gray-600 hover:text-gray-900">Cancel</button>
                                 </div>
                             </form>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function paymentHandler(appId, initialStatus) {
            return {
                phoneNumber: '',
                loading: false,
                waiting: false,
                status: initialStatus,
                error: '',
                pollInterval: null,
                manualPayment: false,

                init() {
                    // Pre-fill phone from student info if available?
                    // Optional.
                },

                initiatePayment() {
                    if (!this.phoneNumber) {
                        this.error = 'Phone number is required';
                        return;
                    }
                    
                    this.loading = true;
                    this.error = '';
                    this.waiting = false;

                    axios.post(`/payment/${appId}/initiate`, {
                        phone_number: this.phoneNumber
                    })
                    .then(response => {
                        this.waiting = true;
                        this.startPolling();
                    })
                    .catch(err => {
                        this.loading = false;
                        this.waiting = false;
                        this.error = err.response?.data?.message || 'Payment initiation failed';
                    });
                },

                startPolling() {
                    // Clear existing interval if any
                    if (this.pollInterval) clearInterval(this.pollInterval);
                    
                    this.pollInterval = setInterval(() => {
                        axios.get(`/payment/${appId}/status`)
                            .then(res => {
                                if (res.data.status === 'completed') {
                                    this.status = 'completed';
                                    clearInterval(this.pollInterval);
                                    this.loading = false;
                                    this.waiting = false;
                                    window.location.reload(); 
                                } else if (res.data.status === 'failed') {
                                    this.status = 'failed';
                                    this.error = 'Payment failed. Please try again.';
                                    clearInterval(this.pollInterval);
                                    this.loading = false;
                                    this.waiting = false;
                                }
                            });
                    }, 3000);
                }
            }
        }
    </script>
</x-app-layout>
