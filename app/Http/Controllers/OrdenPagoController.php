<?php

namespace App\Http\Controllers;

use App\Models\{OrdenPago, Beneficiario, CategoriaGasto, User};
use App\Http\Requests\OrdenPagoRequest;
use App\Services\TrackingService;
use App\Services\BeneficiarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdenPagoController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService,
        protected BeneficiarioService $beneficiarioService,
    ) {
        $this->middleware('auth');
    }

    private function getTrackingService(): TrackingService
    {
        return new TrackingService();
    }

    public function index(Request $request)
    {
        $query = OrdenPago::with(['beneficiario', 'categoriaGasto', 'creador', 'cheque']);

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_orden', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_orden', '<=', $request->fecha_hasta);
        }

        if ($request->filled('beneficiario')) {
            $query->where(function($q) use ($request) {
                $q->where('beneficiario_nombre', 'like', "%{$request->beneficiario}%")
                   ->orWhere('beneficiario_apellidos', 'like', "%{$request->beneficiario}%")
                   ->orWhere('beneficiario_ci_nit', 'like', "%{$request->beneficiario}%");
            });
        }

        // Filtro por rol
        if (!auth()->user()->hasRole('Super Admin')) {
            if (auth()->user()->hasRole('Tesorería')) {
                $query->whereIn('estado', ['pendiente_tesoreria', 'reenviado_financiera', 'rechazado_financiera']);
            } elseif (auth()->user()->hasRole('Contabilidad')) {
                $query->where('estado', 'enviado_contabilidad');
            } elseif (auth()->user()->hasRole('Caja')) {
                $query->where('estado', 'cheque_generado');
            } elseif (auth()->user()->hasRole('Financiera')) {
                $query->whereIn('estado', ['enviado_financiera', 'reenviado_financiera', 'enviado_contabilidad', 'rechazado_financiera']);
            }
        }

        $ordenes = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('ordenes-pago.index', compact('ordenes'));
    }

    public function create()
    {
        $categorias = CategoriaGasto::where('activo', true)->get();
        return view('ordenes-pago.create', compact('categorias'));
    }

    public function store(OrdenPagoRequest $request)
    {
        try {
            DB::beginTransaction();

            $beneficiario = Beneficiario::findOrFail($request->beneficiario_id);

            // Calcular montos
            $montoTotal = $request->monto_total;
            $retencion7 = $request->aplica_retencion_7 ? $montoTotal * 0.07 : 0;
            $retencion35 = $request->aplica_retencion_35 ? $montoTotal * 0.035 : 0;
            $netoPagar = $montoTotal - $retencion7 - $retencion35;

            // Crear orden de pago con snapshot del beneficiario
            $orden = OrdenPago::create([
                'gestion' => date('Y'),
                'ciudad' => config('app.ciudad'),
                'beneficiario_id' => $beneficiario->id,
                'beneficiario_nombre' => $beneficiario->nombre_razon_social,
                'beneficiario_apellidos' => $beneficiario->apellidos,
                'beneficiario_ci_nit' => $beneficiario->ci_nit,
                'beneficiario_direccion' => $beneficiario->direccion,
                'beneficiario_telefono' => $beneficiario->telefono,
                'a_la_orden_de' => $request->a_la_orden_de ?? trim($beneficiario->nombre_razon_social . ' ' . $beneficiario->apellidos),
                'monto_total' => $montoTotal,
                'retencion_7' => $retencion7,
                'retencion_35' => $retencion35,
                'neto_pagar' => $netoPagar,
                'concepto' => $request->concepto,
                'categoria_gasto_id' => $request->categoria_gasto_id,
                'concepto_pago' => $request->concepto_pago,
                'numero_fojas' => $request->numero_fojas ?? 0,
                'tiene_respaldo' => $request->hasFile('documentos'),
                'creado_por' => auth()->id(),
                'liquidador_texto' => $request->liquidador_texto ?? auth()->user()->name,
                'fecha_orden' => now(),
                'observaciones' => $request->observaciones
            ]);

            // Subir documentos
            if ($request->hasFile('documentos')) {
                foreach ($request->file('documentos') as $file) {
                    $path = $file->store('documentos/ordenes-pago', 'public');
                    $orden->documentosAdjuntos()->create([
                        'nombre_archivo' => $file->getClientOriginalName(),
                        'ruta_archivo' => $path,
                        'tipo_archivo' => $file->getClientMimeType(),
                        'tamano' => $file->getSize(),
                        'subido_por' => auth()->id()
                    ]);
                }
            }

            // Registrar tracking inicial
            $this->trackingService->registrarEvento(
                $orden,
                'creacion',
                null,
                'pendiente_tesoreria',
                'Orden de pago creada exitosamente'
            );

            DB::commit();

            return redirect()->route('ordenes-pago.show', $orden)
                ->with('success', 'Orden de Pago creada exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al crear la orden: ' . $e->getMessage());
        }
    }

    public function edit(OrdenPago $ordenPago)
    {
        $this->authorize('update', $ordenPago);

        $beneficiarios = Beneficiario::where('activo', true)->orderBy('nombre_razon_social')->get();
        $liquidadores = User::activos()->whereHas('area', function($q) {
            $q->whereIn('codigo', ['TES', 'FIN']);
        })->get();
        $categorias = CategoriaGasto::where('activo', true)->get();

        return view('ordenes-pago.edit', compact('ordenPago', 'beneficiarios', 'liquidadores', 'categorias'));
    }

    public function update(OrdenPagoRequest $request, OrdenPago $ordenPago)
    {
        $this->authorize('update', $ordenPago);

        try {
            DB::beginTransaction();
            
            // Determinar si es orden editable (solo pendiente_tesoreria)
            $esEditable = $ordenPago->estado === 'pendiente_tesoreria';
            
            // Si no es editable, verificar que no intenten cambiar el beneficiario
            if (!$esEditable && $request->filled('nombre_razon_social') 
                && $request->nombre_razon_social !== $ordenPago->beneficiario_nombre) {
                throw new \Exception('No se puede modificar el beneficiario de una orden en proceso');
            }
            
            // Calcular montos
            $montoTotal = $request->monto_total;
            $retencion7 = $request->aplica_retencion_7 ? $montoTotal * 0.07 : 0;
            $retencion35 = $request->aplica_retencion_35 ? $montoTotal * 0.035 : 0;
            $netoPagar = $montoTotal - $retencion7 - $retencion35;
            
            // TODOS los campos en UNA sola actualizacion
            $data = [
                'monto_total' => $montoTotal,
                'retencion_7' => $retencion7,
                'retencion_35' => $retencion35,
                'neto_pagar' => $netoPagar,
                'concepto' => $request->concepto,
                'categoria_gasto_id' => $request->categoria_gasto_id,
                'concepto_pago' => $request->concepto_pago,
                'numero_fojas' => $request->numero_fojas ?? 0,
                'liquidador_texto' => $request->liquidador_texto ?? ($ordenPago->liquidador_texto ?? $ordenPago->liquidador->name ?? ''),
                'fecha_orden' => $request->fecha_orden,
                'observaciones' => $request->observaciones
            ];

            if ($esEditable) {
                $beneficiario = $this->beneficiarioService->findOrCreate([
                    'ci_nit' => $request->ci_nit,
                    'apellidos' => $request->apellidos,
                    'nombre_razon_social' => $request->nombre_razon_social,
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                ]);

                $data['beneficiario_id'] = $beneficiario->id;
                $data['beneficiario_nombre'] = $beneficiario->nombre_razon_social;
                $data['beneficiario_apellidos'] = $beneficiario->apellidos;
                $data['beneficiario_ci_nit'] = $beneficiario->ci_nit;
                $data['beneficiario_telefono'] = $beneficiario->telefono;
                $data['beneficiario_direccion'] = $beneficiario->direccion;
                $data['a_la_orden_de'] = $request->a_la_orden_de ?? trim($beneficiario->nombre_razon_social . ' ' . $beneficiario->apellidos);
            } else {
                // No editable: mantener datos existentes
                $data['a_la_orden_de'] = $ordenPago->a_la_orden_de;
            }
            
            $ordenPago->update($data);
            
            // Subir documentos adicionales si hay
            if ($request->hasFile('documentos')) {
                $ordenPago->update(['tiene_respaldo' => true]);
                foreach ($request->file('documentos') as $file) {
                    $path = $file->store('documentos/ordenes-pago', 'public');
                    $ordenPago->documentosAdjuntos()->create([
                        'nombre_archivo' => $file->getClientOriginalName(),
                        'ruta_archivo' => $path,
                        'tipo_archivo' => $file->getClientMimeType(),
                        'tamano' => $file->getSize(),
                        'subido_por' => auth()->id()
                    ]);
                }
            }

            // Registrar tracking
            $this->trackingService->registrarEvento(
                $ordenPago,
                'edicion',
                $ordenPago->estado,
                $ordenPago->estado,
                'Orden de pago editada'
            );

            DB::commit();

            return redirect()->route('ordenes-pago.show', $ordenPago)
                ->with('success', 'Orden de Pago actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al actualizar la orden: ' . $e->getMessage());
        }
    }

    public function show(OrdenPago $ordenPago)
    {
        $this->authorize('view', $ordenPago);

        $ordenPago->load(['beneficiario', 'categoriaGasto', 'creador', 'liquidador',
                         'cheque', 'trackingHistorial.usuario', 'documentosAdjuntos']);

        return view('ordenes-pago.show', compact('ordenPago'));
    }

    public function aprobar(OrdenPago $ordenPago)
    {
        $this->authorize('aprobar', $ordenPago);

        if ($ordenPago->estado !== 'pendiente_tesoreria') {
            return back()->with('error', 'Esta orden no está pendiente de aprobación por Tesorería');
        }

        try {
            DB::beginTransaction();

            $ordenPago->update([
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => now(),
                'estado' => 'enviado_financiera'
            ]);

            // Registrar tracking
            $this->trackingService->registrarEvento(
                $ordenPago,
                'envio_financiera',
                'pendiente_tesoreria',
                'enviado_financiera',
                'Orden aprobada y enviada a Financiera'
            );

            DB::commit();

            return redirect()->route('ordenes-pago.show', $ordenPago)
                ->with('success', 'Orden aprobada y enviada a Financiera');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al aprobar la orden: ' . $e->getMessage());
        }
    }

    public function reenviarFinanciera(OrdenPago $ordenPago)
    {
        if ($ordenPago->estado !== 'rechazado_financiera') {
            return back()->with('error', 'Solo se pueden reenviar órdenes rechazadas por Financiera');
        }

        try {
            DB::beginTransaction();

            $ordenPago->update([
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => now(),
                'estado' => 'reenviado_financiera'
            ]);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'reenvio_financiera',
                'rechazado_financiera',
                'reenviado_financiera',
                'Orden reenviada a Financiera después de rechazo'
            );

            DB::commit();

            return redirect()->route('ordenes-pago.show', $ordenPago)
                ->with('success', 'Orden reenviada a Financiera exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al reenviar la orden: ' . $e->getMessage());
        }
    }

    public function enviarMasivo(Request $request)
    {
        $ids = $request->input('ordenes', []);
        
        if (empty($ids)) {
            return back()->with('warning', 'No se seleccionaron órdenes para enviar');
        }

        try {
            DB::beginTransaction();
            
            $ordenes = OrdenPago::whereIn('id', $ids)
                ->where('estado', 'pendiente_tesoreria')
                ->get();
            
            $cont = 0;
            foreach ($ordenes as $orden) {
                // Verificar permisos para cada orden si es necesario, 
                // pero por ahora asumimos que si pueden ver la lista pueden aprobar las suyas
                
                $orden->update([
                    'aprobado_por' => auth()->id(),
                    'fecha_aprobacion' => now(),
                    'estado' => 'enviado_financiera'
                ]);

                $this->trackingService->registrarEvento(
                    $orden,
                    'envio_financiera',
                    'pendiente_tesoreria',
                    'enviado_financiera',
                    'Orden aprobada masivamente y enviada a Financiera'
                );
                $cont++;
            }

            DB::commit();

            return back()->with('success', "Se han enviado {$cont} órdenes a Financiera correctamente");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al procesar el envío masivo: ' . $e->getMessage());
        }
    }

    public function generarPDF(OrdenPago $ordenPago)
    {
        $ordenPago->load(['beneficiario', 'creador', 'liquidador', 'aprobadoPor', 'documentosAdjuntos']);

        $pdf = PDF::loadView('dpfs.orden-pago', compact('ordenPago'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download("Orden_Pago_{$ordenPago->numero_orden}.pdf");
    }

    public function generarCheque(OrdenPago $ordenPago)
    {
        if ($ordenPago->estado !== 'enviado_contabilidad') {
            return back()->with('error', 'Esta orden no está en condiciones de generar un cheque');
        }

        return redirect()->route('cheques.create', ['ordenPago' => $ordenPago->id]);
    }
}