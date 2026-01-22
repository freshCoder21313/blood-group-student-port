<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Admin Welcome -->
            <x-ui.card>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome, Administrator!</h3>
                <p class="text-gray-600">Here is an overview of the current admission cycle.</p>
            </x-ui.card>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Pending Applications -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition border-l-4 border-yellow-400">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Pending Review</p>
                            <p class="text-2xl font-bold text-gray-800">{{ \App\Models\Application::where('status', 'pending_approval')->count() }}</p>
                        </div>
                    </div>
                    <div class="mt-4 text-right">
                        <a href="{{ route('admin.applications.index') }}" class="text-primary-600 text-sm font-medium hover:underline">View All &rarr;</a>
                    </div>
                </div>

                <!-- Pending Payments -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition border-l-4 border-blue-400">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Pending Payments</p>
                            <p class="text-2xl font-bold text-gray-800">{{ \App\Models\Payment::where('status', 'pending_verification')->count() }}</p>
                        </div>
                    </div>
                    <div class="mt-4 text-right">
                        <a href="{{ route('admin.payments.index') }}" class="text-primary-600 text-sm font-medium hover:underline">Verify Payments &rarr;</a>
                    </div>
                </div>

                <!-- Approved Students -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition border-l-4 border-green-400">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Approved Students</p>
                            <p class="text-2xl font-bold text-gray-800">{{ \App\Models\Application::whereIn('status', ['approved', 'student'])->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.card>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Applications by Status</h3>
                    <div class="relative h-64">
                        <canvas id="statusChart"></canvas>
                    </div>
                </x-ui.card>
                
                <x-ui.card>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Applications by Program</h3>
                    <div class="relative h-64">
                        <canvas id="programChart"></canvas>
                    </div>
                </x-ui.card>
            </div>

            <!-- Recent Activity -->
            <x-ui.card>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Applications</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach(\App\Models\Application::with(['student', 'program'])->latest()->take(5)->get() as $app)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $app->student->first_name ?? 'N/A' }} {{ $app->student->last_name ?? '' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $app->program->code ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-ui.badge :status="$app->status" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $app->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

        </div>
    </div>

    <!-- Chart.js Integration -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($appsByStatus)) !!}.map(s => s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' ')),
                    datasets: [{
                        data: {!! json_encode(array_values($appsByStatus)) !!},
                        backgroundColor: [
                            '#FCD34D', // Pending (Yellow)
                            '#10B981', // Approved (Green)
                            '#EF4444', // Rejected (Red)
                            '#6B7280', // Draft (Gray)
                            '#3B82F6'  // Student (Blue)
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' }
                    }
                }
            });

            // Program Chart
            const programCtx = document.getElementById('programChart').getContext('2d');
            new Chart(programCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($appsByProgram)) !!},
                    datasets: [{
                        label: 'Applications',
                        data: {!! json_encode(array_values($appsByProgram)) !!},
                        backgroundColor: '#2563EB', // Primary-600
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        });
    </script>
</x-app-layout>