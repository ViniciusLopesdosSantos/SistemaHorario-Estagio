<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Professor extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'professors';        // usa a tabela 'professors'
    protected $fillable = ['nome', 'email', 'senha'];
    protected $hidden   = ['senha'];        // não retornar hash em JSON

    // sempre criptografa a senha
    public function setSenhaAttribute($value)
    {
        $this->attributes['senha'] = \Hash::make($value);
    }
}
