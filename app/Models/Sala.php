<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    use HasFactory;

    protected $table = 'salas';
    protected $primaryKey = 'id_sala'; // Chave primária definida corretamente
    public $timestamps = false; // Se não estiver usando timestamps

    protected $fillable = ['nome', 'capacidade'];

    // Retorna a chave primária correta para as rotas
    public function getRouteKeyName()
    {
        return 'id_sala';
    }
}
