<x-app-layout>
    @php
        $readonly = Gate::denies('update', $application);
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Application - Parent/Guardian Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-ui.card>
                @if (session('status') === 'parent-updated')
                    <div class="mb-4 text-sm font-medium text-green-600">
                        {{ __('Parent details saved successfully.') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('application.parent.update', $application) }}">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Guardian Name -->
                        <div>
                            <x-ui.input-label for="guardian_name" :value="__('Guardian Name')" />
                            <x-ui.text-input :disabled="$readonly" id="guardian_name" class="block mt-1 w-full" type="text" name="guardian_name" :value="old('guardian_name', $parentInfo->guardian_name ?? '')" />
                            <x-ui.input-error :messages="$errors->get('guardian_name')" class="mt-2" />
                        </div>

                        <!-- Relationship -->
                        <div>
                            <x-ui.input-label for="relationship" :value="__('Relationship')" />
                             <x-ui.text-input :disabled="$readonly" id="relationship" class="block mt-1 w-full" type="text" name="relationship" :value="old('relationship', $parentInfo->relationship ?? '')" placeholder="e.g. Father, Mother" />
                            <x-ui.input-error :messages="$errors->get('relationship')" class="mt-2" />
                        </div>

                        <!-- Phone -->
                        <div>
                            <x-ui.input-label for="guardian_phone" :value="__('Phone Number')" />
                            <x-ui.text-input :disabled="$readonly" id="guardian_phone" class="block mt-1 w-full" type="text" name="guardian_phone" :value="old('guardian_phone', $parentInfo->guardian_phone ?? '')" />
                            <x-ui.input-error :messages="$errors->get('guardian_phone')" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div>
                            <x-ui.input-label for="guardian_email" :value="__('Email (Optional)')" />
                            <x-ui.text-input :disabled="$readonly" id="guardian_email" class="block mt-1 w-full" type="email" name="guardian_email" :value="old('guardian_email', $parentInfo->guardian_email ?? '')" />
                            <x-ui.input-error :messages="$errors->get('guardian_email')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                         <a href="{{ route('application.personal', $application) }}" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            {{ __('Back to Personal Details') }}
                        </a>

                        @if(!$readonly)
                        <div class="flex gap-4">
                            <x-ui.secondary-button type="submit" name="action" value="save">
                                {{ __('Save Draft') }}
                            </x-ui.secondary-button>

                            <x-ui.primary-button type="submit" name="action" value="next">
                                {{ __('Save & Next') }}
                            </x-ui.primary-button>
                        </div>
                        @else
                         <div class="mt-2 text-gray-500">
                             <a href="{{ route('application.program', $application) }}" class="text-primary-600 hover:underline">Next Step &rarr;</a>
                         </div>
                        @endif
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
