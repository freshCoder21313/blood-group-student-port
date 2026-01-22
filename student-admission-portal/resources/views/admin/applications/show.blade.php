<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ $application->student->first_name }} {{ $application->student->last_name }}
                </h2>
                <div class="flex items-center gap-3 text-sm text-gray-500 mt-1">
                    <span
                        class="font-mono bg-gray-100 px-2 py-0.5 rounded">{{ $application->application_number }}</span>
                    <span>&bull;</span>
                    <span>Applied {{ $application->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-ui.badge :status="$application->status" class="text-base px-4 py-1" />

                <!-- Quick Actions Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <x-ui.button variant="secondary" @click="open = !open" @click.away="open = false"
                        class="flex items-center gap-2">
                        <span>Actions</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </x-ui.button>
                    <div x-show="open"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 py-1 ring-1 ring-black ring-opacity-5"
                        style="display: none;">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Download PDF</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Send Email</a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete Application</a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Timeline (Simple Visual) -->
            <div class="mb-8">
                @php
                    $steps = ['draft', 'submitted', 'pending_payment', 'pending_approval', 'approved'];
                    $currentStatusIndex = array_search($application->status === 'student' ? 'approved' : $application->status, $steps);
                    if ($currentStatusIndex === false)
                        $currentStatusIndex = 1; // Default fallback
                @endphp
                <div class="relative">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-between">
                        @foreach($steps as $index => $step)
                            <div class="flex flex-col items-center">
                                <div
                                    class="h-8 w-8 rounded-full flex items-center justify-center {{ $index <= $currentStatusIndex ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-500' }} ring-4 ring-white">
                                    @if($index < $currentStatusIndex)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <span class="text-xs font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                <div
                                    class="mt-2 text-xs font-medium {{ $index <= $currentStatusIndex ? 'text-primary-600' : 'text-gray-500' }}">
                                    {{ ucfirst(str_replace('_', ' ', $step)) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8">

                <!-- Combined Tab Container -->
                <div class="space-y-6">

                    <!-- Tabs Navigation (Alpine.js) -->
                    <div x-data="{ tab: 'personal' }">
                        <div class="border-b border-gray-200 mb-6 flex flex-col md:flex-row justify-between items-end gap-4">
                            <nav class="-mb-px flex space-x-8 overflow-x-auto w-full md:w-auto">
                                <button @click="tab = 'personal'"
                                    :class="tab === 'personal' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                    Personal Info
                                </button>
                                <button @click="tab = 'academic'"
                                    :class="tab === 'academic' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                        </path>
                                    </svg>
                                    Academic & Program
                                </button>
                                <button @click="tab = 'documents'"
                                    :class="tab === 'documents' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    Documents
                                </button>
                                <button @click="tab = 'administration'"
                                    :class="tab === 'administration' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                        </path>
                                    </svg>
                                    Administration & Status
                                </button>
                            </nav>

                            <!-- Decision Actions in Tab Bar -->
                            @if (in_array($application->status, ['pending_approval', 'submitted', 'pending_payment']))
                                <div class="flex items-center gap-3 pb-3">
                                    <form action="{{ route('admin.applications.approve', $application) }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-bold rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-sm transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Approve
                                        </button>
                                    </form>

                                    <button
                                        @click="document.getElementById('reject-modal').classList.remove('hidden')"
                                        class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-bold rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Reject
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Tab: Personal Info -->
                        <div x-show="tab === 'personal'" x-cloak class="space-y-6">
                            <x-ui.card>
                                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">Student Details</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $application->student->first_name }}
                                            {{ $application->student->last_name }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $application->student->user->email ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $application->student->user->phone ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">National ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $application->student->national_id ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Gender</dt>
                                        <dd class="mt-1 text-sm text-gray-900 capitalize">
                                            {{ $application->student->gender ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $application->student->address ?? 'N/A' }}
                                        </dd>
                                    </div>
                                </dl>
                            </x-ui.card>

                            <x-ui.card>
                                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">Guardian Information</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Guardian Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $application->student->parentInfo->guardian_name ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Relationship</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $application->student->parentInfo->relationship ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $application->student->parentInfo->guardian_phone ?? 'N/A' }}
                                        </dd>
                                    </div>
                                </dl>
                            </x-ui.card>
                        </div>

                        <!-- Tab: Academic Info -->
                        <div x-show="tab === 'academic'" x-cloak>
                            <x-ui.card>
                                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">Selected Program</h3>
                                <div class="bg-primary-50 rounded-lg p-6 border border-primary-100">
                                    <h4 class="text-xl font-bold text-primary-900">
                                        {{ $application->program->name ?? 'N/A' }}
                                    </h4>
                                    <p class="text-primary-700 font-mono text-sm mt-1">
                                        {{ $application->program->code ?? 'N/A' }}
                                    </p>

                                    <div class="mt-6 grid grid-cols-2 gap-4">
                                        <div>
                                            <span
                                                class="text-xs text-primary-600 uppercase tracking-wide font-semibold">Intake</span>
                                            <p class="font-medium text-gray-900">
                                                {{ $application->academicBlock->name ?? 'N/A' }}
                                            </p>
                                        </div>
                                        <div>
                                            <span
                                                class="text-xs text-primary-600 uppercase tracking-wide font-semibold">Duration</span>
                                            <p class="font-medium text-gray-900">
                                                {{ $application->program->duration ?? '4 Years' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </x-ui.card>
                        </div>

                        <!-- Tab: Documents -->
                        <div x-show="tab === 'documents'" x-cloak>
                            <x-ui.card>
                                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">Submitted Documents</h3>
                                <div class="space-y-4">
                                    @forelse($application->documents as $doc)
                                        <div
                                            class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-primary-300 transition-colors bg-white">
                                            <div class="flex items-center gap-4">
                                                <div class="p-3 bg-gray-100 rounded-lg text-gray-500">
                                                    <!-- Generic File Icon -->
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 capitalize">
                                                        {{ str_replace('_', ' ', $doc->type) }}
                                                    </p>
                                                    <p class="text-xs text-gray-500">{{ $doc->original_name }}</p>
                                                </div>
                                            </div>
                                            <x-ui.button variant="secondary" size="sm"
                                                href="{{ route('documents.show', $doc) }}" target="_blank">
                                                Download
                                            </x-ui.button>
                                        </div>
                                    @empty
                                        <div class="text-center py-8 text-gray-500 italic">No documents uploaded.</div>
                                    @endforelse
                                </div>
                            </x-ui.card>
                        </div>

                        <!-- Tab: Administration -->
                        <div x-show="tab === 'administration'" x-cloak class="space-y-6">

                            <!-- Top Grid: Payment Status & Summary -->
                            <div class="grid grid-cols-1 gap-3">
                                <!-- Payment Status (Chiếm 1 cột) -->
                                <div class="lg:col-span-1 space-y-6">
                                    <x-ui.card class="h-full border-none shadow-sm ring-1 ring-gray-200 bg-white">
                                        <div class="p-4 border-b border-gray-100 bg-gray-50/80 flex items-center gap-2">
                                            <div class="p-1.5 bg-amber-100 rounded-lg text-amber-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <h3 class="font-bold text-gray-900">Payment Status</h3>
                                        </div>
                                        <div class="p-6">
                                            @if ($application->payment)
                                                <div class="space-y-6">
                                                    <div class="text-center">
                                                        <span class="block text-sm font-medium text-gray-500 mb-1">Total
                                                            Paid</span>
                                                        <span
                                                            class="text-3xl font-black text-gray-900 tracking-tight">{{ number_format($application->payment->amount) }}
                                                            <span
                                                                class="text-sm font-bold text-gray-500 align-top">KES</span></span>
                                                    </div>

                                                    <div
                                                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                                        <span
                                                            class="text-xs font-bold text-gray-500 uppercase">Status</span>
                                                        <x-ui.badge :status="$application->payment->status" />
                                                    </div>

                                                    <x-ui.button variant="secondary"
                                                        class="w-full justify-center py-2.5 text-sm border-gray-300 hover:border-primary-400 hover:text-primary-600 transition-all"
                                                        href="{{ route('admin.payments.show', $application->payment) }}">
                                                        View Receipt
                                                    </x-ui.button>
                                                </div>
                                            @else
                                                <div class="text-center py-8">
                                                    <div
                                                        class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 text-gray-400 mb-3">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M20 12H4M4 12L12 4M4 12L12 20">
                                                            </path>
                                                            <!-- Biểu tượng gạch chéo hoặc trống -->
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </div>
                                                    <p class="text-sm text-gray-500 font-medium">No payment record found</p>
                                                </div>
                                            @endif
                                        </div>
                                    </x-ui.card>
                                </div>

                                <!-- Activity Log (Chiếm 2 cột) -->
                                <div class="lg:col-span-2">
                                    <x-ui.card class="h-full border-none shadow-sm ring-1 ring-gray-200 bg-white">
                                        <div
                                            class="p-4 border-b border-gray-100 bg-gray-50/80 flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="p-1.5 bg-blue-100 rounded-lg text-blue-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <h3 class="font-bold text-gray-900">Activity History</h3>
                                            </div>
                                            <a href="{{ route('admin.activity-logs.index') }}"
                                                class="text-xs font-bold text-primary-600 hover:text-primary-800 hover:underline transition-colors">
                                                View Full Log &rarr;
                                            </a>
                                        </div>

                                        <div class="p-6">
                                            <div class="flow-root">
                                                <ul role="list" class="-mb-8">
                                                    @if ($application->submitted_at)
                                                        <li>
                                                            <div class="relative pb-8">
                                                                <span
                                                                    class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                                                    aria-hidden="true"></span>
                                                                <div class="relative flex space-x-3">
                                                                    <div>
                                                                        <span
                                                                            class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-4 ring-white">
                                                                            <svg class="h-5 w-5 text-white" fill="none"
                                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round" stroke-width="2"
                                                                                    d="M5 13l4 4L19 7"></path>
                                                                            </svg>
                                                                        </span>
                                                                    </div>
                                                                    <div
                                                                        class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                                        <div>
                                                                            <p class="text-sm font-bold text-gray-900">
                                                                                Application Submitted</p>
                                                                            <p class="mt-1 text-sm text-gray-600">The
                                                                                student completed all steps and submitted
                                                                                the
                                                                                application.</p>
                                                                        </div>
                                                                        <div
                                                                            class="text-right text-xs whitespace-nowrap text-gray-500">
                                                                            <time
                                                                                datetime="{{ $application->submitted_at }}">{{ $application->submitted_at->format('M d, H:i') }}</time>
                                                                            <p class="mt-0.5 font-medium text-gray-400">
                                                                                {{ $application->submitted_at->diffForHumans() }}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endif

                                                    <li>
                                                        <div class="relative pb-8">
                                                            <div class="relative flex space-x-3">
                                                                <div>
                                                                    <span
                                                                        class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-4 ring-white">
                                                                        <svg class="h-5 w-5 text-white" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                            </path>
                                                                        </svg>
                                                                    </span>
                                                                </div>
                                                                <div
                                                                    class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                                    <div>
                                                                        <p class="text-sm font-bold text-gray-900">Draft
                                                                            Created</p>
                                                                        <p class="mt-1 text-sm text-gray-600">
                                                                            Application
                                                                            started by student.</p>
                                                                    </div>
                                                                    <div
                                                                        class="text-right text-xs whitespace-nowrap text-gray-500">
                                                                        <time
                                                                            datetime="{{ $application->created_at }}">{{ $application->created_at->format('M d, H:i') }}</time>
                                                                        <p class="mt-0.5 font-medium text-gray-400">
                                                                            {{ $application->created_at->diffForHumans() }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </x-ui.card>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal (Hidden by default) -->
    <div id="reject-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                onclick="document.getElementById('reject-modal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('admin.applications.reject', $application) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Reject Application</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-4">Please provide a reason for rejection. This will be
                                sent to the student.</p>
                            <textarea name="rejection_reason"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                rows="3" required placeholder="Reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <x-ui.button variant="danger" class="w-full sm:w-auto sm:ml-3">
                            Confirm Rejection
                        </x-ui.button>
                        <button type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            onclick="document.getElementById('reject-modal').classList.add('hidden')">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>