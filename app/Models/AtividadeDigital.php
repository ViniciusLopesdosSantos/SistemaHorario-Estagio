<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtividadeDigital extends Model
{
    use HasFactory;

    protected $table = 'atividades_digitais';
    protected $fillable = ['turma_id', 'uc_nome'];

    public function turma()
    {
        return $this->belongsTo(Turma::class);
    }
}