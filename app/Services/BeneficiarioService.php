<?php

namespace App\Services;

use App\Models\Beneficiario;

class BeneficiarioService
{
    public function findOrCreate(array $data): Beneficiario
    {
        $ciNit = $data['ci_nit'] ?? null;
        $beneficiario = $ciNit ? Beneficiario::where('ci_nit', $ciNit)->first() : null;

        if (!$beneficiario) {
            $beneficiario = Beneficiario::create([
                'tipo' => !empty($data['apellidos']) ? 'persona' : 'empresa',
                'nombre_razon_social' => $data['nombre_razon_social'],
                'apellidos' => $data['apellidos'] ?? null,
                'ci_nit' => $ciNit,
                'telefono' => $data['telefono'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'activo' => true,
            ]);
        } else {
            $beneficiario->update([
                'tipo' => !empty($data['apellidos']) ? 'persona' : 'empresa',
                'nombre_razon_social' => $data['nombre_razon_social'],
                'apellidos' => $data['apellidos'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'direccion' => $data['direccion'] ?? null,
            ]);
        }

        return $beneficiario;
    }
}
