<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = app()->make(App\Http\Controllers\ChequeController::class);

$reflection = new \ReflectionClass($controller);
$method = $reflection->getMethod('convertirNumeroALiteral');
$method->setAccessible(true);

try {
    $ordenPago = App\Models\OrdenPago::where('estado', 'enviado_contabilidad')->first();
    if (!$ordenPago) { echo 'No orders'; exit; }
    echo "Neto a pagar: " . $ordenPago->neto_pagar . "\n";
    $literal = $method->invoke($controller, $ordenPago->neto_pagar);
    echo "Literal: " . $literal . "\n";
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
}
