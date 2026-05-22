<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Beneficiario;

class BeneficiariosSeeder extends Seeder
{
    public function run()
    {
        $beneficiarios = [
            [
                'tipo' => 'natural',
                'ci_nit' => '1234567',
                'nombre_razon_social' => 'Juan Carlos',
                'apellidos' => 'Pérez Mamani',
                'direccion' => 'Calle Sucre N° 123',
                'telefono' => '4123456',
                'email' => 'juan.perez@email.com',
                'cuenta_bancaria' => '123-4567890',
                'banco' => 'BCP',
                'activo' => true
            ],
            [
                'tipo' => 'natural',
                'ci_nit' => '7654321',
                'nombre_razon_social' => 'María Elena',
                'apellidos' => 'Flores Rojas',
                'direccion' => 'Av. América N° 456',
                'telefono' => '4234567',
                'email' => 'maria.flores@email.com',
                'cuenta_bancaria' => '987-6543210',
                'banco' => 'MERCANTIL',
                'activo' => true
            ],
            [
                'tipo' => 'empresa',
                'ci_nit' => '1001234023',
                'nombre_razon_social' => 'Farmacias Boliviana S.A.',
                'apellidos' => null,
                'direccion' => 'Av. Libertador N° 789',
                'telefono' => '4345678',
                'email' => 'contacto@farmaciasboliviana.com',
                'cuenta_bancaria' => '456-1237890',
                'banco' => 'BISA',
                'activo' => true
            ],
            [
                'tipo' => 'empresa',
                'ci_nit' => '2005678012',
                'nombre_razon_social' => 'Laboratorios Roche Bolivia',
                'apellidos' => null,
                'direccion' => 'Calle Potosí N° 234',
                'telefono' => '4456789',
                'email' => 'ventas@roche.bo',
                'cuenta_bancaria' => '789-4561230',
                'banco' => 'UNION',
                'activo' => true
            ],
            [
                'tipo' => 'natural',
                'ci_nit' => '3456789',
                'nombre_razon_social' => 'Carlos Alberto',
                'apellidos' => 'Mendoza Vargas',
                'direccion' => 'Barrio Primero de Mayo Calle 2 N° 15',
                'telefono' => '4567890',
                'email' => 'carlos.mendoza@email.com',
                'cuenta_bancaria' => '321-6549870',
                'banco' => 'PRODEM',
                'activo' => true
            ],
        ];
        
        foreach ($beneficiarios as $beneficiario) {
            Beneficiario::updateOrCreate(
                ['ci_nit' => $beneficiario['ci_nit']],
                $beneficiario
            );
        }
    }
}