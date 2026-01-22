<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Student Admission Portal') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-100 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center gap-2">
                        <!-- Logo Placeholder -->
                        <div class="w-8 h-8 bg-primary-600 rounded-md flex items-center justify-center text-white font-bold text-lg">A</div>
                        <span class="font-bold text-xl tracking-tight text-gray-900 dark:text-white">Admission Portal</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">{{ __('Log in') }}</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="ml-4 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-md transition duration-150 ease-in-out shadow-sm">{{ __('Apply Now') }}</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="flex-grow flex items-center justify-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-24 grid lg:grid-cols-2 gap-12 items-center">
            
            <!-- Left Content -->
            <div class="text-center lg:text-left space-y-6">
                <h1 class="text-4xl lg:text-6xl font-bold tracking-tight text-gray-900 dark:text-white leading-tight">
                    {{ __('Start Your Journey') }} <br>
                    <span class="text-primary-600 dark:text-primary-400">{{ __('To Your Future') }}</span>
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto lg:mx-0">
                    {{ __('Welcome to the Online Admission Portal. Submit your application, track results, and enroll quickly and conveniently in just a few simple steps.') }}
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-4">
                    @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white text-base font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <span>{{ __('Apply Now') }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </a>
                    @endif
                    <a href="#guide" class="px-8 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-base font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-200">
                        {{ __('Process Guide') }}
                    </a>
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
            <div class="relative lg:block">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-gray-100 dark:bg-gray-800 aspect-[4/3] group">
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
                <div class="absolute -z-10 -bottom-8 -left-8 w-32 h-32 bg-purple-500/10 rounded-full blur-3xl"></div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">© {{ date('Y') }} Student Admission Portal. All rights reserved.</p>
            <div class="flex gap-6 text-sm text-gray-500 dark:text-gray-400">
                <a href="#" class="hover:text-gray-900 dark:hover:text-white">{{ __('Terms') }}</a>
                <a href="#" class="hover:text-gray-900 dark:hover:text-white">{{ __('Privacy') }}</a>
                <a href="#" class="hover:text-gray-900 dark:hover:text-white">{{ __('Contact') }}</a>
            </div>
        </div>
    </footer>

</body>
</html>
