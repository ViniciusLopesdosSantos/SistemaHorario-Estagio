<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();

            // Relacionamentos
            $table->unsignedBigInteger('turma_id');
            $table->unsignedBigInteger('professor_id');
            $table->unsignedBigInteger('sala_id');
            $table->unsignedBigInteger('uc_id');

            // Campos legados
            $table->string('uc_nome')->nullable();
            $table->string('uc_grupo')->nullable();
            $table->string('uc_codigo')->nullable();

            // Campos de horário
            $table->string('dia_semana'); // Segunda, Terça, etc
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->string('classroom_link')->nullable();

            $table->timestamps();

            // Definições das chaves estrangeiras
            $table->foreign('turma_id')->references('id')->on('turmas')->onDelete('cascade');
            $table->foreign('professor_id')->references('id')->on('professors')->onDelete('cascade');
            $table->foreign('sala_id')->references('id_sala')->on('salas')->onDelete('cascade');
            $table->foreign('uc_id')->references('id')->on('unidades_curriculares')->onDelete('cascade');

            // Índices para melhorar a performance
            $table->index(['turma_id', 'dia_semana']);
            $table->index(['professor_id', 'dia_semana']);
            $table->index(['sala_id', 'dia_semana']);
            $table->index(['dia_semana','hora_inicio','hora_fim'], 'horarios_dia_horas_idx');

           
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};