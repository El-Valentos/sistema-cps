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
            'numero_cheque' => 'required|string|max:20',
        ]);

        $cheque = Cheque::with('ordenPago')
            ->where('numero_cheque', $request->numero_cheque)
            ->first();

        if (!$cheque) {
            return back()->with('error', 'No se encontró ningún cheque con el número ingresado.');
        }

        $estadoCliente = $this->determinarEstadoCliente($cheque);

        return view('consulta-cheque.index', compact('cheque', 'estadoCliente'));
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
