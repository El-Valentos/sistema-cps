<?php

namespace App\Http\Controllers;

use App\Models\{OrdenPago, Cheque, Beneficiario, CategoriaGasto};
use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    protected $reporteService;

    public function __construct(ReporteService $reporteService)
    {
        $this->reporteService = $reporteService;
        $this->middleware('auth');
    }

    public function index()
    {
        $beneficiarios = Beneficiario::orderBy('nombre_razon_social')->get();
        $categorias    = CategoriaGasto::where('activo', true)->get();

        return view('reportes.index', compact('beneficiarios', 'categorias'));
    }

    public function consolidado()
    {
        $reportes = $this->reporteService->resumenConsolidado();
        return view('reportes.consolidado', compact('reportes'));
    }

    public function exportarConsolidadoPDF()
    {
        $reportes = $this->reporteService->resumenConsolidado();
        $pdf = Pdf::loadView('reportes.pdf.consolidado', compact('reportes'));
        $pdf->setPaper('legal', 'landscape');
        return $pdf->download("reporte_consolidado_" . date('Ymd_His') . ".pdf");
    }

    public function descargarTemp(string $filename)
    {
        $path = "public/tmp/{$filename}";
        
        if (!\Illuminate\Support\Facades\Storage::exists($path)) {
            abort(404);
        }

        return response()->download(
            \Illuminate\Support\Facades\Storage::path($path),
            $filename,
            ['Content-Type' => 'application/pdf']
        )->deleteFileAfterSend(true);
    }

    public function generar(Request $request)
    {
        $request->validate([
            'tipo_reporte' => 'required|in:ordenes,cheques,beneficiarios',
            'fecha_desde'  => 'required|date',
            'fecha_hasta'  => 'required|date|after_or_equal:fecha_desde',
        ]);

        $datos = $this->reporteService->generarReporte($request->all());

        if ($request->formato === 'csv') {
            return $this->exportarCSV($datos, $request->tipo_reporte);
        }

        return $this->exportarPDF($datos, $request->tipo_reporte);
    }

    private function exportarCSV($datos, $tipo)
    {
        $filename = "reporte_{$tipo}_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($datos, $tipo) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            if ($tipo === 'ordenes' && isset($datos['ordenes'])) {
                fputcsv($file, ['N° Orden', 'Fecha', 'Beneficiario', 'Concepto', 'Monto Total', 'Neto Pagar', 'Estado']);
                foreach ($datos['ordenes'] as $o) {
                    fputcsv($file, [
                        $o->numero_orden ?? '',
                        $o->fecha_orden?->format('d/m/Y'),
                        $o->beneficiario_nombre ?? '',
                        $o->concepto ?? '',
                        $o->monto_total ?? 0,
                        $o->neto_pagar ?? 0,
                        $o->estado ?? '',
                    ]);
                }
            } elseif ($tipo === 'cheques' && isset($datos['cheques'])) {
                fputcsv($file, ['N° Cheque', 'Fecha Emisión', 'Beneficiario', 'Monto', 'Estado']);
                foreach ($datos['cheques'] as $c) {
                    fputcsv($file, [
                        $c->numero_cheque ?? '',
                        $c->fecha_emision?->format('d/m/Y'),
                        $c->ordenPago->beneficiario_nombre ?? '',
                        $c->monto ?? 0,
                        $c->estado ?? '',
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportarPDF($datos, $tipo)
    {
        $view = "reportes.pdf.{$tipo}";

        // Si la vista específica no existe, usar la genérica
        if (!\Illuminate\Support\Facades\View::exists($view)) {
            $view = 'reportes.pdf.generico';
        }

        $pdf = Pdf::loadView($view, compact('datos', 'tipo'));
        $pdf->setPaper('legal', 'landscape');

        return $pdf->download("reporte_{$tipo}_" . date('Ymd_His') . ".pdf");
    }
}