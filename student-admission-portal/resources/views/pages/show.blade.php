<x-landing-layout>
    {{-- Simple navbar for public pages --}}
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

    {{-- Page Content --}}
    <main class="flex-grow">
        <div class="py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <x-ui.card>
                    <article class="prose prose-sm sm:prose max-w-none prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-primary-600 prose-li:text-gray-600">
                        {!! $page->content !!}
                    </article>

                    <div class="mt-8 pt-4 border-t border-gray-100 text-sm text-gray-400">
                        Last updated: {{ $page->updated_at->format('F j, Y') }}
                    </div>
                </x-ui.card>
            </div>
        </div>
    </main>

    @include('layouts.footer')
</x-landing-layout>
