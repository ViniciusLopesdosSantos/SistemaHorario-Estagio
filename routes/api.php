<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\UnidadeCurricularController;
use App\Http\Controllers\HorarioController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('professores', ProfessorController::class);
    Route::apiResource('salas', SalaController::class);
    Route::apiResource('turmas', TurmaController::class);
    Route::apiResource('unidades-curriculares', UnidadeCurricularController::class);

    Route::apiResource('horarios', HorarioController::class);
    Route::get('horarios/turma/{turmaId}', [HorarioController::class, 'horariosPorTurma']);
});
