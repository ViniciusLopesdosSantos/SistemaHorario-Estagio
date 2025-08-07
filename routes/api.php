<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\ProfessorController;

// Rotas de API para Vue.js
Route::get('/salas', [SalaController::class, 'index']);
Route::post('/salas', [SalaController::class, 'store']);
Route::get('/salas/{sala}', [SalaController::class, 'show']);
Route::put('/salas/{sala}', [SalaController::class, 'update']);
Route::delete('/salas/{sala}', [SalaController::class, 'destroy']);

Route::apiResource('professores', ProfessorController::class);
