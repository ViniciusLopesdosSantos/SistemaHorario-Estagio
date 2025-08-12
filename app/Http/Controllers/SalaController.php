<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalaController extends Controller
{
    public function index()       { return Sala::all(); }
    public function show(Sala $s) { return $s; }

    public function store(Request $req)
    {
        $v = $req->validate([
            'nome'       => 'required|string|unique:salas,nome',
            'capacidade' => 'required|integer|min:1',
        ]);
        return Sala::create($v);
    }

    public function update(Request $req, Sala $s)
    {
        $v = $req->validate([
            'nome'       => ['required','string',Rule::unique('salas','nome')->ignore($s->id_sala)],
            'capacidade' => 'required|integer|min:1',
        ]);
        $s->update($v);
        return $s;
    }

    public function destroy(Sala $s)
    {
        $s->delete();
        return response()->noContent();
    }
}
