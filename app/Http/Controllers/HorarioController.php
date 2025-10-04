<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Professor;
use App\Models\Sala;
use App\Models\Turma;
use App\Models\UnidadeCurricular;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HorarioController extends Controller
{
    // Lista geral
    public function index()
    {
        $horarios = Horario::with(['turma','professor','sala','uc'])->get()
            ->map(function ($h) {
                if ($h->uc) {
                    $h->uc_nome   = $h->uc_nome   ?: ($h->uc->uc ?? $h->uc->nome ?? null);
                    $h->uc_codigo = $h->uc_codigo ?: ($h->uc->codigo_uc ?? $h->uc->codigo ?? null);
                    if (property_exists($h->uc,'grupo')) {
                        $h->uc_grupo = $h->uc_grupo ?: $h->uc->grupo;
                    }
                }
                return $h;
            });

        return response()->json($horarios);
    }

    // Lista por turma
    public function horariosPorTurma($turmaId)
    {
        $horarios = Horario::with(['turma','professor','sala','uc'])
            ->where('turma_id', $turmaId)
            ->orderBy('dia_semana')->orderBy('hora_inicio')
            ->get()
            ->map(function ($h) {
                if ($h->uc) {
                    $h->uc_nome   = $h->uc_nome   ?: ($h->uc->uc ?? $h->uc->nome ?? null);
                    $h->uc_codigo = $h->uc_codigo ?: ($h->uc->codigo_uc ?? $h->uc->codigo ?? null);
                    if (property_exists($h->uc,'grupo')) {
                        $h->uc_grupo = $h->uc_grupo ?: $h->uc->grupo;
                    }
                }
                return $h;
            });

        return response()->json($horarios);
    }

    // Validação completa (store/update)
    private function validateHorario(Request $request, $horarioId = null): array
    {
        $validated = $request->validate([
            'turma_id'       => 'required|exists:turmas,id',
            'professor_id'   => 'required|exists:professors,id',
            'sala_id'        => 'required|exists:salas,id_sala',
            'uc_id'          => 'required|exists:unidades_curriculares,id',
            'dia_semana'     => 'required|string|in:Segunda,Terça,Quarta,Quinta,Sexta',
            'hora_inicio'    => 'required|date_format:H:i',
            'hora_fim'       => 'required|date_format:H:i|after:hora_inicio',
            'classroom_link' => 'nullable|string|max:255',
        ]);

        $sala       = Sala::findOrFail($validated['sala_id']);
        $turma      = Turma::findOrFail($validated['turma_id']);
        $professor  = Professor::findOrFail($validated['professor_id']);
        $uc         = UnidadeCurricular::findOrFail($validated['uc_id']);

        // Preenche campos legados
        $validated['uc_nome']   = $uc->uc ?? $uc->nome ?? null;
        $validated['uc_codigo'] = $uc->codigo_uc ?? $uc->codigo ?? null;
        if (property_exists($uc, 'grupo')) {
            $validated['uc_grupo'] = $uc->grupo;
        }

        // Conflitos na mesma sala (interseção de intervalos)
        $horariosNaMesmaSala = Horario::where('sala_id', $validated['sala_id'])
            ->where('dia_semana', $validated['dia_semana'])
            ->where('hora_inicio', '<', $validated['hora_fim'])
            ->where('hora_fim', '>', $validated['hora_inicio'])
            ->when($horarioId, fn ($q) => $q->where('id', '!=', $horarioId))
            ->get();

        // Capacidade: soma das turmas já nessa sala + turma atual
        $alunosJaNaSala = Turma::whereIn('id', $horariosNaMesmaSala->pluck('turma_id'))
            ->sum('quantidade_alunos');
        $totalProposto = (int)$alunosJaNaSala + (int)($turma->quantidade_alunos ?? 0);

        if ($sala->capacidade !== null && $totalProposto > (int)$sala->capacidade) {
            throw ValidationException::withMessages([
                'sala_id' => "Capacidade da sala ({$sala->capacidade}) seria excedida com {$totalProposto} alunos."
            ]);
        }

        // Turma já ocupada no slot
        $turmaOcupada = Horario::where('turma_id', $validated['turma_id'])
            ->where('dia_semana', $validated['dia_semana'])
            ->where('hora_inicio', '<', $validated['hora_fim'])
            ->where('hora_fim', '>', $validated['hora_inicio'])
            ->when($horarioId, fn ($q) => $q->where('id', '!=', $horarioId))
            ->exists();

        if ($turmaOcupada) {
            throw ValidationException::withMessages([
                'turma_id' => 'A turma já possui aula nesse intervalo.'
            ]);
        }

        // Conflitos do professor no slot
        $profConflitos = Horario::where('professor_id', $validated['professor_id'])
            ->where('dia_semana', $validated['dia_semana'])
            ->where('hora_inicio', '<', $validated['hora_fim'])
            ->where('hora_fim', '>', $validated['hora_inicio'])
            ->when($horarioId, fn ($q) => $q->where('id', '!=', $horarioId))
            ->get();

        if ($profConflitos->isNotEmpty()) {
            // Permite “cross-list” apenas se TODOS os conflitos forem mesma sala + mesma UC
            $podeCrossList = $profConflitos->every(function (Horario $h) use ($validated) {
                $mesmaSala = (string)$h->sala_id === (string)$validated['sala_id'];
                $mesmaUc   = ($h->uc_id === $validated['uc_id'])
                    || (
                        is_null($h->uc_id) &&
                        isset($validated['uc_nome'], $validated['uc_codigo']) &&
                        $h->uc_nome === $validated['uc_nome'] &&
                        $h->uc_codigo === $validated['uc_codigo']
                    );
                return $mesmaSala && $mesmaUc;
            });

            if (!$podeCrossList) {
                throw ValidationException::withMessages([
                    'professor_id' => "O professor(a) {$professor->nome} já possui aula diferente nesse intervalo."
                ]);
            }
            // Se pode cross-list, a checagem de capacidade já garantiu que cabe.
        }

        return $validated;
    }

    // Criar
    public function store(Request $request)
    {
        $data = $this->validateHorario($request);
        $horario = Horario::create($data);
        $horario->load(['turma','professor','sala','uc']);
        return response()->json($horario, 201);
    }

    // Atualizar
    public function update(Request $request, Horario $horario)
    {
        $data = $this->validateHorario($request, $horario->id);
        $horario->update($data);
        $horario->load(['turma','professor','sala','uc']);
        return response()->json($horario);
    }

    // Excluir
    public function destroy(Horario $horario)
    {
        $horario->delete();
        return response()->json(null, 204);
    }
}
