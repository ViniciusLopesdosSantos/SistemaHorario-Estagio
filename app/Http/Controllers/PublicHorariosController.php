<?php

namespace App\Http\Controllers;

use App\Models\Turma;
use App\Models\Horario;
use App\Models\AtividadeDigital;

class PublicHorariosController extends Controller
{
    public function turmasPublicadas()
    {
        $turmas = Turma::whereHas('horarioFeito', fn($q) => $q->where('publicado', true))
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return response()->json($turmas);
    }

    public function gradeTurma(Turma $turma)
    {
        // Verifica se o horário está publicado
        $pub = $turma->horarioFeito()->where('publicado', true)->exists();
        
        if (!$pub) {
            return response()->json([
                'message' => 'Horário não publicado.'
            ], 404);
        }

        // Verifica se é turma FLEX
        $isFlex = strtolower($turma->nome) === 'flex';

        // 1. Buscar os horários normais da turma selecionada
        $itens = Horario::with([
            'professor:id,nome',
            'sala:id_sala,nome',
            'uc:id,uc,codigo_uc,grupo',
        ])
        ->where('turma_id', $turma->id)
        ->orderBy('dia_semana')
        ->orderBy('hora_inicio')
        ->get()
        ->map(function($h) {
            $horaInicio = is_string($h->hora_inicio) 
                ? substr($h->hora_inicio, 0, 5) 
                : $h->hora_inicio->format('H:i');
            
            $horaFim = is_string($h->hora_fim) 
                ? substr($h->hora_fim, 0, 5) 
                : $h->hora_fim->format('H:i');

            return [
                'dia_semana'  => $h->dia_semana,
                'hora_inicio' => $horaInicio,
                'hora_fim'    => $horaFim,
                'uc'          => $h->uc_nome ?: ($h->uc?->uc),
                'codigo_uc'   => $h->uc_codigo ?: ($h->uc?->codigo_uc),
                'grupo'       => $h->uc_grupo ?: ($h->uc?->grupo),
                'professor'   => $h->professor?->nome,
                'sala'        => $h->sala?->nome,
                'classroom'   => $h->classroom_link,
            ];
        });

        // 2. Buscar as atividades digitais da turma selecionada
        $atividadesDigitais = AtividadeDigital::where('turma_id', $turma->id)
            ->orderBy('uc_nome')
            ->get()
            ->map(function($ad) {
                $nome = $ad->uc_nome;
                $codigo = '';
                
                if (preg_match('/^([A-Z]{3,4}\d+)/i', $nome, $matches)) {
                    $codigo = strtoupper($matches[1]);
                    $nome = trim(preg_replace('/^([A-Z]{3,4}\d+)\s*-\s*/i', '', $nome));
                } else {
                    $codigo = 'N/A';
                }
                
                return [
                    'codigo_uc' => $codigo,
                    'nome_uc'   => $nome,
                ];
            });

        // 3. **NOVO**: Buscar horários da turma FLEX (para mostrar em todas as turmas)
        $horariosFlex = [];
        
        // Busca a turma FLEX que está publicada
        $turmaFlex = Turma::whereHas('horarioFeito', fn($q) => $q->where('publicado', true))
            ->whereRaw('LOWER(nome) = ?', ['flex'])
            ->first();

        if ($turmaFlex && $turmaFlex->id !== $turma->id) {
            // Se encontrou a turma FLEX e não é a turma atual, busca seus horários
            $horariosFlex = Horario::with([
                'professor:id,nome',
                'sala:id_sala,nome',
                'uc:id,uc,codigo_uc,grupo',
            ])
            ->where('turma_id', $turmaFlex->id)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get()
            ->map(function($h) {
                $horaInicio = is_string($h->hora_inicio) 
                    ? substr($h->hora_inicio, 0, 5) 
                    : $h->hora_inicio->format('H:i');
                
                $horaFim = is_string($h->hora_fim) 
                    ? substr($h->hora_fim, 0, 5) 
                    : $h->hora_fim->format('H:i');

                return [
                    'dia_semana'  => $h->dia_semana,
                    'hora_inicio' => $horaInicio,
                    'hora_fim'    => $horaFim,
                    'uc'          => $h->uc_nome ?: ($h->uc?->uc),
                    'codigo_uc'   => $h->uc_codigo ?: ($h->uc?->codigo_uc),
                    'grupo'       => $h->uc_grupo ?: ($h->uc?->grupo),
                    'professor'   => $h->professor?->nome,
                    'sala'        => $h->sala?->nome,
                    'classroom'   => $h->classroom_link,
                ];
            })
            ->toArray();
        }

        // 4. Retornar todos os dados incluindo horários FLEX
        return response()->json([
            'turma'               => ['id' => $turma->id, 'nome' => $turma->nome],
            'horarios'            => $itens,
            'atividades_digitais' => $atividadesDigitais,
            'horarios_flex'       => $horariosFlex,
            'is_flex'             => $isFlex,
        ]);
    }
}