<?php

namespace App\Http\Controllers;

use App\Models\Turma;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HorariosFeitosController extends Controller
{
    // Lista quem tem registro em horarios_feitos
    public function index(Request $r)
    {
        $q = trim((string) $r->query('q', ''));

        $turmas = Turma::with('horarioFeito:id,turma_id,publicado,publicado_em')
            ->whereHas('horarioFeito')
            ->when($q !== '', fn($w) => $w->where(fn($x) =>
                $x->where('nome', 'like', "%{$q}%")
                    ->orWhere('representante', 'like', "%{$q}%")
            ))
            ->orderBy('nome')
            ->get(['id', 'nome', 'representante']);

        return response()->json($turmas->map(fn($t) => [
            'id'            => $t->id,
            'nome'          => $t->nome,
            'representante' => $t->representante,
            'publicado'     => (bool) optional($t->horarioFeito)->publicado,
            'publicado_em'  => optional($t->horarioFeito)->publicado_em,
        ]));
    }

    // Botão "Salvar" na montagem: cria o registro para aparecer aqui
    public function finalizar(Turma $turma)
    {
        if (!$turma->horarios()->exists()) {
            throw ValidationException::withMessages([
                'turma_id' => 'A turma não possui aulas cadastradas.'
            ]);
        }

        $turma->horarioFeito()->firstOrCreate([]);  // Cria um horário finalizado
        return response()->json(['ok' => true, 'turma_id' => $turma->id]);
    }

    // Reabrir para edição: remove o registro e some da lista
    public function reabrir(Turma $turma)
    {
        optional($turma->horarioFeito)->delete();  // Remove o horário finalizado
        return response()->json(['ok' => true]);
    }

    // Marcar/desmarcar como publicado
    public function publicar(Request $r, Turma $turma)
    {
        $hf = $turma->horarioFeito()->first();
        if (!$hf) {
            return response()->json(['message' => 'Turma não está finalizada.'], 422);
        }

        $valor = filter_var($r->input('publicado', !$hf->publicado), FILTER_VALIDATE_BOOLEAN);
        $hf->publicado = $valor;
        $hf->publicado_em = $valor ? now() : null;
        $hf->save();

        return response()->json([
            'publicado'    => $hf->publicado,
            'publicado_em' => $hf->publicado_em,
        ]);
    }

    // Excluir horário feito: apaga aulas e o registro, volta a rascunho
    public function destruir(Turma $turma)
    {
        Horario::where('turma_id', $turma->id)->delete();
        optional($turma->horarioFeito)->delete();
        return response()->json(null, 204);
    }
}
