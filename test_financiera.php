<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\OrdenPago;
use App\Services\TrackingService;

// Login como usuario Financiera (id=5)
Auth::loginUsingId(5);
echo "Usuario: " . Auth::user()->name . " | Rol: " . Auth::user()->roles->first()->name . "\n";

$ordenPago = OrdenPago::find(12);
echo "Orden: " . $ordenPago->numero_orden . " | Estado: " . $ordenPago->estado . "\n";

try {
    DB::beginTransaction();
    
    $ordenPago->update(['estado' => 'enviado_contabilidad']);
    
    app(TrackingService::class)->registrarEvento(
        $ordenPago,
        'envio_contabilidad',
        'enviado_financiera',
        'enviado_contabilidad',
        'Orden aprobada por Financiera y enviada a Contabilidad'
    );
    
    DB::commit();
    echo "OK - Estado actualizado a: " . $ordenPago->fresh()->estado . "\n";
} catch (\Exception $e) {
    DB::rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . " en " . $e->getFile() . "\n";
}
