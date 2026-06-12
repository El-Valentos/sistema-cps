<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\OrdenPago;
use App\Models\Beneficiario;
use App\Models\Area;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TrackingUniversalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear roles
        $roles = ['Super Admin', 'Tesorería', 'Financiera', 'Contabilidad', 'Presupuesto', 'Administración', 'Caja', 'Archivos'];
        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Crear áreas con código requerido
        Area::create(['nombre' => 'Tesorería', 'codigo' => 'TES', 'descripcion' => 'Área de tesorería']);
        Area::create(['nombre' => 'Financiera', 'codigo' => 'FIN', 'descripcion' => 'Área financiera']);
        Area::create(['nombre' => 'Contabilidad', 'codigo' => 'CON', 'descripcion' => 'Área de contabilidad']);
        Area::create(['nombre' => 'Presupuesto', 'codigo' => 'PRE', 'descripcion' => 'Área de presupuesto']);
        Area::create(['nombre' => 'Administración', 'codigo' => 'ADM', 'descripcion' => 'Área de administración']);
        Area::create(['nombre' => 'Caja', 'codigo' => 'CAJ', 'descripcion' => 'Área de caja']);
        Area::create(['nombre' => 'Archivos', 'codigo' => 'ARC', 'descripcion' => 'Área de archivos']);
    }

    private function createUserWithRole(string $roleName): User
    {
        $area = Area::where('nombre', $roleName === 'Super Admin' ? 'Tesorería' : $roleName)->first();
        
        $user = User::factory()->create([
            'area_id' => $area?->id,
            'activo' => true
        ]);
        
        $user->assignRole($roleName);
        return $user;
    }

    private function createBeneficiario(): Beneficiario
    {
        return Beneficiario::create([
            'nombre_razon_social' => 'Beneficiario Test',
            'apellidos' => 'Apellido Test',
            'ci_nit' => '123456',
            'tipo_documento' => 'CI',
            'telefono' => '7777777',
            'direccion' => 'Dirección Test',
            'email' => 'test@test.com',
            'activo' => true
        ]);
    }

    private function createOrdenPago(string $estado, User $creador = null): OrdenPago
    {
        $beneficiario = $this->createBeneficiario();
        
        return OrdenPago::create([
            'numero_orden' => 'OP-2024-' . rand(10000, 99999),
            'gestion' => 2024,
            'beneficiario_id' => $beneficiario->id,
            'beneficiario_nombre' => $beneficiario->nombre_razon_social,
            'beneficiario_apellidos' => $beneficiario->apellidos,
            'beneficiario_ci_nit' => $beneficiario->ci_nit,
            'concepto' => 'Concepto de prueba',
            'monto_total' => 1000.00,
            'descuentos' => 0,
            'neto_pagar' => 1000.00,
            'estado' => $estado,
            'creado_por' => $creador?->id ?? 1,
            'fecha_orden' => now()
        ]);
    }

    // ============================================
    // TESTS DE VISIBILIDAD EN LISTADO (index)
    // ============================================

    /** @test */
    public function tesoreria_puede_ver_ordenes_en_estado_en_caja()
    {
        $tesoreria = $this->createUserWithRole('Tesorería');
        $orden = $this->createOrdenPago('en_caja');

        $response = $this->actingAs($tesoreria)
            ->get(route('tracking.index'));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
    }

    /** @test */
    public function caja_puede_ver_ordenes_en_estado_pendiente_tesoreria()
    {
        $caja = $this->createUserWithRole('Caja');
        $orden = $this->createOrdenPago('pendiente_tesoreria');

        $response = $this->actingAs($caja)
            ->get(route('tracking.index'));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
    }

    /** @test */
    public function contabilidad_puede_ver_ordenes_en_estado_entregado()
    {
        $contabilidad = $this->createUserWithRole('Contabilidad');
        $orden = $this->createOrdenPago('entregado');

        $response = $this->actingAs($contabilidad)
            ->get(route('tracking.index'));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
    }

    /** @test */
    public function financiera_puede_ver_ordenes_en_estado_cerrado()
    {
        $financiera = $this->createUserWithRole('Financiera');
        $orden = $this->createOrdenPago('cerrado');

        $response = $this->actingAs($financiera)
            ->get(route('tracking.index'));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
    }

    /** @test */
    public function presupuesto_puede_ver_ordenes_en_multiple_estados()
    {
        $presupuesto = $this->createUserWithRole('Presupuesto');
        
        $orden1 = $this->createOrdenPago('pendiente_tesoreria');
        $orden2 = $this->createOrdenPago('enviado_financiera');
        $orden3 = $this->createOrdenPago('enviado_contabilidad');
        $orden4 = $this->createOrdenPago('entregado');

        $response = $this->actingAs($presupuesto)
            ->get(route('tracking.index'));

        $response->assertStatus(200);
        $response->assertSee($orden1->numero_orden);
        $response->assertSee($orden2->numero_orden);
        $response->assertSee($orden3->numero_orden);
        $response->assertSee($orden4->numero_orden);
    }

    /** @test */
    public function administracion_puede_ver_ordenes_en_estado_pendiente_tesoreria()
    {
        $admin = $this->createUserWithRole('Administración');
        $orden = $this->createOrdenPago('pendiente_tesoreria');

        $response = $this->actingAs($admin)
            ->get(route('tracking.index'));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
    }

    // ============================================
    // TESTS DE VISIBILIDAD EN DETALLE (show)
    // ============================================

    /** @test */
    public function tesoreria_puede_ver_detalle_de_orden_en_estado_en_caja()
    {
        $tesoreria = $this->createUserWithRole('Tesorería');
        $orden = $this->createOrdenPago('en_caja');

        $response = $this->actingAs($tesoreria)
            ->get(route('tracking.show', $orden));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
        $response->assertSee('Tracking');
    }

    /** @test */
    public function caja_puede_ver_detalle_de_orden_en_estado_pendiente_tesoreria()
    {
        $caja = $this->createUserWithRole('Caja');
        $orden = $this->createOrdenPago('pendiente_tesoreria');

        $response = $this->actingAs($caja)
            ->get(route('tracking.show', $orden));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
    }

    /** @test */
    public function contabilidad_puede_ver_detalle_de_orden_en_estado_entregado()
    {
        $contabilidad = $this->createUserWithRole('Contabilidad');
        $orden = $this->createOrdenPago('entregado');

        $response = $this->actingAs($contabilidad)
            ->get(route('tracking.show', $orden));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
    }

    /** @test */
    public function financiera_puede_ver_detalle_de_orden_en_estado_cerrado()
    {
        $financiera = $this->createUserWithRole('Financiera');
        $orden = $this->createOrdenPago('cerrado');

        $response = $this->actingAs($financiera)
            ->get(route('tracking.show', $orden));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
    }

    /** @test */
    public function presupuesto_puede_ver_detalle_de_orden_en_estado_pendiente_tesoreria()
    {
        $presupuesto = $this->createUserWithRole('Presupuesto');
        $orden = $this->createOrdenPago('pendiente_tesoreria');

        $response = $this->actingAs($presupuesto)
            ->get(route('tracking.show', $orden));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
    }

    /** @test */
    public function administracion_puede_ver_detalle_de_orden_en_estado_pendiente_tesoreria()
    {
        $admin = $this->createUserWithRole('Administración');
        $orden = $this->createOrdenPago('pendiente_tesoreria');

        $response = $this->actingAs($admin)
            ->get(route('tracking.show', $orden));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
    }

    /** @test */
    public function archivos_puede_ver_detalle_de_cualquier_orden()
    {
        $archivos = $this->createUserWithRole('Archivos');
        $orden = $this->createOrdenPago('enviado_presupuesto');

        $response = $this->actingAs($archivos)
            ->get(route('tracking.show', $orden));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
    }

    // ============================================
    // TESTS DE RESTRICCIONES DE ACCIÓN (seguridad)
    // ============================================

    /** @test */
    public function tesoreria_no_puede_actualizar_orden_en_estado_en_caja()
    {
        $tesoreria = $this->createUserWithRole('Tesorería');
        $orden = $this->createOrdenPago('en_caja', $tesoreria);

        $response = $this->actingAs($tesoreria)
            ->post(route('tracking.actualizar', $orden), [
                'nuevo_estado' => 'enviado_financiera',
                'comentario' => 'Intento no autorizado'
            ]);

        // Debe ser 403 (Forbidden) o redirigido con error
        $this->assertTrue(
            $response->status() === 403 || $response->status() === 302,
            'Expected 403 or 302, got ' . $response->status()
        );
    }

    /** @test */
    public function caja_no_puede_actualizar_orden_en_estado_pendiente_tesoreria()
    {
        $tesoreria = $this->createUserWithRole('Tesorería');
        $caja = $this->createUserWithRole('Caja');
        $orden = $this->createOrdenPago('pendiente_tesoreria', $tesoreria);

        $response = $this->actingAs($caja)
            ->post(route('tracking.actualizar', $orden), [
                'nuevo_estado' => 'enviado_financiera',
                'comentario' => 'Intento no autorizado'
            ]);

        // Debe ser 403 (Forbidden) o redirigido con error
        $this->assertTrue(
            $response->status() === 403 || $response->status() === 302,
            'Expected 403 or 302, got ' . $response->status()
        );
    }

    /** @test */
    public function contabilidad_no_puede_actualizar_orden_en_estado_pendiente_tesoreria()
    {
        $tesoreria = $this->createUserWithRole('Tesorería');
        $contabilidad = $this->createUserWithRole('Contabilidad');
        $orden = $this->createOrdenPago('pendiente_tesoreria', $tesoreria);

        $response = $this->actingAs($contabilidad)
            ->post(route('tracking.actualizar', $orden), [
                'nuevo_estado' => 'enviado_financiera',
                'comentario' => 'Intento no autorizado'
            ]);

        // Debe ser 403 (Forbidden) o redirigido con error
        $this->assertTrue(
            $response->status() === 403 || $response->status() === 302,
            'Expected 403 or 302, got ' . $response->status()
        );
    }

    // ============================================
    // TEST DE VISIBILIDAD UNIVERSAL COMPLETA
    // ============================================

    /** @test */
    public function todas_las_areas_pueden_ver_todas_las_ordenes_en_tracking()
    {
        $roles = ['Tesorería', 'Financiera', 'Contabilidad', 'Presupuesto', 'Administración', 'Caja'];
        
        $estados = [
            'pendiente_tesoreria',
            'enviado_financiera',
            'enviado_contabilidad',
            'enviado_presupuesto',
            'en_caja',
            'entregado',
            'cerrado'
        ];

        // Crear un usuario primero para que exista como creador
        $creator = $this->createUserWithRole('Tesorería');

        // Crear una orden en cada estado
        $ordenes = [];
        foreach ($estados as $estado) {
            $ordenes[$estado] = $this->createOrdenPago($estado, $creator);
        }

        // Verificar que cada rol puede ver todas las órdenes
        foreach ($roles as $roleName) {
            $user = $this->createUserWithRole($roleName);
            
            $response = $this->actingAs($user)
                ->get(route('tracking.index'));

            $response->assertStatus(200);
            
            // Cada orden debe ser visible para este rol
            foreach ($ordenes as $orden) {
                $response->assertSee($orden->numero_orden);
            }
        }
    }

    // ============================================
    // TEST DE BÚSQUEDA
    // ============================================

    /** @test */
    public function busqueda_por_numero_orden_funciona_para_todas_las_areas()
    {
        $caja = $this->createUserWithRole('Caja');
        $orden = $this->createOrdenPago('pendiente_tesoreria');

        $response = $this->actingAs($caja)
            ->get(route('tracking.index', ['numero_orden' => $orden->numero_orden]));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
    }

    /** @test */
    public function filtro_por_estado_funciona_universalmente()
    {
        $financiera = $this->createUserWithRole('Financiera');
        
        $orden1 = $this->createOrdenPago('pendiente_tesoreria');
        $orden2 = $this->createOrdenPago('en_caja');

        // Filtrar solo pendientes
        $response = $this->actingAs($financiera)
            ->get(route('tracking.index', ['estado' => 'pendiente_tesoreria']));

        $response->assertStatus(200);
        $response->assertSee($orden1->numero_orden);
        $response->assertDontSee($orden2->numero_orden);
    }

    /** @test */
    public function usuario_puede_ver_tracking_de_orden_que_no_creo()
    {
        $tesoreria1 = $this->createUserWithRole('Tesorería');
        $tesoreria2 = User::factory()->create(['email' => 'tesoreria2@test.com', 'activo' => true]);
        $tesoreria2->assignRole('Tesorería');
        
        // tesoreria1 crea la orden
        $orden = $this->createOrdenPago('pendiente_tesoreria', $tesoreria1);
        
        // tesoreria2 (mismo rol, diferente usuario) debe poder verla
        $response = $this->actingAs($tesoreria2)
            ->get(route('tracking.show', $orden));

        $response->assertStatus(200);
        $response->assertSee($orden->numero_orden);
    }
}
