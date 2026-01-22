<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            @if(isset($application) && $application)
                <x-ui.badge :status="$application->status" />
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Main Action Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(isset($application) && $application && $application->status === 'approved')
                    <div class="space-y-6">
                        <div class="flex items-center justify-between border-b pb-4">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Welcome, {{ $student->first_name }}!</h3>
                                <p class="text-gray-600">Student ID: <span
                                        class="font-mono font-bold text-primary-600">{{ $student->student_code ?? ($application->student_code ?? 'N/A') }}</span>
                                </p>
                            </div>
                            <div class="bg-green-100 text-green-800 px-4 py-2 rounded-full font-bold">
                                Admitted
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <x-ui.card title="Grades" description="View your latest results">
                                <x-slot name="icon">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </x-slot>
                                <a href="{{ route('student.grades') }}"
                                    class="text-primary-600 hover:underline font-medium">View Grades &rarr;</a>
                            </x-ui.card>

                            <x-ui.card title="Schedule" description="Class timetables">
                                <x-slot name="icon">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </x-slot>
                                <a href="{{ route('student.schedule') }}"
                                    class="text-primary-600 hover:underline font-medium">View Schedule &rarr;</a>
                            </x-ui.card>

                            <x-ui.card title="Fees" description="Financial information">
                                <x-slot name="icon">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                </x-slot>
                                <a href="{{ route('student.fees') }}"
                                    class="text-primary-600 hover:underline font-medium">Fee Statements &rarr;</a>
                            </x-ui.card>
                        </div>
                    </div>
                @elseif(isset($application) && $application)
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome!</h3>
                        <p class="text-gray-600 mb-4">Start your admission process today.</p>

                        <form method="POST" action="{{ route('application.create') }}">
                            @csrf
                            <x-ui.button variant="primary">
                                {{ __('Apply Now') }}
                            </x-ui.button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- 4-Card Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Personal Info -->
                <a href="{{ (isset($application) && $application) ? route('application.wizard', $application) . '#step-1' : '#' }}"
                    class="block hover:shadow-lg transition duration-200">
                    <x-ui.card title="Personal Info" description="Basic details about you">
                        <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </x-slot>
                        @if(isset($application) && $application)
                            @if($application->current_step > 1)
                                <span class="text-green-500 text-sm font-bold">✓ Completed</span>
                            @else
                                <span class="text-primary-500 text-sm">In Progress</span>
                            @endif
                        @endif
                    </x-ui.card>
                </a>

                <!-- Parent Info -->
                <a href="{{ (isset($application) && $application) ? route('application.wizard', $application) . '#step-2' : '#' }}"
                    class="block hover:shadow-lg transition duration-200">
                    <x-ui.card title="Parent Info" description="Guardian contact details">
                        <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                        </x-slot>
                        @if(isset($application) && $application)
                            @if($application->current_step > 2)
                                <span class="text-green-500 text-sm font-bold">✓ Completed</span>
                            @elseif($application->current_step == 2)
                                <span class="text-primary-500 text-sm">In Progress</span>
                            @else
                                <span class="text-gray-400 text-sm">Pending</span>
                            @endif
                        @endif
                    </x-ui.card>
                </a>

                <!-- Program Info -->
                <a href="{{ (isset($application) && $application) ? route('application.wizard', $application) . '#step-3' : '#' }}"
                    class="block hover:shadow-lg transition duration-200">
                    <x-ui.card title="Program" description="Select your course">
                        <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222">
                                </path>
                            </svg>
                        </x-slot>
                        @if(isset($application) && $application)
                            @if($application->current_step > 3)
                                <span class="text-green-500 text-sm font-bold">✓ Completed</span>
                            @elseif($application->current_step == 3)
                                <span class="text-primary-500 text-sm">In Progress</span>
                            @else
                                <span class="text-gray-400 text-sm">Pending</span>
                            @endif
                        @endif
                    </x-ui.card>
                </a>

                <x-ui.card title="Documents" description="Upload required files">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </x-slot>
                    @if(isset($application) && $application && $application->current_step > 4)
                        <span class="text-green-500 text-sm">✓ Completed</span>
                    @endif
                </x-ui.card>
            </div>
        </div>
    </div>
</x-app-layout>