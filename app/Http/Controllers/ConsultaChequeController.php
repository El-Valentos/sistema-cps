<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
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

        $query = Cheque::with('ordenPago.beneficiario');

        $query->whereHas('ordenPago', function ($q) use ($valorBusqueda) {
            $q->where(function ($q2) use ($valorBusqueda) {
                $q2->where('beneficiario_nombre', 'LIKE', '%' . $valorBusqueda . '%')
                   ->orWhere('beneficiario_apellidos', 'LIKE', '%' . $valorBusqueda . '%')
                   ->orWhere('beneficiario_ci_nit', 'LIKE', '%' . $valorBusqueda . '%')
                   ->orWhereHas('beneficiario', function ($q3) use ($valorBusqueda) {
                       $q3->where('nombre_razon_social', 'LIKE', '%' . $valorBusqueda . '%')
                          ->orWhere('apellidos', 'LIKE', '%' . $valorBusqueda . '%')
                          ->orWhere('ci_nit', 'LIKE', '%' . $valorBusqueda . '%');
                   });
            });
            $q->whereNotIn('estado', [
                'anulado',
            ]);
        });

        $cheques = $query->orderBy('fecha_emision', 'desc')->get();

        if ($cheques->isEmpty()) {
            return back()
                ->with('error', 'No se encontraron cheques con el dato ingresado.')
                ->withInput();
        }

        $resultados = $cheques->map(function ($cheque) {
            return [
                'cheque' => $cheque,
                'estadoCliente' => $this->determinarEstadoCliente($cheque),
            ];
        });

        return view('consulta-cheque.index', compact('resultados', 'valorBusqueda'));
    }

    private function determinarEstadoCliente($cheque)
    {
        $opEstado = $cheque->ordenPago?->estado;

        $rechazados = ['rechazado_financiera', 'rechazado_contabilidad', 'rechazado_presupuesto', 'rechazado_financiera_cheque', 'rechazado_administracion'];

        if ($opEstado === 'en_caja') {
            return ['key' => 'listo', 'label' => 'Listo para Entrega', 'color' => 'blue'];
        }

        $postEntrega = [
            'entregado', 'entregado_contabilidad', 'enviado_archivos',
            'archivado', 'cerrado', 'cobrado', 'revalidado', 'revalidando',
        ];

        if (in_array($opEstado, $postEntrega)) {
            return ['key' => 'entregado', 'label' => 'Entregado', 'color' => 'green'];
        }

        if (in_array($opEstado, $rechazados)) {
            return ['key' => 'rechazado', 'label' => 'Rechazado', 'color' => 'red'];
        }

        return ['key' => 'tramite', 'label' => 'En Trámite', 'color' => 'yellow'];
    }
}
