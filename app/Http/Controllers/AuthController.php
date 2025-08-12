<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/login
     * Faz login e retorna um token Sanctum.
     */
    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'senha' => 'required|string',
        'remember' => 'sometimes|boolean',
    ]);

    $professor = Professor::where('email', $request->email)->first();

    if (! $professor || ! \Hash::check($request->senha, $professor->senha)) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => ['Credenciais inválidas.'],
        ]);
    }

    // opcional: revoga tokens antigos
    $professor->tokens()->delete();

    $remember = $request->boolean('remember');
    // expiração: 30 dias se lembrar, 8h se não
    $expiresAt = $remember ? now()->addDays(30) : now()->addHours(8);

    // Laravel Sanctum 3+ aceita o terceiro parâmetro expiresAt
    $token = $professor->createToken('api-token', ['*'], $expiresAt)->plainTextToken;

    return response()->json([
        'professor' => $professor,
        'token' => $token,
    ]);
}

}
