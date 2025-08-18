<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\TurmaController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Rota de login
Route::middleware([EnsureFrontendRequestsAreStateful::class])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Rotas protegidas por Sanctum, exigem autenticação
Route::middleware('auth:sanctum')->group(function () {
    // CRUD de professores
    Route::apiResource('professores', ProfessorController::class);

    // CRUD de salas
    Route::apiResource('salas', SalaController::class);

    // CRUD de turmas
    Route::apiResource('turmas', TurmaController::class);
});
