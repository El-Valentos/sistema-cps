<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $ordenPago = App\Models\OrdenPago::where('estado', 'enviado_contabilidad')->first();
    if (!$ordenPago) { echo 'No orders in enviado_contabilidad'; exit; }
    echo 'Order ID: ' . $ordenPago->id . PHP_EOL;

    $numeroCheque = 100001;

    $cheque = App\Models\Cheque::create([
        'orden_pago_id' => $ordenPago->id,
        'numero_cheque' => $numeroCheque,
        'numero_cuenta' => '12345',
        'banco' => 'BNB',
        'fecha_emision' => now(),
        'monto' => $ordenPago->neto_pagar,
        'monto_literal' => 'DOS MIL',
        'emitido_por' => 1,
        'fecha_emision_sistema' => now(),
        'estado' => 'emitido'
    ]);
    echo 'Cheque created';
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
}
