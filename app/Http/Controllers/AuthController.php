<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'senha' => 'required|string',
        ]);

        $professor = Professor::where('email', $request->email)->first();

        if (!$professor || !Hash::check($request->senha, $professor->senha)) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $token = $professor->createToken('MeuApp')->plainTextToken;

        return response()->json([
            'professor' => $professor,
            'token' => $token,
        ]);
    }
}