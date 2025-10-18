<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turma extends Model
{
    use HasFactory;

    protected $fillable = ['nome','representante','quantidade_alunos'];
    protected $casts = ['quantidade_alunos' => 'integer'];

    // RELAÇÕES
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'turma_id');
    }

    public function horarioFeito()
    {
        return $this->hasOne(HorarioFeito::class);
    }
}
