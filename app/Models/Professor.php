<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Professor extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'professors';
    protected $fillable = ['nome','email','senha'];
    protected $hidden = ['senha','remember_token'];

    public function getAuthPassword()
    {
        return $this->senha;
    }
}
