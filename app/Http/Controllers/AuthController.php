<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $r)
    {
        $r->validate([
            'email' => 'required|email',
            'password' => 'required_without:senha',
            'senha' => 'nullable'
        ]);

        $plain = $r->input('password', $r->input('senha'));

        $p = Professor::where('email', $r->email)->first();
        if (!$p || !Hash::check($plain, $p->senha)) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $token = $p->createToken('web')->plainTextToken;
        return response()->json([
            'professor' => ['id'=>$p->id,'nome'=>$p->nome,'email'=>$p->email],
            'token' => $token,
        ]);
    }

    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()?->delete();
        return response()->json(['ok'=>true]);
    }
}
