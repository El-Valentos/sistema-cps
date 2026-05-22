<?php

namespace App\Http\Controllers;

use App\Models\OrdenPago;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArchivosController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService,
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        // Órdenes enviadas por Contabilidad (después de auditoría) para archivo final
        $ordenes = OrdenPago::where('estado', 'enviado_archivos')
            ->with(['beneficiario', 'cheque'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('archivos.index', compact('ordenes'));
    }

    public function archivar(OrdenPago $ordenPago)
    {
        try {
            DB::beginTransaction();

            $ordenPago->update(['estado' => 'archivado']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'archivo_final',
                'enviado_archivos',
                'archivado',
                'Orden de pago archivada definitivamente por el área de Archivos (Fin del flujo)'
            );

            DB::commit();

            return redirect()->route('archivos.index')
                ->with('success', 'Orden archivada definitivamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al archivar la orden: ' . $e->getMessage());
        }
    }

    public function archivados()
    {
        // Órdenes que ya están archivadas
        $ordenes = OrdenPago::where('estado', 'archivado')
            ->with(['beneficiario', 'cheque'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('archivos.archivados', compact('ordenes'));
    }
}
