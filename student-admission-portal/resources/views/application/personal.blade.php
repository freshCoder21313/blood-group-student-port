<x-app-layout>
    @php
        $readonly = Gate::denies('update', $application);
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Application - Personal Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-ui.card>
                @if (session('status') === 'personal-updated')
                    <div class="mb-4 text-sm font-medium text-green-600">
                        {{ __('Personal details saved successfully.') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('application.personal.update', $application) }}">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div>
                            <x-ui.input-label for="first_name" :value="__('First Name')" />
                            <x-ui.text-input :disabled="$readonly" id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name', $student->first_name)" />
                            <x-ui.input-error :messages="$errors->get('first_name')" class="mt-2" />
                        </div>

                        <!-- Middle Name -->
                        <div>
                            <x-ui.input-label for="middle_name" :value="__('Middle Name')" />
                            <x-ui.text-input :disabled="$readonly" id="middle_name" class="block mt-1 w-full" type="text" name="middle_name" :value="old('middle_name', $student->middle_name)" />
                            <x-ui.input-error :messages="$errors->get('middle_name')" class="mt-2" />
                        </div>

                        <!-- Last Name -->
                        <div>
                            <x-ui.input-label for="last_name" :value="__('Last Name')" />
                            <x-ui.text-input :disabled="$readonly" id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name', $student->last_name)" />
                            <x-ui.input-error :messages="$errors->get('last_name')" class="mt-2" />
                        </div>
                        
                        <!-- DOB -->
                        <div>
                            <x-ui.input-label for="date_of_birth" :value="__('Date of Birth')" />
                            <x-ui.text-input :disabled="$readonly" id="date_of_birth" class="block mt-1 w-full" type="date" name="date_of_birth" :value="old('date_of_birth', optional($student->date_of_birth)->format('Y-m-d'))" />
                            <x-ui.input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                        </div>

                        <!-- Gender -->
                        <div>
                            <x-ui.input-label for="gender" :value="__('Gender')" />
                            <select @disabled($readonly) id="gender" name="gender" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $student->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $student->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', $student->gender) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <x-ui.input-error :messages="$errors->get('gender')" class="mt-2" />
                        </div>
                         <!-- Nationality -->
                        <div>
                            <x-ui.input-label for="nationality" :value="__('Nationality')" />
                            <x-ui.text-input :disabled="$readonly" id="nationality" class="block mt-1 w-full" type="text" name="nationality" :value="old('nationality', $student->nationality ?? 'Kenya')" />
                            <x-ui.input-error :messages="$errors->get('nationality')" class="mt-2" />
                        </div>

                         <!-- National ID -->
                        <div>
                            <x-ui.input-label for="national_id" :value="__('National ID')" />
                            <x-ui.text-input :disabled="$readonly" id="national_id" class="block mt-1 w-full" type="text" name="national_id" :value="old('national_id', $student->national_id)" />
                             <p class="text-xs text-gray-500 mt-1">Encrypted at rest</p>
                            <x-ui.input-error :messages="$errors->get('national_id')" class="mt-2" />
                        </div>
                        
                         <!-- Passport -->
                        <div>
                            <x-ui.input-label for="passport_number" :value="__('Passport Number (Optional)')" />
                            <x-ui.text-input :disabled="$readonly" id="passport_number" class="block mt-1 w-full" type="text" name="passport_number" :value="old('passport_number', $student->passport_number)" />
                             <p class="text-xs text-gray-500 mt-1">Encrypted at rest</p>
                            <x-ui.input-error :messages="$errors->get('passport_number')" class="mt-2" />
                        </div>

                         <!-- Address -->
                        <div class="col-span-1 md:col-span-2">
                            <x-ui.input-label for="address" :value="__('Address')" />
                            <textarea @disabled($readonly) id="address" name="address" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" rows="3">{{ old('address', $student->address) }}</textarea>
                            <x-ui.input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>
                        
                        <!-- City -->
                        <div>
                            <x-ui.input-label for="city" :value="__('City')" />
                            <x-ui.text-input :disabled="$readonly" id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city', $student->city)" />
                            <x-ui.input-error :messages="$errors->get('city')" class="mt-2" />
                        </div>

                        <!-- County -->
                         <div>
                            <x-ui.input-label for="county" :value="__('County')" />
                            <x-ui.text-input :disabled="$readonly" id="county" class="block mt-1 w-full" type="text" name="county" :value="old('county', $student->county)" />
                            <x-ui.input-error :messages="$errors->get('county')" class="mt-2" />
                        </div>
                        
                        <!-- Postal Code -->
                        <div>
                            <x-ui.input-label for="postal_code" :value="__('Postal Code')" />
                            <x-ui.text-input :disabled="$readonly" id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" :value="old('postal_code', $student->postal_code)" />
                            <x-ui.input-error :messages="$errors->get('postal_code')" class="mt-2" />
                        </div>

                    </div>

                    @if(!$readonly)
                    <div class="flex items-center justify-end mt-4 gap-4">
                        <x-ui.secondary-button type="submit" name="action" value="save">
                            {{ __('Save Draft') }}
                        </x-ui.secondary-button>

                        <x-ui.primary-button type="submit" name="action" value="next">
                            {{ __('Save & Next') }}
                        </x-ui.primary-button>
                    </div>
                    @else
                     <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded text-center text-gray-600">
                        This application has been submitted and cannot be edited.
                        <div class="mt-2">
                            <a href="{{ route('application.parent', $application) }}" class="text-blue-600 hover:underline">Next Step &rarr;</a>
                        </div>
                     </div>
                    @endif
                </form>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
