<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\Services\TrackingService;
use App\Services\PDFGeneratorService;
use App\Services\ReporteService;
use App\Services\BeneficiarioService;
use App\Services\WorkflowOrchestratorService;
use App\View\Composers\SidebarComposer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TrackingService::class);
        $this->app->singleton(PDFGeneratorService::class);
        $this->app->singleton(ReporteService::class);
        $this->app->singleton(BeneficiarioService::class);
        $this->app->singleton(WorkflowOrchestratorService::class);
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        View::composer('layouts.sidebar', SidebarComposer::class);
    }
}
