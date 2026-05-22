<?php
namespace Database\Factories;

use App\Models\{OrdenPago, Beneficiario, CategoriaGasto, User};
use Illuminate\Database\Eloquent\Factories\Factory;

class OrdenPagoFactory extends Factory
{
    protected $model = OrdenPago::class;
    
    public function definition()
    {
        $montoTotal = $this->faker->numberBetween(1000, 50000);
        $retencion7 = $this->faker->boolean(30) ? $montoTotal * 0.07 : 0;
        $retencion35 = $this->faker->boolean(30) ? $montoTotal * 0.035 : 0;
        $devolucion = $this->faker->boolean(10) ? $this->faker->numberBetween(100, 1000) : 0;
        $netoPagar = $montoTotal - $retencion7 - $retencion35 + $devolucion;
        
        $estados = ['pendiente_tesoreria', 'enviado_contabilidad', 'cheque_generado', 'en_caja', 'entregado', 'cerrado'];
        
        return [
            'numero_orden' => 'OP-' . date('Y') . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 5, '0', STR_PAD_LEFT),
            'gestion' => date('Y'),
            'ciudad' => 'Cochabamba',
            'beneficiario_id' => Beneficiario::factory(),
            'a_la_orden_de' => $this->faker->name,
            'monto_total' => $montoTotal,
            'retencion_7' => $retencion7,
            'retencion_35' => $retencion35,
            'devolucion_retencion' => $devolucion,
            'neto_pagar' => $netoPagar,
            'concepto' => $this->faker->sentence,
            'categoria_gasto_id' => CategoriaGasto::factory(),
            'tipo_orden' => $this->faker->randomElement(['orden_pago', 'devolucion']),
            'numero_fojas' => $this->faker->numberBetween(1, 50),
            'tiene_respaldo' => $this->faker->boolean(70),
            'estado' => $this->faker->randomElement($estados),
            'creado_por' => User::factory(),
            'liquidador_id' => User::factory(),
            'fecha_orden' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'observaciones' => $this->faker->optional()->text(200)
        ];
    }
}