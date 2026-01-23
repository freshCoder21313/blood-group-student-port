<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Application Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <h3 class="text-lg font-medium">All Applications</h3>
                        
                        <form method="GET" action="{{ route('admin.applications.index') }}" class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                            <x-ui.input type="text" name="search" placeholder="Search by App # or Name" value="{{ request('search') }}" class="w-full md:w-64" />
                            <x-ui.select name="status" class="w-full md:w-48">
                                <option value="">All Statuses</option>
                                @foreach(['draft', 'pending_payment', 'pending_approval', 'approved', 'rejected'] as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                            <div class="flex gap-2">
                                <x-ui.button variant="secondary" type="submit">Filter</x-ui.button>
                                @if(request('search') || request('status'))
                                    <x-ui.button variant="ghost" href="{{ route('admin.applications.index') }}">Clear</x-ui.button>
                                @endif
                            </div>
                        </form>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">App #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($applications as $app)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $app->application_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $app->student->first_name ?? 'N/A' }} {{ $app->student->last_name ?? '' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $app->program->code ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $app->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $app->status === 'pending_approval' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $app->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ ucfirst($app->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('admin.applications.show', $app) }}" class="text-primary-600 hover:text-primary-900">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <p class="text-lg font-medium text-gray-900">No applications found</p>
                                                <p class="text-sm text-gray-500">Try adjusting your search or filters.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $applications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
