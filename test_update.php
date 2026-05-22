<?php
// Test script to verify the update logic
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find an order in pendiente_tesoreria
$orden = App\Models\OrdenPago::where('estado', 'pendiente_tesoreria')->first();

if (!$orden) {
    echo "No orders in pendiente_tesoreria found\n";
    exit;
}

echo "Order ID: {$orden->id}\n";
echo "Estado: {$orden->estado}\n";
echo "Beneficiario actual: {$orden->beneficiario_nombre}\n";
echo "Apellidos actual: {$orden->beneficiario_apellidos}\n";

// Simulate a request
$request = new \Illuminate\Http\Request();
$request->merge([
    'nombre_razon_social' => 'TEST_BENEFICIARIO_' . time(),
    'apellidos' => 'TEST_APELLIDO',
    'ci_nit' => '12345678',
]);

echo "\nSimulating update...\n";
echo "New nombre_razon_social: " . $request->nombre_razon_social . "\n";

// Test $esEditable logic
$esEditable = $orden->estado === 'pendiente_tesoreria';
echo "esEditable: " . var_export($esEditable, true) . "\n";

// Update if editable
if ($esEditable) {
    $orden->update([
        'beneficiario_nombre' => $request->get('nombre_razon_social', $orden->beneficiario_nombre),
        'beneficiario_apellidos' => $request->get('apellidos', $orden->beneficiario_apellidos),
    ]);
    
    // Refresh from DB
    $orden->refresh();
    echo "\nAfter update:\n";
    echo "Beneficiario: {$orden->beneficiario_nombre}\n";
    echo "Apellidos: {$orden->beneficiario_apellidos}\n";
} else {
    echo "Not editable, skipping update\n";
}
