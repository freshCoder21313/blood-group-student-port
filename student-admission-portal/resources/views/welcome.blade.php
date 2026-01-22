<x-landing-layout>
    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <x-ui.container>
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center gap-2">
                        <x-application-logo class="block h-8 w-auto text-primary-600" />
                        <span class="font-bold text-xl tracking-tight text-gray-900 dark:text-white">{{ config('app.name') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    @if (Route::has('login'))
                        @auth
                            <x-ui.button variant="ghost" href="{{ url('/dashboard') }}">Dashboard</x-ui.button>
                        @else
                            <x-ui.button variant="ghost" href="{{ route('login') }}">{{ __('Log in') }}</x-ui.button>
                            @if (Route::has('register'))
                                <x-ui.button variant="primary" href="{{ route('register') }}">{{ __('Apply Now') }}</x-ui.button>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </x-ui.container>
    </nav>

    <!-- Hero Section -->
    <main class="flex-grow flex items-center justify-center">
        <x-ui.container class="py-12 lg:py-24 grid lg:grid-cols-2 gap-12 items-center">
            
            <!-- Left Content -->
            <div class="text-center lg:text-left space-y-6">
                <h1 class="text-4xl lg:text-6xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-tight">
                    {{ __('Start Your Journey') }} <br>
                    <span class="text-primary-600 dark:text-primary-400">{{ __('To Your Future') }}</span>
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    {{ __('Welcome to the Online Admission Portal. Submit your application, track results, and enroll quickly and conveniently in just a few simple steps.') }}
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-4">
                    @if (Route::has('register'))
                        <x-ui.button variant="primary" size="lg" href="{{ route('register') }}" class="gap-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                            <span>{{ __('Apply Now') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </x-ui.button>
                    @endif
                    <x-ui.button variant="secondary" size="lg" href="#guide">
                        {{ __('Process Guide') }}
                    </x-ui.button>
                </div>

                <!-- Stats / Trust Indicators -->
                <div class="pt-8 border-t border-gray-100 dark:border-gray-700 flex justify-center lg:justify-start gap-8 lg:gap-12">
                    <div>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">5+</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Academic Programs') }}</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">24/7</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Online Support') }}</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">100%</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Digital Process') }}</p>
                    </div>
                </div>
            </div>

            <!-- Right Image/Illustration -->
            <div class="relative hidden lg:block">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-gray-100 dark:bg-gray-800 aspect-[4/3] group ring-1 ring-gray-900/5">
                    <!-- Placeholder for Hero Image -->
                    <div class="absolute inset-0 bg-gradient-to-br from-primary-100 to-primary-50 dark:from-primary-900/30 dark:to-gray-900 flex items-center justify-center">
                         <svg class="w-32 h-32 text-primary-200 dark:text-primary-800 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm p-6 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-primary-100 dark:bg-primary-900/50 rounded-full text-primary-600 dark:text-primary-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ __('Application Status') }}</p>
                                <p class="text-sm text-gray-500">{{ __('Real-time updates from the system') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Decorative element -->
                <div class="absolute -z-10 -top-6 -right-6 w-24 h-24 bg-primary-500/10 rounded-full blur-2xl"></div>
                <div class="absolute -z-10 -bottom-8 -left-8 w-32 h-32 bg-secondary-500/10 rounded-full blur-3xl"></div>
            </div>

        </x-ui.container>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 py-8">
        <x-ui.container>
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <div class="flex gap-6 text-sm text-gray-500 dark:text-gray-400">
                    <a href="#" class="hover:text-gray-900 dark:hover:text-white transition-colors">{{ __('Terms') }}</a>
                    <a href="#" class="hover:text-gray-900 dark:hover:text-white transition-colors">{{ __('Privacy') }}</a>
                    <a href="#" class="hover:text-gray-900 dark:hover:text-white transition-colors">{{ __('Contact') }}</a>
                </div>
            </div>
        </x-ui.container>
    </footer>
</x-landing-layout>