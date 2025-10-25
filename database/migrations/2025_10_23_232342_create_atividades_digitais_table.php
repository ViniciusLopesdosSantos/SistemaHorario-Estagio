<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('atividades_digitais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('turma_id');  // CORRIGIDO: turma_id em vez de horario_id
            $table->string('uc_nome');
            $table->timestamps();
            
            $table->foreign('turma_id')->references('id')->on('turmas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('atividades_digitais');
    }
};