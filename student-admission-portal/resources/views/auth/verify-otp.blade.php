<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Please enter the verification code sent to your email or phone.') }}
    </div>

    <!-- Session Status -->
    <x-auth.session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf

        <!-- OTP Code -->
        <div>
            <x-ui.input-label for="code" :value="__('Verification Code')" />
            <x-ui.input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code')" required autofocus />
            <x-ui.input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-ui.button variant="primary" class="ms-3">
                {{ __('Verify') }}
            </x-ui.button>
        </div>
    </form>
    
    <div class="mt-4 flex justify-center">
        <form method="POST" action="{{ route('otp.resend') }}">
            @csrf
            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                {{ __('Resend Code') }}
            </button>
        </form>
    </div>
</x-guest-layout>
