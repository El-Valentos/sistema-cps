<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Area;
use App\Models\CategoriaGasto;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesYPermisosSeeder::class,
            AreasSeeder::class,
        ]);

        $areas = Area::all()->keyBy('codigo');

        $users = [
            ['name' => 'Administrador CPS',      'email' => 'admin@cps.bo',          'password' => 'Admin1234!', 'cargo' => 'Administrador del Sistema', 'area' => null,     'rol' => 'Super Admin'],
            ['name' => 'Juan Pérez',              'email' => 'tesoreria@cps.bo',      'password' => 'Tesorer1a!', 'cargo' => 'Jefe de Tesorería',        'area' => 'TES',    'rol' => 'Tesorería'],
            ['name' => 'María López',             'email' => 'contabilidad@cps.bo',   'password' => 'Contab1l!',  'cargo' => 'Contadora General',        'area' => 'CON',    'rol' => 'Contabilidad'],
            ['name' => 'Carlos Ruiz',             'email' => 'caja@cps.bo',           'password' => 'Caja123!',   'cargo' => 'Cajero Principal',         'area' => 'CAJ',    'rol' => 'Caja'],
            ['name' => 'Ana Flores',              'email' => 'financiera@cps.bo',     'password' => 'Financ13!',  'cargo' => 'Analista Financiero',      'area' => 'FIN',    'rol' => 'Financiera'],
            ['name' => 'Roberto Paz',             'email' => 'presupuesto@cps.bo',    'password' => 'Presup12!',  'cargo' => 'Jefe de Presupuesto',      'area' => 'PRE',    'rol' => 'Presupuesto'],
            ['name' => 'Laura Méndez',            'email' => 'administracion@cps.bo', 'password' => 'Admin123!',  'cargo' => 'Administradora',           'area' => 'ADM',    'rol' => 'Administración'],
            ['name' => 'Sandra Vargas',           'email' => 'archivos@cps.bo',       'password' => 'Archiv12!',  'cargo' => 'Archivera',                'area' => 'ARC',    'rol' => 'Archivos'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make($data['password']),
                    'cargo'    => $data['cargo'],
                    'telefono' => '4' . random_int(100000, 999999),
                    'area_id'  => $data['area'] ? ($areas[$data['area']]->id ?? null) : null,
                    'activo'   => true,
                ]
            );
            $user->assignRole($data['rol']);
        }

        $categorias = [
            ['nombre' => 'Incapacidad temporal', 'codigo' => 'IT', 'partida_presupuestaria' => '2.1.1.001', 'presupuesto_anual' => 5000000, 'requiere_aprobacion' => false],
            ['nombre' => 'Medicamentos',          'codigo' => 'ME', 'partida_presupuestaria' => '2.1.2.005', 'presupuesto_anual' => 15000000, 'requiere_aprobacion' => true],
            ['nombre' => 'Servicios',             'codigo' => 'SE', 'partida_presupuestaria' => '2.2.1.010', 'presupuesto_anual' => 8000000, 'requiere_aprobacion' => true],
            ['nombre' => 'Pasajes',               'codigo' => 'PA', 'partida_presupuestaria' => '2.3.1.015', 'presupuesto_anual' => 2000000, 'requiere_aprobacion' => false],
            ['nombre' => 'Otros',                  'codigo' => 'OT', 'partida_presupuestaria' => null,        'presupuesto_anual' => 0,       'requiere_aprobacion' => false],
        ];

        foreach ($categorias as $cat) {
            CategoriaGasto::firstOrCreate(['codigo' => $cat['codigo']], $cat);
        }

        $this->command?->info('Database seeded correctamente.');
    }
}
