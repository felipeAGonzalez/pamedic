<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use App\Observers\DialysisPrescriptionObserver;
use App\Observers\DialysisMonitoringObserver;
use App\Models\DialysisPrescription;
use App\Models\DialysisMonitoring;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrap();
        DialysisPrescription::observe(new DialysisPrescriptionObserver());
        DialysisMonitoring::observe(new DialysisMonitoringObserver());
    }
}
