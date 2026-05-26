<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Area;

try {
    // 2. Create Area
    Area::firstOrCreate(['nombre' => 'Presupuesto'], [
        'codigo' => 'PRE',
        'descripcion' => 'Área de Presupuesto',
        'estado' => 'activo'
    ]);
    echo "Area Presupuesto created.\n";

    // 3. Create Role
    $role = Role::firstOrCreate(['name' => 'Presupuesto']);
    
    // permissions: ver_dashboard, ver_ordenes_pago, ver_cheques, ver_tracking
    $permissions = ['ver_dashboard', 'ver_ordenes_pago', 'ver_cheques', 'ver_tracking', 'ver_reportes'];
    foreach($permissions as $p) {
        $perm = Permission::firstOrCreate(['name' => $p]);
        $role->givePermissionTo($perm);
    }
    
    echo "Role Presupuesto created and permissions assigned.\n";

    // Give Administracion the necessary permissions too just in case
    $adminRole = Role::where('name', 'Administración')->first();
    if($adminRole) {
        $adminRole->givePermissionTo('ver_ordenes_pago');
        $adminRole->givePermissionTo('ver_cheques');
        echo "Administracion permissions updated.\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
