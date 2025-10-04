<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadeCurricular extends Model
{
    use HasFactory;

    protected $table = 'unidades_curriculares';

    protected $fillable = [
        'uc',
        'grupo',
        'codigo_uc',
    ];

  
}