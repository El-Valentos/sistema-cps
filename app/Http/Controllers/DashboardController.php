<?php

namespace App\Http\Controllers;

use App\Models\{OrdenPago, Cheque, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->roles->first()->name ?? 'General';

        $stats = [
            'total_ordenes' => OrdenPago::count(),
            'total_cheques' => Cheque::count(),
            'monto_total'   => OrdenPago::sum('monto_total') ?? 0,
            'monto_pagado'  => OrdenPago::where('estado', 'entregado')->sum('neto_pagar') ?? 0,
        ];

        if ($role === 'Tesorería') {
            $stats = [
                'total_ordenes' => OrdenPago::whereIn('estado', ['pendiente_tesoreria', 'rechazado_financiera'])->count(),
                'pendientes'    => OrdenPago::where('estado', 'pendiente_tesoreria')->count(),
                'monto_pendiente' => OrdenPago::where('estado', 'pendiente_tesoreria')->sum('neto_pagar') ?? 0,
            ];
        } elseif ($role === 'Contabilidad') {
            $stats = [
                'pendientes'    => OrdenPago::where('estado', 'enviado_contabilidad')->count(),
                'cheques'       => Cheque::count(),
                'cheques_emitidos' => Cheque::where('estado', 'emitido')->count(),
            ];
        } elseif ($role === 'Financiera') {
            $stats = [
                'ordenes_pendientes' => OrdenPago::where('estado', 'enviado_financiera')->count(),
                'cheques_pendientes' => OrdenPago::where('estado', 'enviado_financiera_cheque')->count(),
                'total_pendientes'   => OrdenPago::whereIn('estado', ['enviado_financiera', 'enviado_financiera_cheque'])->count(),
            ];
        } elseif (in_array($role, ['Presupuesto', 'Administración', 'Caja', 'Archivos'])) {
            $estadosMap = [
                'Presupuesto'     => 'enviado_presupuesto',
                'Administración'  => 'enviado_administracion',
                'Caja'            => ['en_caja', 'entregado'],
                'Archivos'        => ['enviado_archivos', 'archivado'],
            ];
            $estado = $estadosMap[$role];
            $stats = [
                'total'    => OrdenPago::whereIn('estado', (array)$estado)->count(),
                'activos'  => OrdenPago::where('estado', is_array($estado) ? $estado[0] : $estado)->count(),
            ];
        }

        // Todas las vistas de dashboard usan la misma vista general
        // con datos filtrados según el rol
        return view('dashboard', compact('stats', 'role'));
    }
}
