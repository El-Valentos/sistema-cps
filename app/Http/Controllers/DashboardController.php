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

        // Todas las vistas de dashboard usan la misma vista general
        // con datos filtrados según el rol
        return view('dashboard', compact('stats', 'role'));
    }
}
