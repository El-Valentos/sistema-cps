<?php

namespace App\View\Composers;

use App\Models\OrdenPago;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SidebarComposer
{
    public function compose(View $view): void
    {
        $badges = Cache::remember('sidebar_badges', 60, function () {
            return [
                'pending_tesoreria' => OrdenPago::where('estado', 'pendiente_tesoreria')->count(),
                'pending_financiera' => OrdenPago::where('estado', 'enviado_financiera')->count(),
                'pending_contabilidad_audit' => OrdenPago::where('estado', 'entregado_contabilidad')->count(),
                'pending_archivos' => OrdenPago::where('estado', 'enviado_archivos')->count(),
                'revision_cheque' => true,
            ];
        });

        $view->with('sidebarBadges', $badges);
    }
}
