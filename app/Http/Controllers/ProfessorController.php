<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfessorController extends Controller
{
    public function index()
    {
        return response()->json(Professor::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:professors,email',
            'senha' => 'required|string|min:6',
        ]);

        $professor = new Professor();
        $professor->nome = $validated['nome'];
        $professor->email = $validated['email'];
        // Hash explícito da senha - sem usar mutator
        $professor->senha = Hash::make($validated['senha']);
        $professor->save();

        return response()->json($professor, 201);
    }

    public function show($id)
    {
        $professor = Professor::findOrFail($id);
        return response()->json($professor);
    }

    public function update(Request $request, $id)
    {
        $professor = Professor::findOrFail($id);
        
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:professors,email,' . $professor->id,
            'senha' => 'nullable|string|min:6',
        ]);

        $professor->nome = $validated['nome'];
        $professor->email = $validated['email'];
        
        // Só atualiza a senha se foi fornecida
        if ($request->has('senha') && !empty($request->senha)) {
            // Hash explícito da senha - sem usar mutator
            $professor->senha = Hash::make($request->senha);
        }
        
        $professor->save();

        return response()->json($professor);
    }

    public function destroy($id)
    {
        $professor = Professor::findOrFail($id);
        $professor->delete();

        return response()->json(null, 204);
    }
}
