<x-app-layout>
    @php
        $readonly = Gate::denies('update', $application);
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Application - Document Upload') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-ui.card>
                @if (session('status') === 'documents-updated')
                    <div class="mb-4 text-sm font-medium text-green-600">
                        {{ __('Documents saved successfully.') }}
                    </div>
                @endif
                
                {{-- Global errors (like missing required docs) --}}
                @if ($errors->any())
                    <div class="mb-4">
                        <div class="font-medium text-red-600">{{ __('Whoops! Something went wrong.') }}</div>
                        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('application.documents.update', $application) }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- National ID -->
                        @php
                            $nationalId = $documents->where('type', 'national_id')->first();
                            $nationalIdUrl = $nationalId ? route('documents.show', $nationalId) : null;
                            $nationalIdDeleteUrl = $nationalId ? route('documents.destroy', $nationalId) : null;
                            $nationalIdIsImage = $nationalId ? Str::startsWith($nationalId->mime_type, 'image/') : true;
                        @endphp
                        <div>
                            <x-ui.image-uploader :disabled="$readonly" 
                                name="national_id" 
                                label="National ID (Scanned Copy)" 
                                accept="image/*,.pdf"
                                :value="$nationalIdUrl"
                                :delete-url="$nationalIdDeleteUrl"
                                :initial-is-image="$nationalIdIsImage"
                            />
                        </div>

                        <!-- Transcript -->
                        @php
                            $transcript = $documents->where('type', 'transcript')->first();
                            $transcriptUrl = $transcript ? route('documents.show', $transcript) : null;
                            $transcriptDeleteUrl = $transcript ? route('documents.destroy', $transcript) : null;
                            $transcriptIsImage = $transcript ? Str::startsWith($transcript->mime_type, 'image/') : true;
                        @endphp
                        <div>
                            <x-ui.image-uploader :disabled="$readonly" 
                                name="transcript" 
                                label="High School Transcript" 
                                accept="image/*,.pdf"
                                :value="$transcriptUrl"
                                :delete-url="$transcriptDeleteUrl"
                                :initial-is-image="$transcriptIsImage"
                            />
                        </div>
                    </div>

                    @if(!$readonly)
                    <div class="flex items-center justify-end mt-4 gap-4">
                        <x-ui.secondary-button type="submit" name="action" value="save">
                            {{ __('Save Draft') }}
                        </x-ui.secondary-button>

                        <x-ui.primary-button type="submit" name="action" value="next">
                            {{ __('Review & Submit') }}
                        </x-ui.primary-button>
                    </div>
                    @else
                     <div class="mt-2 text-gray-500">
                         <a href="{{ route('application.payment', $application) }}" class="text-primary-600 hover:underline">Next Step &rarr;</a>
                     </div>
                    @endif
                </form>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
