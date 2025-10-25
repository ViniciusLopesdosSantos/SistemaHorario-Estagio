<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\UnidadeCurricularController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\HorariosFeitosController;
use App\Http\Controllers\PublicHorariosController;

// Rotas públicas (SEM autenticação)
Route::post('/login', [AuthController::class, 'login']);
Route::prefix('public')->group(function () {
    Route::get('/turmas-publicadas', [PublicHorariosController::class, 'turmasPublicadas']);
    Route::get('/horarios/{turma}', [PublicHorariosController::class, 'gradeTurma']);
});

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // IMPORTANTE: Rotas específicas ANTES das resource
    Route::get('/horarios/turma/{turmaId}', [HorarioController::class, 'horariosPorTurma']);
    Route::post('/horarios/atividade-digital', [HorarioController::class, 'adicionarAtividadeDigital']);
    Route::get('/horarios/atividades-digitais/{turmaId}', [HorarioController::class, 'listarAtividadesDigitais']);
    Route::delete('/horarios/atividade-digital/{id}', [HorarioController::class, 'excluirAtividadeDigital']);
    
    // Resource routes
    Route::apiResource('salas', SalaController::class);
    Route::apiResource('professores', ProfessorController::class);
    Route::apiResource('turmas', TurmaController::class);
    Route::apiResource('unidades-curriculares', UnidadeCurricularController::class);
    Route::apiResource('horarios', HorarioController::class);

    // Horários Feitos
    Route::get('/horarios-feitos', [HorariosFeitosController::class, 'index']);
    Route::post('/turmas/{turma}/horario/finalizar', [HorariosFeitosController::class, 'finalizar']);
    Route::post('/turmas/{turma}/horario/reabrir', [HorariosFeitosController::class, 'reabrir']);
    Route::patch('/turmas/{turma}/publicar', [HorariosFeitosController::class, 'publicar']);
    Route::delete('/turmas/{turma}/horario', [HorariosFeitosController::class, 'destruir']);
});