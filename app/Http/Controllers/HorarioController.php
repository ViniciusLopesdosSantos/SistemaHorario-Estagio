<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Professor;
use App\Models\Sala;
use App\Models\Turma;
use App\Models\UnidadeCurricular;
use App\Models\AtividadeDigital;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HorarioController extends Controller
{
    // Lista geral de horários
    public function index()
    {
        $horarios = Horario::with(['turma', 'professor', 'sala', 'uc'])
            ->orderBy('turma_id')
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get()
            ->map(function ($h) {
                return $this->formatarHorario($h);
            });

        return response()->json($horarios);
    }

    // Lista horários por turma - CORRIGIDO
    public function horariosPorTurma($turmaId)
    {
        $horarios = Horario::with(['turma', 'professor', 'sala', 'uc'])
            ->where('turma_id', $turmaId)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get()
            ->map(function ($h) {
                return $this->formatarHorario($h);
            });

        return response()->json($horarios);
    }

    // Método auxiliar para formatar horário
    private function formatarHorario($h)
    {
        // Preenche campos legados se necessário
        if ($h->uc) {
            $h->uc_nome = $h->uc_nome ?: ($h->uc->uc ?? null);
            $h->uc_codigo = $h->uc_codigo ?: ($h->uc->codigo_uc ?? null);
            $h->uc_grupo = $h->uc_grupo ?: ($h->uc->grupo ?? null);
        }
        
        return [
            'id' => $h->id,
            'turma_id' => $h->turma_id,
            'professor_id' => $h->professor_id,
            'sala_id' => $h->sala_id,
            'uc_id' => $h->uc_id,
            'uc_nome' => $h->uc_nome,
            'uc_grupo' => $h->uc_grupo,
            'uc_codigo' => $h->uc_codigo,
            'dia_semana' => $h->dia_semana,
            'hora_inicio' => $h->hora_inicio,
            'hora_fim' => $h->hora_fim,
            'classroom_link' => $h->classroom_link,
            'professor' => $h->professor ? [
                'id' => $h->professor->id,
                'nome' => $h->professor->nome,
                'email' => $h->professor->email
            ] : null,
            'sala' => $h->sala ? [
                'id_sala' => $h->sala->id_sala,
                'nome' => $h->sala->nome,
                'capacidade' => $h->sala->capacidade
            ] : null,
            'uc' => $h->uc ? [
                'id' => $h->uc->id,
                'uc' => $h->uc->uc,
                'grupo' => $h->uc->grupo,
                'codigo_uc' => $h->uc->codigo_uc
            ] : null,
        ];
    }

    // Validação completa
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

        $sala = Sala::findOrFail($validated['sala_id']);
        $turma = Turma::findOrFail($validated['turma_id']);
        $professor = Professor::findOrFail($validated['professor_id']);
        $uc = UnidadeCurricular::findOrFail($validated['uc_id']);

        // Preenche campos legados
        $validated['uc_nome'] = $uc->uc ?? null;
        $validated['uc_codigo'] = $uc->codigo_uc ?? null;
        $validated['uc_grupo'] = $uc->grupo ?? null;

        // Conflitos na mesma sala
        $horariosNaMesmaSala = Horario::where('sala_id', $validated['sala_id'])
            ->where('dia_semana', $validated['dia_semana'])
            ->where('hora_inicio', '<', $validated['hora_fim'])
            ->where('hora_fim', '>', $validated['hora_inicio'])
            ->when($horarioId, fn($q) => $q->where('id', '!=', $horarioId))
            ->get();

        // Capacidade da sala
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
            ->when($horarioId, fn($q) => $q->where('id', '!=', $horarioId))
            ->exists();

        if ($turmaOcupada) {
            throw ValidationException::withMessages([
                'turma_id' => 'A turma já possui aula nesse intervalo.'
            ]);
        }

        // Conflitos do professor
        $profConflitos = Horario::where('professor_id', $validated['professor_id'])
            ->where('dia_semana', $validated['dia_semana'])
            ->where('hora_inicio', '<', $validated['hora_fim'])
            ->where('hora_fim', '>', $validated['hora_inicio'])
            ->when($horarioId, fn($q) => $q->where('id', '!=', $horarioId))
            ->get();

        if ($profConflitos->isNotEmpty()) {
            $podeCrossList = $profConflitos->every(function (Horario $h) use ($validated) {
                $mesmaSala = (string)$h->sala_id === (string)$validated['sala_id'];
                $mesmaUc = ($h->uc_id === $validated['uc_id']) ||
                    (is_null($h->uc_id) &&
                        isset($validated['uc_nome'], $validated['uc_codigo']) &&
                        $h->uc_nome === $validated['uc_nome'] &&
                        $h->uc_codigo === $validated['uc_codigo']);
                return $mesmaSala && $mesmaUc;
            });

            if (!$podeCrossList) {
                throw ValidationException::withMessages([
                    'professor_id' => "O professor(a) {$professor->nome} já possui aula diferente nesse intervalo."
                ]);
            }
        }

        return $validated;
    }

    // Criar horário
    public function store(Request $request)
    {
        $data = $this->validateHorario($request);
        $horario = Horario::create($data);
        $horario->load(['turma', 'professor', 'sala', 'uc']);
        
        return response()->json($this->formatarHorario($horario), 201);
    }

    // Atualizar horário
    public function update(Request $request, Horario $horario)
    {
        $data = $this->validateHorario($request, $horario->id);
        $horario->update($data);
        $horario->load(['turma', 'professor', 'sala', 'uc']);
        
        return response()->json($this->formatarHorario($horario));
    }

    // Excluir horário
    public function destroy(Horario $horario)
    {
        $horario->delete();
        return response()->json(null, 204);
    }

    // ========== ATIVIDADES DIGITAIS ==========

    public function adicionarAtividadeDigital(Request $request)
    {
        $validated = $request->validate([
            'turma_id' => 'required|exists:turmas,id',
            'uc_nome' => 'required|string|max:255',
        ]);

        $atividade = AtividadeDigital::create($validated);
        return response()->json($atividade, 201);
    }

    public function listarAtividadesDigitais($turmaId)
    {
        $atividades = AtividadeDigital::where('turma_id', $turmaId)
            ->orderBy('uc_nome')
            ->get();
        return response()->json($atividades);
    }

    public function excluirAtividadeDigital($id)
    {
        $atividade = AtividadeDigital::findOrFail($id);
        $atividade->delete();
        return response()->json(null, 204);
    }
}