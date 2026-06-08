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
        $request->validate([
            'tipo_busqueda' => 'required|in:beneficiario,ci,nit',
            'valor_busqueda' => 'required|string|max:100',
        ]);

        $tipoBusqueda = $request->tipo_busqueda;
        $valorBusqueda = trim($request->valor_busqueda);

        // Construir la consulta base
        $query = Cheque::with('ordenPago');

        // Aplicar filtro según el tipo de búsqueda
        switch ($tipoBusqueda) {
            case 'beneficiario':
                // Búsqueda parcial por nombre de beneficiario (insensible a mayúsculas)
                $query->whereHas('ordenPago', function ($q) use ($valorBusqueda) {
                    $q->where('beneficiario_nombre', 'LIKE', '%' . $valorBusqueda . '%');
                });
                break;

            case 'ci':
            case 'nit':
                // Búsqueda exacta por CI/NIT
                $query->whereHas('ordenPago', function ($q) use ($valorBusqueda) {
                    $q->where('beneficiario_ci_nit', $valorBusqueda);
                });
                break;
        }

        // Obtener todos los cheques coincidentes, ordenados por fecha más reciente
        $cheques = $query->orderBy('fecha_emision', 'desc')->get();

        if ($cheques->isEmpty()) {
            return back()
                ->with('error', 'No se encontraron cheques con los criterios de búsqueda ingresados.')
                ->withInput();
        }

        // Determinar el estado del cliente para cada cheque
        $resultados = $cheques->map(function ($cheque) {
            return [
                'cheque' => $cheque,
                'estadoCliente' => $this->determinarEstadoCliente($cheque),
            ];
        });

        return view('consulta-cheque.index', compact('resultados', 'tipoBusqueda', 'valorBusqueda'));
    }

    private function determinarEstadoCliente($cheque)
    {
        $opEstado = $cheque->ordenPago?->estado;

        $rechazados = ['rechazado_financiera', 'rechazado_contabilidad', 'rechazado_presupuesto', 'rechazado_financiera_cheque', 'rechazado_administracion'];

        if (in_array($opEstado, ['entregado', 'cerrado'])) {
            return ['key' => 'aprobado', 'label' => 'Aprobado', 'color' => 'green'];
        }

        if (in_array($opEstado, $rechazados)) {
            return ['key' => 'rechazado', 'label' => 'Rechazado', 'color' => 'red'];
        }

        return ['key' => 'tramite', 'label' => 'En Trámite', 'color' => 'yellow'];
    }
}
