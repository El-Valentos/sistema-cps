<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;

class AreasSeeder extends Seeder
{
    public function run()
    {
        $areas = [
            ['nombre' => 'Tesorería', 'codigo' => 'TES', 'orden_flujo' => 1, 'descripcion' => 'Área de Tesorería - Gestión de pagos'],
            ['nombre' => 'Contabilidad', 'codigo' => 'CON', 'orden_flujo' => 2, 'descripcion' => 'Área de Contabilidad - Registro de cheques'],
            ['nombre' => 'Caja', 'codigo' => 'CAJ', 'orden_flujo' => 3, 'descripcion' => 'Área de Caja - Entrega de cheques'],
            ['nombre' => 'Financiera', 'codigo' => 'FIN', 'orden_flujo' => 4, 'descripcion' => 'Área Financiera - Supervisión'],
            ['nombre' => 'Presupuesto', 'codigo' => 'PRE', 'orden_flujo' => 5, 'descripcion' => 'Área de Presupuesto - Control presupuestario'],
            ['nombre' => 'Administración', 'codigo' => 'ADM', 'orden_flujo' => 6, 'descripcion' => 'Área de Administración - Gestión general'],
            ['nombre' => 'Archivos', 'codigo' => 'ARC', 'orden_flujo' => 7, 'descripcion' => 'Área de Archivos - Documentación'],
        ];
        
        foreach ($areas as $area) {
            Area::updateOrCreate(
                ['codigo' => $area['codigo']],
                $area
            );
        }
    }
}