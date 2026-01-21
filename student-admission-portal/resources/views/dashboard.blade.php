<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            @if(isset($application) && $application)
                <x-status-badge :status="$application->status" />
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Main Action Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(isset($application) && $application)
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Application #{{ $application->application_number }}</h3>
                        <p class="text-gray-600 mb-4">You have a draft application in progress.</p>
                        
                        <!-- Progress Bar (Simple) -->
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-6 max-w-md mx-auto">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ ($application->current_step / $application->total_steps) * 100 }}%"></div>
                        </div>

                        @php
                            $continueRoute = '#';
                            if(isset($application)) {
                                switch($application->current_step) {
                                    case 1: $continueRoute = route('application.personal', $application); break;
                                    case 2: $continueRoute = route('application.parent', $application); break;
                                    case 3: $continueRoute = route('application.program', $application); break; // Program Selection
                                    case 4: $continueRoute = '#'; break; // Documents (Next Story)
                                    default: $continueRoute = route('dashboard');
                                }
                            }
                        @endphp

                        <a href="{{ $continueRoute }}">
                            <x-ui.primary-button>
                                {{ __('Continue Application') }}
                            </x-ui.primary-button>
                        </a>
                    </div>
                @else
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome!</h3>
                        <p class="text-gray-600 mb-4">Start your admission process today.</p>
                        
                        <form method="POST" action="{{ route('application.create') }}">
                            @csrf
                            <x-ui.primary-button>
                                {{ __('Apply Now') }}
                            </x-ui.primary-button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- 4-Card Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Personal Info -->
                <a href="{{ (isset($application) && $application) ? route('application.personal', $application) : '#' }}" class="block hover:shadow-lg transition duration-200">
                    <x-card title="Personal Info" description="Basic details about you">
                         <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                         </x-slot>
                         @if(isset($application) && $application)
                             @if($application->current_step > 1)
                                <span class="text-green-500 text-sm font-bold">✓ Completed</span>
                             @else
                                <span class="text-blue-500 text-sm">In Progress</span>
                             @endif
                         @endif
                    </x-card>
                </a>
                
                <!-- Parent Info -->
                <a href="{{ (isset($application) && $application) ? route('application.parent', $application) : '#' }}" class="block hover:shadow-lg transition duration-200">
                    <x-card title="Parent Info" description="Guardian contact details">
                         <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                         </x-slot>
                         @if(isset($application) && $application)
                             @if($application->current_step > 2)
                                <span class="text-green-500 text-sm font-bold">✓ Completed</span>
                             @elseif($application->current_step == 2)
                                <span class="text-blue-500 text-sm">In Progress</span>
                             @else
                                <span class="text-gray-400 text-sm">Pending</span>
                             @endif
                         @endif
                    </x-card>
                </a>
                
                <!-- Program Info -->
                <a href="{{ (isset($application) && $application) ? route('application.program', $application) : '#' }}" class="block hover:shadow-lg transition duration-200">
                    <x-card title="Program" description="Select your course">
                         <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                         </x-slot>
                         @if(isset($application) && $application)
                             @if($application->current_step > 3)
                                <span class="text-green-500 text-sm font-bold">✓ Completed</span>
                             @elseif($application->current_step == 3)
                                <span class="text-blue-500 text-sm">In Progress</span>
                             @else
                                <span class="text-gray-400 text-sm">Pending</span>
                             @endif
                         @endif
                    </x-card>
                </a>
                
                <x-card title="Documents" description="Upload required files">
                     <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                     </x-slot>
                     @if(isset($application) && $application && $application->current_step > 4)
                        <span class="text-green-500 text-sm">✓ Completed</span>
                     @endif
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
