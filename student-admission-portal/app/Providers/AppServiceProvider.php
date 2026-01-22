<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Student\StudentInformationServiceInterface::class,
            function ($app) {
                $driver = config('services.student_info.driver', 'mock');

                if ($driver === 'mock') {
                    return new \App\Services\Student\MockStudentInformationService();
                }

                throw new \RuntimeException("Unknown Student Information Driver: {$driver}");
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
