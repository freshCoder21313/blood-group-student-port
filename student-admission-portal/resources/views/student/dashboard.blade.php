<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Student Dashboard') }}
            </h2>
            <x-ui.status-badge :status="'approved'" />
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Welcome Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-center md:text-left flex flex-col md:flex-row justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">Welcome, {{ $student->first_name }}!</h3>
                        <p class="text-gray-600 mt-1">Student ID: <span class="font-mono font-bold text-blue-600">{{ $student->student_code ?? 'Pending' }}</span></p>
                    </div>
                    <div class="mt-4 md:mt-0">
                         <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-semibold">
                            Academic Year: {{ date('Y') }}
                         </span>
                    </div>
                </div>
            </div>

            <!-- Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Grades -->
                <a href="{{ route('student.grades') }}" class="block hover:shadow-lg transition duration-200">
                    <x-ui.card title="My Grades" description="View academic performance">
                         <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                         </x-slot>
                    </x-ui.card>
                </a>
                
                <!-- Schedule -->
                <a href="{{ route('student.schedule') }}" class="block hover:shadow-lg transition duration-200">
                    <x-ui.card title="Class Schedule" description="View weekly timetable">
                         <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                         </x-slot>
                    </x-ui.card>
                </a>
                
                <!-- Fees -->
                <a href="{{ route('student.fees') }}" class="block hover:shadow-lg transition duration-200">
                    <x-ui.card title="Fee Statement" description="Check outstanding balance">
                         <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                         </x-slot>
                    </x-ui.card>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>
