<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'turma_id',
        'professor_id',
        'sala_id',
        'uc_id',
        'uc_nome',
        'uc_grupo',
        'uc_codigo',
        'dia_semana',
        'hora_inicio',
        'hora_fim',
        'classroom_link',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime:H:i',
        'hora_fim'    => 'datetime:H:i',
    ];

    public function turma()     { return $this->belongsTo(Turma::class, 'turma_id'); }
    public function professor() { return $this->belongsTo(Professor::class, 'professor_id'); }
    public function sala()      { return $this->belongsTo(Sala::class, 'sala_id'); }    // salas.id_sala referenciada na FK da migration
    public function uc()        { return $this->belongsTo(UnidadeCurricular::class, 'uc_id'); }
}
