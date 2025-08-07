<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Professor extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'email', 'senha'];

    public function setSenhaAttribute($value)
    {
        $this->attributes['senha'] = Hash::make($value);
    }
}
