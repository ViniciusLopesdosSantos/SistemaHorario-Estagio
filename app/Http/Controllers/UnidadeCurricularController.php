<?php

namespace App\Http\Controllers;

use App\Models\UnidadeCurricular;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnidadeCurricularController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $unidadesCurriculares = UnidadeCurricular::all();
        return response()->json($unidadesCurriculares);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'uc' => 'required|string|max:255',
            'grupo' => 'nullable|string|max:255',
            'codigo_uc' => 'required|string|max:255|unique:unidades_curriculares,codigo_uc',
        ]);

        $unidadeCurricular = UnidadeCurricular::create($validated);
        return response()->json($unidadeCurricular, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unidadeCurricular = UnidadeCurricular::findOrFail($id);
        return response()->json($unidadeCurricular);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $unidadeCurricular = UnidadeCurricular::findOrFail($id);

        $validated = $request->validate([
            'uc' => 'required|string|max:255',
            'grupo' => 'nullable|string|max:255',
            'codigo_uc' => 'required|string|max:255|unique:unidades_curriculares,codigo_uc,' . $id,
        ]);

        $unidadeCurricular->update($validated);
        return response()->json($unidadeCurricular);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unidadeCurricular = UnidadeCurricular::findOrFail($id);
        $unidadeCurricular->delete();
        return response()->json(null, 204);
    }
}
