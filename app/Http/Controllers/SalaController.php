<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalaController extends Controller
{
    public function index()
    {
        return response()->json(Sala::all());
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate(
            [
                'nome'       => 'required|string|max:255|unique:salas,nome',
                'capacidade' => 'required|integer|min:1',
            ],
            [
                // mensagens para "nome"
                'nome.required' => 'O nome da sala é obrigatório.',
                'nome.string'   => 'O nome da sala deve ser um texto.',
                'nome.max'      => 'O nome da sala pode ter no máximo :max caracteres.',
                'nome.unique'   => 'Já existe uma sala com esse nome.',

                // mensagens para "capacidade"
                'capacidade.required' => 'A capacidade da sala é obrigatória.',
                'capacidade.integer'  => 'A capacidade deve ser um número inteiro.',
                'capacidade.min'      => 'A capacidade mínima da sala é 1.',
            ],
            [
                // alias dos atributos (usado em mensagens genéricas, se aparecerem)
                'nome'       => 'nome da sala',
                'capacidade' => 'capacidade',
            ]
        );

        // opcional: normalizar o nome
        $validatedData['nome'] = trim($validatedData['nome']);

        $sala = Sala::create($validatedData);

        return response()->json($sala, 201);
    }

    public function update(Request $request, Sala $sala)
    {
        $validatedData = $request->validate(
            [
                'nome' => [
                    'required',
                    'string',
                    'max:255',
                    // ignora a *própria* sala ao validar unicidade do nome
                    Rule::unique('salas')->ignore($sala->id_sala, 'id_sala'),
                ],
                'capacidade' => 'required|integer|min:1',
            ],
            [
                // mensagens para "nome"
                'nome.required' => 'O nome da sala é obrigatório.',
                'nome.string'   => 'O nome da sala deve ser um texto.',
                'nome.max'      => 'O nome da sala pode ter no máximo :max caracteres.',
                'nome.unique'   => 'Já existe uma sala com esse nome.',

                // mensagens para "capacidade"
                'capacidade.required' => 'A capacidade da sala é obrigatória.',
                'capacidade.integer'  => 'A capacidade deve ser um número inteiro.',
                'capacidade.min'      => 'A capacidade mínima da sala é 1.',
            ],
            [
                'nome'       => 'nome da sala',
                'capacidade' => 'capacidade',
            ]
        );

        // opcional: normalizar o nome
        if (isset($validatedData['nome'])) {
            $validatedData['nome'] = trim($validatedData['nome']);
        }

        $sala->update($validatedData);

        return response()->json($sala, 200);
    }

    public function destroy($id)
    {
        $sala = Sala::findOrFail($id);
        $sala->delete();

        return response()->json(null, 204);
    }
}
