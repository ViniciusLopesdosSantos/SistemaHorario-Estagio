<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\ProfessorController;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Salas
    Route::get   ('/salas',        [SalaController::class, 'index']);
    Route::post  ('/salas',        [SalaController::class, 'store']);
    Route::get   ('/salas/{sala}', [SalaController::class, 'show']);
    Route::put   ('/salas/{sala}', [SalaController::class, 'update']);
    Route::delete('/salas/{sala}', [SalaController::class, 'destroy']);

    // Professores
    Route::apiResource('professores', ProfessorController::class);
});
