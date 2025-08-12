<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    public function index()
    {
        return Professor::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome'  => 'required|string|max:255',
            'email' => 'required|email|unique:professors,email',
            'senha' => 'required|string|min:6',
        ]);

        return Professor::create($validated);
    }

    public function show($id)
    {
        return Professor::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $professor = Professor::findOrFail($id);

        $validated = $request->validate([
            'nome'  => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:professors,email,'.$id,
            'senha' => 'nullable|string|min:6',
        ]);

        // NÃO hashear aqui; deixe o mutator do model fazer isso
        $professor->update($validated);

        return $professor;
    }

    public function destroy($id)
    {
        return Professor::destroy($id);
    }
}
