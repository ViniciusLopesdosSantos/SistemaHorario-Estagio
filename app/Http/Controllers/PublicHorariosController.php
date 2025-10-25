<?php

namespace App\Http\Controllers;

use App\Models\Turma;
use App\Models\Horario;
use App\Models\AtividadeDigital; // Importar o modelo AtividadeDigital

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

        // 1. Buscar os horários normais
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
            // Normaliza as horas (remove segundos)
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

        // 2. Buscar as atividades digitais
        $atividadesDigitais = AtividadeDigital::where('turma_id', $turma->id)
            ->orderBy('uc_nome')
            ->get()
            ->map(function($ad) {
                $nome = $ad->uc_nome;
                $codigo = '';
                
                // Tenta extrair o código da UC do nome (ex: "MAT123" ou "MAT123 - Nome")
                // Padrão: 3-4 letras seguidas por números, no início da string
                if (preg_match('/^([A-Z]{3,4}\d+)/i', $nome, $matches)) {
                    $codigo = strtoupper($matches[1]);
                    // Se o nome estiver no formato "CODIGO - NOME", remove o código para deixar só o nome
                    $nome = trim(preg_replace('/^([A-Z]{3,4}\d+)\s*-\s*/i', '', $nome));
                } else {
                    // Se não encontrar o padrão, usa o nome completo, e o código fica "N/A"
                    $codigo = 'N/A';
                }
                
                return [
                    'codigo_uc' => $codigo,
                    'nome_uc'   => $nome,
                ];
            });

        // 3. Retornar os horários e as atividades digitais
        return response()->json([
            'turma'    => ['id' => $turma->id, 'nome' => $turma->nome],
            'horarios' => $itens,
            'atividades_digitais' => $atividadesDigitais,
        ]);
    }
}
