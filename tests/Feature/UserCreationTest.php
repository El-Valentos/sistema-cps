<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesYPermisosSeeder::class);
        $this->seed(\Database\Seeders\AreasSeeder::class);

        $this->admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@test.bo',
        ]);
        $this->admin->assignRole('Super Admin');
    }

    public function test_super_admin_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.create'));

        $response->assertStatus(200);
    }

    public function test_super_admin_can_create_user_with_role(): void
    {
        $area = Area::first();

        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@cps.bo',
            'password' => 'Segura123!',
            'password_confirmation' => 'Segura123!',
            'cargo' => 'Analista',
            'telefono' => '77712345',
            'area_id' => $area->id,
            'role' => 'Tesorería',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $user = User::where('email', 'nuevo@cps.bo')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->activo);
        $this->assertEquals('Analista', $user->cargo);
        $this->assertEquals('77712345', $user->telefono);
        $this->assertEquals($area->id, $user->area_id);
        $this->assertTrue($user->hasRole('Tesorería'));
    }

    public function test_super_admin_can_create_inactive_user(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Usuario Inactivo',
            'email' => 'inactivo@cps.bo',
            'password' => 'Segura123!',
            'password_confirmation' => 'Segura123!',
            'role' => 'Caja',
            'activo' => '0',
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::where('email', 'inactivo@cps.bo')->first();
        $this->assertFalse($user->activo);
    }

    public function test_creation_fails_without_role(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Sin Rol',
            'email' => 'sinrol@cps.bo',
            'password' => 'Segura123!',
            'password_confirmation' => 'Segura123!',
        ]);

        $response->assertSessionHasErrors('role');
    }

    public function test_creation_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existente@cps.bo']);

        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Duplicado',
            'email' => 'existente@cps.bo',
            'password' => 'Segura123!',
            'password_confirmation' => 'Segura123!',
            'role' => 'Contabilidad',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_creation_fails_with_password_mismatch(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Pass Mismatch',
            'email' => 'passfail@cps.bo',
            'password' => 'Segura123!',
            'password_confirmation' => 'OtraClave!',
            'role' => 'Financiera',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_non_super_admin_cannot_access_create_form(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Caja');

        $response = $this->actingAs($user)->get(route('users.create'));

        $response->assertForbidden();
    }

    public function test_non_super_admin_cannot_store_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Tesorería');

        $response = $this->actingAs($user)->post(route('users.store'), [
            'name' => 'Hacker',
            'email' => 'hacker@cps.bo',
            'password' => 'Segura123!',
            'password_confirmation' => 'Segura123!',
            'role' => 'Super Admin',
        ]);

        $response->assertForbidden();
    }

    public function test_register_route_returns_404(): void
    {
        $this->get('/register')->assertStatus(404);

        $this->post('/register', [
            'name' => 'Test',
            'email' => 'test@cps.bo',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertStatus(405);
    }
}
