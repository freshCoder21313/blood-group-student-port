<x-app-layout>
    @php
        $readonly = Gate::denies('update', $application);
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Application - Program Selection') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-ui.card>
                @if (session('status') === 'program-updated')
                    <div class="mb-4 text-sm font-medium text-green-600">
                        {{ __('Program selection saved successfully.') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('application.program.update', $application) }}">
                    @csrf
                    
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Program -->
                        <div>
                            <x-ui.input-label for="program_id" :value="__('Select Program')" />
                            <x-ui.select :disabled="$readonly" id="program_id" name="program_id" class="block mt-1 w-full">
                                <option value="">{{ __('Select a program...') }}</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('program_id', $application->program_id) == $program->id ? 'selected' : '' }}>
                                        {{ $program->code }} - {{ $program->name }} ({{ $program->duration }}) - {{ number_format($program->fee, 2) }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.input-error :messages="$errors->get('program_id')" class="mt-2" />
                        </div>
                    </div>

                    @if(!$readonly)
                    <div class="flex items-center justify-end mt-4 gap-4">
                        <x-ui.button variant="secondary" type="submit" name="action" value="save">
                            {{ __('Save Draft') }}
                        </x-ui.button>

                        <x-ui.button variant="primary" type="submit" name="action" value="next">
                            {{ __('Save & Next') }}
                        </x-ui.button>
                    </div>
                    @else
                     <div class="mt-2 text-gray-500">
                         <a href="{{ route('application.documents', $application) }}" class="text-primary-600 hover:underline">Next Step &rarr;</a>
                     </div>
                    @endif
                </form>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
