<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Blade Component Aliases for main feature modules
        Blade::anonymousComponentPath(resource_path('views/components/main/dashboard'), 'dashboard');
        Blade::anonymousComponentPath(resource_path('views/components/main/dashboard/finance'), 'finance');
        Blade::anonymousComponentPath(resource_path('views/components/main/dashboard/hr'), 'hr');
        Blade::anonymousComponentPath(resource_path('views/components/main/dashboard/logistics'), 'logistics');
        Blade::anonymousComponentPath(resource_path('views/components/main/dashboard/sales'), 'sales');
        Blade::anonymousComponentPath(resource_path('views/components/main/dashboard/ai'), 'ai-dashboard');
        Blade::anonymousComponentPath(resource_path('views/components/main/ai-assistant'), 'ai');
        Blade::anonymousComponentPath(resource_path('views/components/main/e-commerce'), 'ecommerce');
        Blade::anonymousComponentPath(resource_path('views/components/main/forms'), 'form');
        Blade::anonymousComponentPath(resource_path('views/components/main/profile'), 'profile');
        Blade::anonymousComponentPath(resource_path('views/components/main/tables'), 'tables');
        Blade::anonymousComponentPath(resource_path('views/components/main/task'), 'task');
        Blade::anonymousComponentPath(resource_path('views/components/main/pages/faq'), 'faq');
        Blade::anonymousComponentPath(resource_path('views/components/main/pages/file-manager'), 'file-manager');
        Blade::anonymousComponentPath(resource_path('views/components/main/pages/pricing-tables'), 'pricing');
        Blade::anonymousComponentPath(resource_path('views/components/main/pages/integrations'), 'integration');
    }
}