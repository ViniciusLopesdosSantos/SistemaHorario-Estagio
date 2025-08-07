<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    use HasFactory;

    public $timestamps = false; // <-- Isso aqui

    protected $table = 'salas';
    protected $primaryKey = 'id_sala'; // 👈 isso resolve o problema
    public $incrementing = true;
    protected $fillable = ['nome', 'capacidade'];
}


