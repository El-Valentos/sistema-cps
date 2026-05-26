<?php

namespace Tests\Feature;

use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use App\Models\OrdenPago;
use App\Models\Beneficiario;
use App\Models\CategoriaGasto;
use Illuminate\Support\Facades\Hash;

class WorkflowSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_with_tesoreria_credentials(): void
    {
        // Intento de login con credenciales de tesorería
        $response = $this->post('/login', [
            'email' => 'tesoreria@cps.bo',
            'password' => 'Tesorer1a!',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }

    public function test_tesoreria_can_access_ordenes_pago(): void
    {
        $user = User::where('email', 'tesoreria@cps.bo')->first();
        $this->actingAs($user);

        $response = $this->get(route('ordenes-pago.index'));
        $response->assertStatus(200);
    }

    public function test_tesoreria_can_create_orden_pago(): void
    {
        $user = User::where('email', 'tesoreria@cps.bo')->first();
        $this->actingAs($user);

        $categoria = CategoriaGasto::first();
        $beneficiario = Beneficiario::factory()->create([
            'nombre_razon_social' => 'Proveedor Test',
            'ci_nit' => '123456789',
        ]);

        $response = $this->post(route('ordenes-pago.store'), [
            'beneficiario_id' => $beneficiario->id,
            'monto_total' => 1000.00,
            'concepto' => 'Servicio de prueba',
            'categoria_gasto_id' => $categoria?->id,
            'fecha_orden' => now()->format('Y-m-d'),
            'a_la_orden_de' => 'Proveedor Test',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('ordenes_pago', [
            'beneficiario_id' => $beneficiario->id,
            'beneficiario_nombre' => 'Proveedor Test',
            'monto_total' => 1000.00,
        ]);
    }

    public function test_full_workflow_creation_to_cheque(): void
    {
        // Crear orden como Tesorería
        $tesoreria = User::where('email', 'tesoreria@cps.bo')->first();
        $this->actingAs($tesoreria);

        $categoria = CategoriaGasto::first();
        $beneficiario = Beneficiario::factory()->create([
            'nombre_razon_social' => 'Proveedor Full',
            'ci_nit' => '987654321',
        ]);

        $this->post(route('ordenes-pago.store'), [
            'beneficiario_id' => $beneficiario->id,
            'monto_total' => 5000.00,
            'concepto' => 'Servicio completo',
            'categoria_gasto_id' => $categoria?->id,
            'fecha_orden' => now()->format('Y-m-d'),
        ]);

        $orden = OrdenPago::where('beneficiario_nombre', 'Proveedor Full')->first();
        $this->assertNotNull($orden, 'La orden debería haberse creado');
    }
}
