<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Application Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 flex justify-between">
                        <a href="{{ route('admin.applications.index') }}" class="text-primary-600 hover:text-primary-900">&larr; Back</a>
                        <span class="px-3 py-1 text-sm font-bold rounded-full bg-gray-100">{{ ucfirst($application->status) }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Student Info -->
                        <div>
                            <h3 class="font-bold text-lg mb-4 border-b pb-2">Student Information</h3>
                            <p><strong>Name:</strong> {{ $application->student->first_name ?? '' }} {{ $application->student->last_name ?? '' }}</p>
                            <p><strong>Email:</strong> {{ $application->student->user->email ?? 'N/A' }}</p>
                            <p><strong>Phone:</strong> {{ $application->student->user->phone ?? 'N/A' }}</p>
                            <p><strong>National ID:</strong> {{ $application->student->national_id ?? 'N/A' }}</p>
                            <p><strong>Address:</strong> {{ $application->student->address ?? 'N/A' }}</p>
                        </div>

                        <!-- Program Info -->
                        <div>
                            <h3 class="font-bold text-lg mb-4 border-b pb-2">Program Selection</h3>
                            <p><strong>Program:</strong> {{ $application->program->name ?? 'N/A' }} ({{ $application->program->code ?? 'N/A' }})</p>
                            <p><strong>Intake:</strong> {{ $application->academicBlock->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="mt-8">
                        <h3 class="font-bold text-lg mb-4 border-b pb-2">Documents</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($application->documents as $doc)
                                <div class="border p-4 rounded flex justify-between items-center">
                                    <div>
                                        <p class="font-medium capitalize">{{ str_replace('_', ' ', $doc->type) }}</p>
                                        <p class="text-xs text-gray-500">{{ $doc->original_name }}</p>
                                    </div>
                                    <a href="{{ route('documents.show', $doc) }}" target="_blank" class="text-primary-600 hover:underline">Download</a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($application->status !== 'draft')
                        <div class="mt-8 border-t pt-6 flex space-x-4">
                            @if($application->status !== 'approved')
                                <form action="{{ route('admin.applications.approve', $application) }}" method="POST">
                                    @csrf
                                    <x-ui.primary-button class="bg-green-600 hover:bg-green-700">Approve Application</x-ui.primary-button>
                                </form>
                            @endif

                            @if($application->status !== 'rejected')
                                <button onclick="document.getElementById('reject-form').classList.toggle('hidden')" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Reject</button>
                            @endif
                        </div>

                        <div id="reject-form" class="mt-4 hidden bg-red-50 p-4 rounded">
                            <form action="{{ route('admin.applications.reject', $application) }}" method="POST">
                                @csrf
                                <label class="block mb-2 text-sm font-bold text-red-700">Rejection Reason:</label>
                                <textarea name="rejection_reason" class="w-full border-red-300 rounded" required></textarea>
                                <div class="mt-2 text-right">
                                    <x-ui.danger-button>Confirm Rejection</x-ui.danger-button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
