<?php

namespace App\Http\Controllers;

use App\Models\Beneficiario;
use App\Http\Requests\BeneficiarioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeneficiarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Beneficiario::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre_razon_social', 'like', "%{$search}%")
                  ->orWhere('ci_nit', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $beneficiarios = $query->orderBy('nombre_razon_social')->paginate(15);

        return view('beneficiarios.index', compact('beneficiarios'));
    }

    public function create()
    {
        return view('beneficiarios.create');
    }

    public function store(BeneficiarioRequest $request)
    {
        try {
            DB::beginTransaction();

            $beneficiario = Beneficiario::create($request->validated());

            DB::commit();

            return redirect()->route('beneficiarios.index')
                ->with('success', 'Beneficiario creado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al crear beneficiario: ' . $e->getMessage());
        }
    }

    public function show(Beneficiario $beneficiario)
    {
        $beneficiario->load(['ordenesPago' => function($q) {
            $q->latest()->limit(10);
        }]);

        return view('beneficiarios.show', compact('beneficiario'));
    }

    public function edit(Beneficiario $beneficiario)
    {
        return view('beneficiarios.edit', compact('beneficiario'));
    }

    public function update(BeneficiarioRequest $request, Beneficiario $beneficiario)
    {
        try {
            DB::beginTransaction();

            $beneficiario->update($request->validated());

            DB::commit();

            return redirect()->route('beneficiarios.index')
                ->with('success', 'Beneficiario actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al actualizar beneficiario');
        }
    }

    public function destroy(Beneficiario $beneficiario)
    {
        try {
            // Verificar si tiene órdenes asociadas
            if ($beneficiario->ordenesPago()->count() > 0) {
                return back()->with('error', 'No se puede eliminar un beneficiario con órdenes de pago asociadas');
            }

            $beneficiario->delete();

            return redirect()->route('beneficiarios.index')
                ->with('success', 'Beneficiario eliminado exitosamente');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar beneficiario');
        }
    }

    // API: Buscar beneficiarios (usado en formularios)
    public function buscar(Request $request)
    {
        if ($request->filled('q')) {
            $q = $request->q;
            $beneficiarios = Beneficiario::where(function ($query) use ($q) {
                $query->where('nombre_razon_social', 'like', "%{$q}%")
                    ->orWhere('apellidos', 'like', "%{$q}%")
                    ->orWhere('ci_nit', 'like', "%{$q}%");
            })
                ->limit(10)
                ->get(['id', 'nombre_razon_social', 'apellidos', 'ci_nit', 'telefono', 'direccion']);

            return response()->json($beneficiarios);
        }

        $ciNit = $request->query('ci_nit');
        if (!$ciNit) {
            return response()->json(['found' => false]);
        }

        $beneficiario = Beneficiario::where('ci_nit', $ciNit)->first();

        if ($beneficiario) {
            return response()->json([
                'found' => true,
                'beneficiario' => [
                    'id' => $beneficiario->id,
                    'nombre_razon_social' => $beneficiario->nombre_razon_social,
                    'apellidos' => $beneficiario->apellidos,
                    'ci_nit' => $beneficiario->ci_nit,
                    'telefono' => $beneficiario->telefono,
                    'direccion' => $beneficiario->direccion,
                ]
            ]);
        }

        return response()->json(['found' => false]);
    }
}