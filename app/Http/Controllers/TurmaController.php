<?php

namespace App\Http\Controllers;

use App\Models\Turma;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TurmaController extends Controller
{
    public function index()
    {
        return response()->json(Turma::orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome'               => ['required','string','max:255', 'unique:turmas,nome'],
            'representante'      => ['required','string','max:255'],
            'quantidade_alunos'  => ['required','integer','min:1'],
        ], [
            'nome.required'              => 'O nome da turma é obrigatório.',
            'nome.string'                => 'O nome da turma deve ser um texto.',
            'nome.max'                   => 'O nome da turma pode ter no máximo 255 caracteres.',
            'nome.unique'                => 'Já existe uma turma com esse nome.',
            'representante.required'     => 'O representante é obrigatório.',
            'representante.string'       => 'O representante deve ser um texto.',
            'representante.max'          => 'O representante pode ter no máximo 255 caracteres.',
            'quantidade_alunos.required' => 'A quantidade de alunos é obrigatória.',
            'quantidade_alunos.integer'  => 'A quantidade de alunos deve ser um número inteiro.',
            'quantidade_alunos.min'      => 'A quantidade de alunos deve ser no mínimo 1.',
        ]);

        $turma = Turma::create($dados);

        return response()->json($turma, 201);
    }

    public function update(Request $request, Turma $turma)
    {
        // normaliza
        if ($request->filled('nome')) {
            $request->merge(['nome' => trim((string) $request->input('nome'))]);
        }

        $dados = $request->validate([
            'nome'               => [
                'required','string','max:255',
                Rule::unique('turmas','nome')->ignore($turma->id) // não acusa conflito com ela mesma
            ],
            'representante'      => ['required','string','max:255'],
            'quantidade_alunos'  => ['required','integer','min:1'],
        ], [
            'nome.required'              => 'O nome da turma é obrigatório.',
            'nome.string'                => 'O nome da turma deve ser um texto.',
            'nome.max'                   => 'O nome da turma pode ter no máximo 255 caracteres.',
            'nome.unique'                => 'Já existe uma turma com esse nome.',
            'representante.required'     => 'O representante é obrigatório.',
            'representante.string'       => 'O representante deve ser um texto.',
            'representante.max'          => 'O representante pode ter no máximo 255 caracteres.',
            'quantidade_alunos.required' => 'A quantidade de alunos é obrigatória.',
            'quantidade_alunos.integer'  => 'A quantidade de alunos deve ser um número inteiro.',
            'quantidade_alunos.min'      => 'A quantidade de alunos deve ser no mínimo 1.',
        ]);

        $turma->update($dados);

        return response()->json($turma);
    }

    public function destroy(Turma $turma)
    {
        $turma->delete();
        return response()->json(null, 204);
    }
}
