<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Area;
use App\Http\Requests\UserRequest;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = User::with(['roles', 'area']);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        $users = $query->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $areas = Area::activas()->get();
        return view('users.create', compact('roles', 'areas'));
    }

    public function store(UserRequest $request)
    {
        try {
            $user = DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                    'cargo' => $request->cargo,
                    'telefono' => $request->telefono,
                    'area_id' => $request->area_id,
                    'activo' => $request->boolean('activo', true),
                ]);

                $user->assignRole($request->role);

                return $user;
            });

            return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al crear el usuario: ' . $e->getMessage());
        }
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $areas = Area::activas()->get();
        return view('users.edit', compact('user', 'roles', 'areas'));
    }

    public function update(UserRequest $request, User $user)
    {
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'cargo' => $request->cargo,
            'telefono' => $request->telefono,
            'area_id' => $request->area_id,
            'activo' => $request->boolean('activo'),
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => $request->password]);
        }

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente');
    }

    public function toggleActivo(User $user)
    {
        $user->update(['activo' => !$user->activo]);
        $status = $user->activo ? 'activado' : 'desactivado';
        return back()->with('success', "Usuario {$status} correctamente");
    }

    public function asignarSuperAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes asignarte el rol Super Admin a ti mismo');
        }

        if ($user->hasRole('Super Admin')) {
            return back()->with('error', 'El usuario ya tiene el rol Super Admin');
        }

        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $user->assignRole('Super Admin');
            return back()->with('success', 'Usuario ahora es Super Admin');
        }
        return back()->with('error', 'Rol Super Admin no encontrado');
    }

    public function quitarSuperAdmin(User $user)
    {
        $user->removeRole('Super Admin');
        return back()->with('success', 'Rol Super Admin eliminado');
    }
}
