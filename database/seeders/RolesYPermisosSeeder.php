<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesYPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permisos = [
            'ver_dashboard',
            'ver_beneficiarios', 'crear_beneficiario', 'editar_beneficiario', 'eliminar_beneficiario',
            'ver_ordenes_pago', 'crear_orden_pago', 'editar_orden_pago', 'eliminar_orden_pago', 'aprobar_orden_pago',
            'ver_cheques', 'generar_cheque', 'imprimir_cheque', 'anular_cheque',
            'ver_tracking', 'actualizar_tracking',
            'ver_reportes', 'exportar_reportes',
            'ver_configuracion', 'editar_configuracion',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        $roles = [
            'Super Admin' => $permisos,
            'Tesorería' => [
                'ver_dashboard', 'ver_beneficiarios', 'crear_beneficiario', 'editar_beneficiario',
                'ver_ordenes_pago', 'crear_orden_pago', 'editar_orden_pago', 'aprobar_orden_pago',
                'ver_cheques', 'ver_tracking', 'ver_reportes',
            ],
            'Financiera' => [
                'ver_dashboard', 'ver_beneficiarios',
                'ver_cheques', 'ver_tracking', 'ver_reportes',
            ],
            'Contabilidad' => [
                'ver_dashboard',
                'ver_cheques', 'generar_cheque', 'imprimir_cheque', 'anular_cheque',
                'ver_tracking', 'ver_reportes',
            ],
            'Presupuesto' => [
                'ver_dashboard',
                'ver_cheques', 'ver_tracking', 'ver_reportes',
            ],
            'Administración' => [
                'ver_dashboard', 'ver_configuracion',
                'ver_tracking', 'ver_reportes',
            ],
            'Caja' => [
                'ver_dashboard',
                'ver_tracking', 'ver_reportes',
            ],
            'Archivos' => [
                'ver_dashboard',
                'ver_tracking', 'ver_reportes',
            ],
        ];

        foreach ($roles as $nombre => $permisosRole) {
            $role = Role::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
            $role->syncPermissions($permisosRole);
        }
    }
}


