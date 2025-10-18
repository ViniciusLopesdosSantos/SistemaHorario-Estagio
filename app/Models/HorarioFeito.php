<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioFeito extends Model
{
    protected $table = 'horarios_feitos';              // <- IMPORTANTE
    protected $fillable = ['turma_id','publicado','publicado_em'];
    protected $casts = ['publicado'=>'boolean','publicado_em'=>'datetime'];
    public function turma(){ return $this->belongsTo(Turma::class); }
}
