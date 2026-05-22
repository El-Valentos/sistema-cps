<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\{OrdenPago, Cheque};
use App\Policies\{OrdenPagoPolicy, ChequePolicy};

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        OrdenPago::class => OrdenPagoPolicy::class,
        Cheque::class => ChequePolicy::class,
    ];
    
    public function boot()
    {
        $this->registerPolicies();
        
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Super Admin')) {
                return true;
            }
        });
        
        Gate::define('ver_dashboard', function ($user) {
            return $user->hasAnyRole(['Super Admin', 'Tesorería', 'Contabilidad', 'Caja', 'Financiera', 'Presupuesto', 'Administración']);
        });
        
        Gate::define('ver_reportes', function ($user) {
            return $user->hasAnyRole(['Super Admin', 'Tesorería', 'Contabilidad', 'Caja', 'Financiera', 'Presupuesto', 'Administración']);
        });
        
        Gate::define('exportar_reportes', function ($user) {
            return $user->hasAnyRole(['Super Admin', 'Financiera', 'Administración']);
        });
        
        Gate::define('ver_configuracion', function ($user) {
            return $user->hasAnyRole(['Super Admin', 'Administración']);
        });
        
        Gate::define('editar_configuracion', function ($user) {
            return $user->hasRole('Super Admin');
        });
    }
}