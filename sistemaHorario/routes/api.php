<?php

use App\Http\Controllers\SalaController;
use Illuminate\Support\Facades\Route;

Route::get('/salas', [SalaController::class, 'index']);
Route::post('/salas', [SalaController::class, 'store']);
Route::get('/salas/{sala}', [SalaController::class, 'show']);
Route::put('/salas/{sala}', [SalaController::class, 'update']);
Route::delete('/salas/{sala}', [SalaController::class, 'destroy']);


