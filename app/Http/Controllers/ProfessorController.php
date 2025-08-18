<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfessorController extends Controller
{
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
        $professor->senha = Hash::make($validated['senha']);
        $professor->save();

        return response()->json($professor, 201);
    }

    public function update(Request $request, $id)
    {
        $professor = Professor::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:professors,email,' . $professor->id,
            'senha' => 'nullable|string|min:6',
        ]);

        if ($request->has('senha')) {
            $validated['senha'] = Hash::make($request->senha);
        } else {
            unset($validated['senha']);
        }

        $professor->update($validated);

        return response()->json($professor);
    }

    public function destroy($id)
    {
        $professor = Professor::findOrFail($id);
        $professor->delete();

        return response()->json(null, 204);
    }

    public function index()
    {
        return response()->json(Professor::all());
    }
}