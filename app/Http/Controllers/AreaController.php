<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Http\Requests\AreaRequest;

class AreaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $areas = Area::withCount('usuarios')->orderBy('orden_flujo')->get();
        return view('areas.index', compact('areas'));
    }

    public function create()
    {
        return view('areas.create');
    }

    public function store(AreaRequest $request)
    {
        Area::create([
            'nombre' => $request->nombre,
            'codigo' => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'orden_flujo' => $request->orden_flujo,
            'activo' => true,
        ]);

        return redirect()->route('areas.index')->with('success', 'Área creada exitosamente');
    }

    public function edit(Area $area)
    {
        return view('areas.edit', compact('area'));
    }

    public function update(AreaRequest $request, Area $area)
    {
        $area->update([
            'nombre' => $request->nombre,
            'codigo' => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'orden_flujo' => $request->orden_flujo,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('areas.index')->with('success', 'Área actualizada exitosamente');
    }
}
