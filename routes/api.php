<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\UnidadeCurricularController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\HorariosFeitosController; // <= novo
use App\Http\Controllers\PublicHorariosController;

Route::get('/public/turmas-publicadas', [PublicHorariosController::class, 'turmasPublicadas']);
Route::get('/public/horarios/{turma}',   [PublicHorariosController::class, 'gradeTurma']);

Route::post('/login', [AuthController::class, 'login']);

// Todas as rotas a partir daqui requerem autenticação via Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    // Logout (só acessível para usuários autenticados)
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // CRUD de Professores
    Route::apiResource('professores', ProfessorController::class);

    // CRUD de Salas
    Route::apiResource('salas', SalaController::class);
    
    // CRUD de Turmas
    Route::apiResource('turmas', TurmaController::class);

    // CRUD de Unidades Curriculares
    Route::apiResource('unidades-curriculares', UnidadeCurricularController::class);

    // CRUD de Horários
    Route::apiResource('horarios', HorarioController::class);
    Route::get('horarios/turma/{turmaId}', [HorarioController::class, 'horariosPorTurma']);

    // Horários Feitos (com ações como "finalizar", "reabrir", "publicar", "excluir")
    Route::get('/horarios-feitos', [HorariosFeitosController::class, 'index']);
    Route::post('/turmas/{turma}/horario/finalizar', [HorariosFeitosController::class, 'finalizar']);
    Route::post('/turmas/{turma}/horario/reabrir',    [HorariosFeitosController::class, 'reabrir']);
    Route::patch('/turmas/{turma}/publicar',          [HorariosFeitosController::class, 'publicar']);
    Route::delete('/turmas/{turma}/horario',          [HorariosFeitosController::class, 'destruir']);
});

