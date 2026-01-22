<x-app-layout>
    @php
        $readonly = Gate::denies('update', $application);
        $currentStep = $application->current_step;
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Application') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="wizardHandler({{ $currentStep }})">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-ui.card>
                <!-- Status Messages -->
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-600">
                        {{ session('status') }}
                    </div>
                @endif
                
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded border border-red-200">
                        <strong>Please check the errors below:</strong>
                        <ul class="list-disc list-inside mt-2 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Wizard Progress Bar -->
                <div class="relative pt-1 mb-8">
                    <div class="flex mb-2 items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-primary-600 bg-primary-200">
                                Step <span x-text="step"></span> of 4
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold inline-block text-primary-600" x-text="Math.round((step / 4) * 100) + '%'">
                            </span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-primary-200">
                        <div :style="'width: ' + ((step / 4) * 100) + '%'" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-primary-500 transition-all duration-500 ease-out"></div>
                    </div>
                </div>

                <!-- Wizard Tabs -->
                <div class="mb-8 border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                        <button @click="showStep(1)" :class="step === 1 ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            1. Personal Details
                        </button>
                        <button @click="showStep(2)" :class="step === 2 ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            2. Parent Details
                        </button>
                        <button @click="showStep(3)" :class="step === 3 ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            3. Program Selection
                        </button>
                        <button @click="showStep(4)" :class="step === 4 ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            4. Documents
                        </button>
                    </nav>
                </div>

                <!-- Step 1: Personal Details -->
                <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <form method="POST" action="{{ route('application.wizard.save', ['application' => $application, 'step' => 1]) }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Personal Details Fields (Same as before) -->
                            <div>
                                <x-ui.input-label for="first_name" :value="__('First Name')" />
                                <x-ui.text-input :disabled="$readonly" id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name', $student->first_name)" />
                            </div>
                            <div>
                                <x-ui.input-label for="last_name" :value="__('Last Name')" />
                                <x-ui.text-input :disabled="$readonly" id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name', $student->last_name)" />
                            </div>
                             <!-- More fields from original personal.blade.php -->
                             <div>
                                <x-ui.input-label for="date_of_birth" :value="__('Date of Birth')" />
                                <x-ui.text-input :disabled="$readonly" id="date_of_birth" class="block mt-1 w-full" type="date" name="date_of_birth" :value="old('date_of_birth', optional($student->date_of_birth)->format('Y-m-d'))" />
                            </div>
                             <div>
                                <x-ui.input-label for="gender" :value="__('Gender')" />
                                <select @disabled($readonly) id="gender" name="gender" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm block mt-1 w-full">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $student->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $student->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div>
                                <x-ui.input-label for="national_id" :value="__('National ID')" />
                                <x-ui.text-input :disabled="$readonly" id="national_id" class="block mt-1 w-full" type="text" name="national_id" :value="old('national_id', $student->national_id)" />
                            </div>
                             <div class="col-span-1 md:col-span-2">
                                <x-ui.input-label for="address" :value="__('Address')" />
                                <textarea @disabled($readonly) id="address" name="address" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm block mt-1 w-full" rows="2">{{ old('address', $student->address) }}</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-ui.primary-button type="submit" name="action" value="next">
                                {{ __('Save & Continue') }}
                            </x-ui.primary-button>
                        </div>
                    </form>
                </div>

                <!-- Step 2: Parent Details -->
                <div x-show="step === 2" x-cloak>
                     <form method="POST" action="{{ route('application.wizard.save', ['application' => $application, 'step' => 2]) }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-ui.input-label for="guardian_name" :value="__('Guardian Name')" />
                                <x-ui.text-input :disabled="$readonly" id="guardian_name" class="block mt-1 w-full" type="text" name="guardian_name" :value="old('guardian_name', $parentInfo->guardian_name ?? '')" />
                            </div>
                            <div>
                                <x-ui.input-label for="guardian_phone" :value="__('Guardian Phone')" />
                                <x-ui.text-input :disabled="$readonly" id="guardian_phone" class="block mt-1 w-full" type="text" name="guardian_phone" :value="old('guardian_phone', $parentInfo->guardian_phone ?? '')" />
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-6">
                            <button type="button" @click="step = 1" class="text-gray-600 hover:underline">Back</button>
                            <x-ui.primary-button type="submit" name="action" value="next">
                                {{ __('Save & Continue') }}
                            </x-ui.primary-button>
                        </div>
                    </form>
                </div>

                <!-- Step 3: Program Selection -->
                <div x-show="step === 3" x-cloak>
                    <form method="POST" action="{{ route('application.wizard.save', ['application' => $application, 'step' => 3]) }}">
                        @csrf
                        <div>
                            <x-ui.input-label for="program_id" :value="__('Select Program')" />
                            <x-ui.select :disabled="$readonly" id="program_id" name="program_id" class="block mt-1 w-full">
                                <option value="">{{ __('Select a program...') }}</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('program_id', $application->program_id) == $program->id ? 'selected' : '' }}>
                                        {{ $program->code }} - {{ $program->name }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>
                         <div class="flex items-center justify-between mt-6">
                            <button type="button" @click="step = 2" class="text-gray-600 hover:underline">Back</button>
                            <x-ui.primary-button type="submit" name="action" value="next">
                                {{ __('Save & Continue') }}
                            </x-ui.primary-button>
                        </div>
                    </form>
                </div>

                <!-- Step 4: Documents -->
                <div x-show="step === 4" x-cloak>
                    <form method="POST" action="{{ route('application.wizard.save', ['application' => $application, 'step' => 4]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             @php
                                $nationalId = $documents->where('type', 'national_id')->first();
                                $nationalIdUrl = $nationalId ? route('documents.show', $nationalId) : null;
                                $nationalIdDeleteUrl = $nationalId ? route('documents.destroy', $nationalId) : null;
                            @endphp
                            <div>
                                <x-ui.image-uploader :disabled="$readonly" 
                                    name="national_id" 
                                    label="National ID (Scanned Copy)" 
                                    :value="$nationalIdUrl"
                                    :delete-url="$nationalIdDeleteUrl"
                                />
                            </div>
                            
                            @php
                                $transcript = $documents->where('type', 'transcript')->first();
                                $transcriptUrl = $transcript ? route('documents.show', $transcript) : null;
                                $transcriptDeleteUrl = $transcript ? route('documents.destroy', $transcript) : null;
                            @endphp
                            <div>
                                <x-ui.image-uploader :disabled="$readonly" 
                                    name="transcript" 
                                    label="High School Transcript" 
                                    :value="$transcriptUrl"
                                    :delete-url="$transcriptDeleteUrl"
                                />
                            </div>
                        </div>

                         <div class="flex items-center justify-between mt-6">
                            <button type="button" @click="step = 3" class="text-gray-600 hover:underline">Back</button>
                            <x-ui.primary-button type="submit" name="action" value="finish">
                                {{ __('Finish & Proceed to Payment') }}
                            </x-ui.primary-button>
                        </div>
                    </form>
                </div>

            </x-ui.card>
        </div>
    </div>

    <script>
        function wizardHandler(initialStep) {
            return {
                step: initialStep > 4 ? 4 : (initialStep < 1 ? 1 : initialStep),
                showStep(s) {
                    this.step = s;
                    // Optional: Update URL hash
                    window.location.hash = 'step-' + s;
                },
                init() {
                    if(window.location.hash) {
                         const s = parseInt(window.location.hash.replace('#step-', ''));
                         if(!isNaN(s) && s >= 1 && s <= 4) this.step = s;
                    }
                }
            }
        }
    </script>
</x-app-layout>
