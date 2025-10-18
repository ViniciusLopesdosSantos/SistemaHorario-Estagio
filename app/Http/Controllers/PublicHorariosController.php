<?php
namespace App\Http\Controllers;
use App\Models\Turma;
use App\Models\Horario;
class PublicHorariosController extends Controller
{
public function turmasPublicadas()
{
// Recupera todas as turmas que têm horários publicados
$turmas = Turma::whereHas('horarioFeito', fn($q) => $q->where('publicado', true))
->orderBy('nome')
->get(['id', 'nome']);
// Retorna as turmas publicadas
return response()->json($turmas);
}
public function gradeTurma(Turma $turma)
{
    $pub = $turma->horarioFeito()->where('publicado', true)->exists();
    abort_unless($pub, 404, 'Horário não publicado.');

    $itens = Horario::with([
        'professor:id,nome',
        'sala:id_sala,nome',
        'uc:id,uc,codigo_uc,grupo',
    ])
    ->where('turma_id', $turma->id)
    ->orderBy('dia_semana')
    ->orderBy('hora_inicio')
    ->get()
    ->map(fn($h) => [
    'dia_semana' => $h->dia_semana,
    'hora_inicio' => is_string($h->hora_inicio) ? $h->hora_inicio : $h->hora_inicio->format('H:i'),
    'hora_fim' => is_string($h->hora_fim) ? $h->hora_fim : $h->hora_fim->format('H:i'),
    'uc' => $h->uc?->uc,
    'codigo_uc' => $h->uc?->codigo_uc,
    'grupo' => $h->uc?->grupo,
    'professor' => $h->professor?->nome,
    'sala' => $h->sala?->nome,
    'classroom' => $h->classroom_link,
    ]);

    return response()->json([
        'turma' => ['id' => $turma->id, 'nome' => $turma->nome],
        'horarios' => $itens,
    ]);
}
}