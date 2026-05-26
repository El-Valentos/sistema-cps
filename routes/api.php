<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\DashboardController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Tracking endpoints
    Route::get('/tracking/{orden}/estado', [TrackingController::class, 'getEstado']);
    Route::get('/tracking/{orden}/historial', [TrackingController::class, 'getHistorial']);
    Route::get('/tracking/{orden}/timeline', [TrackingController::class, 'getTimeline']);
    Route::post('/tracking/webhook', [TrackingController::class, 'webhook']);
    
    // Reportes endpoints
    Route::get('/reportes/resumen', [ReporteController::class, 'resumen']);
    Route::get('/reportes/estadisticas', [ReporteController::class, 'estadisticas']);
    Route::post('/reportes/exportar', [ReporteController::class, 'exportar']);
    
    // Dashboard endpoints
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/notificaciones', [DashboardController::class, 'notificaciones']);
    Route::get('/dashboard/actividad-reciente', [DashboardController::class, 'actividadReciente']);
    
    // Beneficiarios endpoints
    Route::get('/beneficiarios', [BeneficiarioController::class, 'index']);
    Route::get('/beneficiarios/{beneficiario}', [BeneficiarioController::class, 'show']);
    Route::get('/beneficiarios/buscar', function (Request $request) {
        if (!$request->has('ci_nit')) {
            return response()->json(['error' => 'CI/NIT requerido'], 422);
        }
        $beneficiario = \App\Models\Beneficiario::where('ci_nit', $request->ci_nit)->first();
        if (!$beneficiario) {
            return response()->json(['found' => false]);
        }
        return response()->json([
            'found' => true,
            'beneficiario' => [
                'id' => $beneficiario->id,
                'nombre_razon_social' => $beneficiario->nombre_razon_social,
                'apellidos' => $beneficiario->apellidos,
                'ci_nit' => $beneficiario->ci_nit,
                'telefono' => $beneficiario->telefono,
                'direccion' => $beneficiario->direccion,
                'banco' => $beneficiario->banco,
                'numero_cuenta' => $beneficiario->numero_cuenta,
            ]
        ]);
    });
});

// Webhook público para notificaciones externas
Route::post('/webhooks/cps/estado', [WebhookController::class, 'handleEstado']);
Route::post('/webhooks/cps/notificacion', [WebhookController::class, 'handleNotificacion']);