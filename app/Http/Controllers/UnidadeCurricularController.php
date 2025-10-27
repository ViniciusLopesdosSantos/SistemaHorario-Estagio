<?php

namespace App\Http\Controllers;

use App\Models\UnidadeCurricular;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnidadeCurricularController extends Controller
{
    public function index()
    {
        $unidadesCurriculares = UnidadeCurricular::all();
        return response()->json($unidadesCurriculares);
    }

    public function store(Request $request)
    {
        // Verifica se é UC FLEX
        $nomeUc = trim((string) $request->input('uc', ''));
        $isFlex = strtolower($nomeUc) === 'flex';
        
        $validated = $request->validate([
            'uc' => 'required|string|max:255',
            'grupo' => $isFlex ? 'nullable|string|max:255' : 'nullable|string|max:255',
            'codigo_uc' => $isFlex ? 'nullable|string|max:255' : 'required|string|max:255|unique:unidades_curriculares,codigo_uc',
        ]);
        
        // Define valores padrão para FLEX
        if ($isFlex) {
            $validated['grupo'] = $validated['grupo'] ?? 'N/A';
            $validated['codigo_uc'] = $validated['codigo_uc'] ?? 'FLEX-' . time();
        }

        $unidadeCurricular = UnidadeCurricular::create($validated);
        return response()->json($unidadeCurricular, 201);
    }

    public function show(string $id)
    {
        $unidadeCurricular = UnidadeCurricular::findOrFail($id);
        return response()->json($unidadeCurricular);
    }

    public function update(Request $request, string $id)
    {
        $unidadeCurricular = UnidadeCurricular::findOrFail($id);
        
        // Verifica se é UC FLEX
        $nomeUc = trim((string) $request->input('uc', ''));
        $isFlex = strtolower($nomeUc) === 'flex';
        
        $validated = $request->validate([
            'uc' => 'required|string|max:255',
            'grupo' => $isFlex ? 'nullable|string|max:255' : 'nullable|string|max:255',
            'codigo_uc' => $isFlex 
                ? 'nullable|string|max:255' 
                : 'required|string|max:255|unique:unidades_curriculares,codigo_uc,' . $id,
        ]);
        
        // Define valores padrão para FLEX
        if ($isFlex) {
            $validated['grupo'] = $validated['grupo'] ?? 'N/A';
            if (!isset($validated['codigo_uc']) || empty($validated['codigo_uc'])) {
                $validated['codigo_uc'] = 'FLEX-' . time();
            }
        }

        $unidadeCurricular->update($validated);
        return response()->json($unidadeCurricular);
    }

    public function destroy(string $id)
    {
        $unidadeCurricular = UnidadeCurricular::findOrFail($id);
        $unidadeCurricular->delete();
        return response()->json(null, 204);
    }
}