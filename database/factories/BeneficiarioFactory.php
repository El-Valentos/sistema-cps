<?php
namespace Database\Factories;

use App\Models\Beneficiario;
use Illuminate\Database\Eloquent\Factories\Factory;

class BeneficiarioFactory extends Factory
{
    protected $model = Beneficiario::class;
    
    public function definition()
    {
        $tipo = $this->faker->randomElement(['natural', 'empresa']);
        
        return [
            'tipo' => $tipo,
            'ci_nit' => $this->faker->unique()->numerify('########'),
            'nombre_razon_social' => $tipo === 'natural' ? $this->faker->firstName : $this->faker->company,
            'apellidos' => $tipo === 'natural' ? $this->faker->lastName : null,
            'direccion' => $this->faker->address,
            'telefono' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'activo' => true
        ];
    }
}