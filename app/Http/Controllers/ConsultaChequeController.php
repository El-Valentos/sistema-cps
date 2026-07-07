<?php

namespace App\Http\Controllers;

use App\Models\OrdenPago;
use Illuminate\Http\Request;

class ConsultaChequeController extends Controller
{
    public function index()
    {
        return view('consulta-cheque.index');
    }

    public function buscar(Request $request)
    {
        $request->merge(['valor_busqueda' => trim($request->valor_busqueda)]);

        $request->validate([
            'valor_busqueda' => 'required|string|max:100|min:1',
        ]);

        $valorBusqueda = $request->valor_busqueda;

        $ordenes = OrdenPago::with('cheque', 'beneficiario')
            ->where(function ($q) use ($valorBusqueda) {
                $q->where('beneficiario_nombre', 'LIKE', '%' . $valorBusqueda . '%')
                   ->orWhere('beneficiario_apellidos', 'LIKE', '%' . $valorBusqueda . '%')
                   ->orWhere('beneficiario_ci_nit', 'LIKE', '%' . $valorBusqueda . '%')
                   ->orWhereHas('beneficiario', function ($q2) use ($valorBusqueda) {
                       $q2->where('nombre_razon_social', 'LIKE', '%' . $valorBusqueda . '%')
                          ->orWhere('apellidos', 'LIKE', '%' . $valorBusqueda . '%')
                          ->orWhere('ci_nit', 'LIKE', '%' . $valorBusqueda . '%');
                   });
            })
            ->whereNotIn('estado', ['anulado'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($ordenes->isEmpty()) {
            return back()
                ->with('error', 'No se encontraron resultados con el dato ingresado.')
                ->withInput();
        }

        $resultados = $ordenes->map(function ($orden) {
            $cheque = $orden->cheque;
            return [
                'orden' => $orden,
                'cheque' => $cheque,
                'estadoCliente' => $this->determinarEstadoCliente($orden->estado),
            ];
        });

        return view('consulta-cheque.index', compact('resultados', 'valorBusqueda'));
    }

    private function determinarEstadoCliente(string $estado): array
    {
        $rechazados = ['rechazado_financiera', 'rechazado_contabilidad', 'rechazado_presupuesto', 'rechazado_financiera_cheque', 'rechazado_administracion'];

        if ($estado === 'en_caja') {
            return ['key' => 'listo', 'label' => 'Listo para Entrega', 'color' => 'blue'];
        }

        $postEntrega = [
            'entregado', 'entregado_contabilidad', 'enviado_archivos',
            'archivado', 'cerrado', 'cobrado', 'revalidado', 'revalidando',
        ];

        if (in_array($estado, $postEntrega)) {
            return ['key' => 'entregado', 'label' => 'Entregado', 'color' => 'green'];
        }

        if (in_array($estado, $rechazados)) {
            return ['key' => 'rechazado', 'label' => 'Rechazado', 'color' => 'red'];
        }

        return ['key' => 'tramite', 'label' => 'En Trámite', 'color' => 'yellow'];
    }
}
