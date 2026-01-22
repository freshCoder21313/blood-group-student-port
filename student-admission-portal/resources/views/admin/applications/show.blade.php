<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ $application->student->first_name }} {{ $application->student->last_name }}
                </h2>
                <div class="flex items-center gap-3 text-sm text-gray-500 mt-1">
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded">{{ $application->application_number }}</span>
                    <span>&bull;</span>
                    <span>Applied {{ $application->created_at->diffForHumans() }}</span>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <x-ui.badge :status="$application->status" class="text-base px-4 py-1" />
                
                <!-- Quick Actions Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <x-ui.button variant="secondary" @click="open = !open" @click.away="open = false" class="flex items-center gap-2">
                        <span>Actions</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </x-ui.button>
                    <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 py-1 ring-1 ring-black ring-opacity-5" style="display: none;">
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
                    if($currentStatusIndex === false) $currentStatusIndex = 1; // Default fallback
                @endphp
                <div class="relative">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-between">
                        @foreach($steps as $index => $step)
                            <div class="flex flex-col items-center">
                                <div class="h-8 w-8 rounded-full flex items-center justify-center {{ $index <= $currentStatusIndex ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-500' }} ring-4 ring-white">
                                    @if($index < $currentStatusIndex)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        <span class="text-xs font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                <div class="mt-2 text-xs font-medium {{ $index <= $currentStatusIndex ? 'text-primary-600' : 'text-gray-500' }}">
                                    {{ ucfirst(str_replace('_', ' ', $step)) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
                
                <!-- Left Column: Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Tabs Navigation (Alpine.js) -->
                    <div x-data="{ tab: 'personal' }">
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="-mb-px flex space-x-8">
                                <button @click="tab = 'personal'" :class="tab === 'personal' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                                    Personal Info
                                </button>
                                <button @click="tab = 'academic'" :class="tab === 'academic' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                                    Academic & Program
                                </button>
                                <button @click="tab = 'documents'" :class="tab === 'documents' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                                    Documents
                                </button>
                            </nav>
                        </div>

                        <!-- Tab: Personal Info -->
                        <div x-show="tab === 'personal'" x-cloak class="space-y-6">
                            <x-ui.card>
                                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">Student Details</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $application->student->first_name }} {{ $application->student->last_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $application->student->user->email ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $application->student->user->phone ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">National ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $application->student->national_id ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Gender</dt>
                                        <dd class="mt-1 text-sm text-gray-900 capitalize">{{ $application->student->gender ?? 'N/A' }}</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $application->student->address ?? 'N/A' }}</dd>
                                    </div>
                                </dl>
                            </x-ui.card>

                            <x-ui.card>
                                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">Guardian Information</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Guardian Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $application->student->parentInfo->guardian_name ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Relationship</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $application->student->parentInfo->relationship ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $application->student->parentInfo->guardian_phone ?? 'N/A' }}</dd>
                                    </div>
                                </dl>
                            </x-ui.card>
                        </div>

                        <!-- Tab: Academic Info -->
                        <div x-show="tab === 'academic'" x-cloak>
                            <x-ui.card>
                                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">Selected Program</h3>
                                <div class="bg-primary-50 rounded-lg p-6 border border-primary-100">
                                    <h4 class="text-xl font-bold text-primary-900">{{ $application->program->name ?? 'N/A' }}</h4>
                                    <p class="text-primary-700 font-mono text-sm mt-1">{{ $application->program->code ?? 'N/A' }}</p>
                                    
                                    <div class="mt-6 grid grid-cols-2 gap-4">
                                        <div>
                                            <span class="text-xs text-primary-600 uppercase tracking-wide font-semibold">Intake</span>
                                            <p class="font-medium text-gray-900">{{ $application->academicBlock->name ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-primary-600 uppercase tracking-wide font-semibold">Duration</span>
                                            <p class="font-medium text-gray-900">{{ $application->program->duration ?? '4 Years' }}</p>
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
                                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-primary-300 transition-colors bg-white">
                                            <div class="flex items-center gap-4">
                                                <div class="p-3 bg-gray-100 rounded-lg text-gray-500">
                                                    <!-- Generic File Icon -->
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 capitalize">{{ str_replace('_', ' ', $doc->type) }}</p>
                                                    <p class="text-xs text-gray-500">{{ $doc->original_name }}</p>
                                                </div>
                                            </div>
                                            <x-ui.button variant="secondary" size="sm" href="{{ route('documents.show', $doc) }}" target="_blank">
                                                Download
                                            </x-ui.button>
                                        </div>
                                    @empty
                                        <div class="text-center py-8 text-gray-500 italic">No documents uploaded.</div>
                                    @endforelse
                                </div>
                            </x-ui.card>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sidebar -->
                <div class="space-y-6">
                    
                    <!-- Admin Actions Panel -->
                    @if($application->status !== 'draft')
                        <x-ui.card class="overflow-hidden border-none shadow-sm ring-1 ring-gray-200">
                            <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Application Decision
                                </h3>
                            </div>
                            
                            <div class="p-5">
                                @if($application->status === 'pending_approval')
                                    <div class="space-y-4">
                                        <form action="{{ route('admin.applications.approve', $application) }}" method="POST">
                                            @csrf
                                            <x-ui.button variant="success" class="w-full justify-center py-2.5 shadow-sm hover:shadow-md transition-all duration-200">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Approve Application
                                            </x-ui.button>
                                        </form>
                                        
                                        <button @click="document.getElementById('reject-modal').classList.remove('hidden')" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-white border border-red-200 rounded-md font-bold text-xs text-red-600 uppercase tracking-widest shadow-sm hover:bg-red-50 hover:border-red-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Reject Application
                                        </button>
                                    </div>
                                @elseif($application->status === 'approved')
                                    <div class="text-center p-6 bg-green-50 rounded-xl border border-green-100 ring-4 ring-green-50/50">
                                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-600 mb-3">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <p class="text-green-900 font-bold text-lg leading-tight">Approved</p>
                                        <p class="text-xs text-green-600 mt-2 font-medium">Verified on {{ $application->approved_at ? $application->approved_at->format('M d, Y') : 'N/A' }}</p>
                                    </div>
                                @elseif($application->status === 'rejected')
                                    <div class="p-4 bg-red-50 rounded-xl border border-red-100">
                                        <div class="flex items-start gap-3">
                                            <div class="shrink-0 p-2 bg-red-100 rounded-lg text-red-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </div>
                                            <div>
                                                <p class="text-red-900 font-bold">Rejected</p>
                                                <p class="text-sm text-red-700 mt-1 leading-relaxed">{{ $application->rejection_reason }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </x-ui.card>
                    @endif

                    <!-- Payment Status -->
                    <x-ui.card class="overflow-hidden border-none shadow-sm ring-1 ring-gray-200">
                        <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Payment Status
                            </h3>
                        </div>
                        <div class="p-5">
                            @if($application->payment)
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                        <span class="text-sm font-medium text-gray-500">Amount Paid</span>
                                        <span class="font-bold text-gray-900">{{ number_format($application->payment->amount) }} KES</span>
                                    </div>
                                    <div class="flex items-center justify-between px-1">
                                        <span class="text-sm font-medium text-gray-500">Status</span>
                                        <x-ui.badge :status="$application->payment->status" class="scale-110 origin-right" />
                                    </div>
                                    <x-ui.button variant="secondary" size="sm" class="w-full justify-center py-2 border-gray-200 hover:bg-gray-50 hover:text-primary-600 transition-colors" href="{{ route('admin.payments.show', $application->payment) }}">
                                        View Receipt Details
                                    </x-ui.button>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center py-8 bg-gray-50/50 rounded-xl border border-dashed border-gray-300">
                                    <div class="p-3 bg-white rounded-full shadow-sm mb-3">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <span class="text-sm text-gray-500 font-semibold italic">No payment record found</span>
                                </div>
                            @endif
                        </div>
                    </x-ui.card>

                    <!-- Activity Log -->
                    <x-ui.card class="overflow-hidden border-none shadow-sm ring-1 ring-gray-200">
                        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Recent Activity
                            </h3>
                            <a href="{{ route('admin.activity-logs.index') }}" class="text-[10px] font-bold text-primary-600 uppercase tracking-wider hover:text-primary-700 transition-colors">View All</a>
                        </div>
                        <div class="p-5">
                            <ul class="space-y-6 relative before:absolute before:inset-0 before:left-[11px] before:w-0.5 before:bg-gray-100">
                                @if($application->submitted_at)
                                <li class="relative pl-8">
                                    <div class="absolute left-0 top-1 w-[24px] h-[24px] flex items-center justify-center bg-white border-2 border-primary-500 rounded-full z-10">
                                        <div class="w-1.5 h-1.5 bg-primary-500 rounded-full"></div>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900">Application Submitted</span>
                                        <span class="text-xs text-gray-500 mt-0.5">{{ $application->submitted_at->diffForHumans() }}</span>
                                    </div>
                                </li>
                                @endif
                                <li class="relative pl-8">
                                    <div class="absolute left-0 top-1 w-[24px] h-[24px] flex items-center justify-center bg-white border-2 border-gray-300 rounded-full z-10">
                                        <div class="w-1.5 h-1.5 bg-gray-300 rounded-full"></div>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-700">Draft Created</span>
                                        <span class="text-xs text-gray-500 mt-0.5">{{ $application->created_at->diffForHumans() }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </x-ui.card>

                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal (Hidden by default) -->
    <div id="reject-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('reject-modal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('admin.applications.reject', $application) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Reject Application</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-4">Please provide a reason for rejection. This will be sent to the student.</p>
                            <textarea name="rejection_reason" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" rows="3" required placeholder="Reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <x-ui.button variant="danger" class="w-full sm:w-auto sm:ml-3">
                            Confirm Rejection
                        </x-ui.button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('reject-modal').classList.add('hidden')">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>