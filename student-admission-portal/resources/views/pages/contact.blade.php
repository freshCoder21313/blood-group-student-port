<x-landing-layout>
    {{-- Navbar --}}
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <x-ui.container>
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex-shrink-0 flex items-center gap-2">
                        <x-application-logo class="block h-8 w-auto text-primary-600" />
                        <span class="font-bold text-xl tracking-tight text-gray-900 dark:text-white">{{ config('app.name') }}</span>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <x-ui.button variant="ghost" href="{{ url('/dashboard') }}">Dashboard</x-ui.button>
                    @else
                        <x-ui.button variant="ghost" href="{{ route('login') }}">{{ __('Log in') }}</x-ui.button>
                        @if (Route::has('register'))
                            <x-ui.button variant="primary" href="{{ route('register') }}">{{ __('Apply Now') }}</x-ui.button>
                        @endif
                    @endauth
                </div>
            </div>
        </x-ui.container>
    </nav>

    {{-- Contact Page --}}
    <main class="flex-grow">
        <div class="py-12">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    {{-- Contact Info Sidebar --}}
                    <div class="lg:col-span-1 space-y-6">
                        <x-ui.card>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">📍 Contact Details</h3>
                            <div class="space-y-4">
                                @if($contactInfo['email'])
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Email</p>
                                        <a href="mailto:{{ $contactInfo['email'] }}" class="text-sm text-primary-600 hover:underline">{{ $contactInfo['email'] }}</a>
                                    </div>
                                </div>
                                @endif

                                @if($contactInfo['phone'])
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Phone</p>
                                        <a href="tel:{{ $contactInfo['phone'] }}" class="text-sm text-primary-600 hover:underline">{{ $contactInfo['phone'] }}</a>
                                    </div>
                                </div>
                                @endif

                                @if($contactInfo['address'])
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Address</p>
                                        <p class="text-sm text-gray-600">{{ $contactInfo['address'] }}</p>
                                    </div>
                                </div>
                                @endif

                                @if($contactInfo['website'])
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Website</p>
                                        <a href="{{ $contactInfo['website'] }}" target="_blank" class="text-sm text-primary-600 hover:underline">{{ $contactInfo['website'] }}</a>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </x-ui.card>
                    </div>

                    {{-- Main Content --}}
                    <div class="lg:col-span-2">
                        <x-ui.card>
                            <article class="prose prose-sm sm:prose max-w-none prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-primary-600 prose-li:text-gray-600">
                                {!! $page->content !!}
                            </article>
                        </x-ui.card>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('layouts.footer')
</x-landing-layout>
