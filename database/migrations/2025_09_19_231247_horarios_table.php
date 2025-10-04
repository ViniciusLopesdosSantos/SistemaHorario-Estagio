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

            $table->unsignedBigInteger('turma_id');
            $table->unsignedBigInteger('professor_id');
            $table->unsignedBigInteger('sala_id');
            $table->unsignedBigInteger('uc_id');

            // legados
            $table->string('uc_nome')->nullable();
            $table->string('uc_grupo')->nullable();
            $table->string('uc_codigo')->nullable();

            $table->string('dia_semana'); // Segunda..Sexta
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->string('classroom_link')->nullable();

            $table->timestamps();

            // FKs — alinhe ao seu schema
            $table->foreign('turma_id')
                ->references('id')->on('turmas')
                ->onDelete('cascade');

            // AQUI O AJUSTE: tabela 'professors'
            $table->foreign('professor_id')
                ->references('id')->on('professors')
                ->onDelete('cascade');

            // salas usa PK id_sala (confirme o tipo bigInteger)
            $table->foreign('sala_id')
                ->references('id_sala')->on('salas')
                ->onDelete('cascade');

            $table->foreign('uc_id')
                ->references('id')->on('unidades_curriculares')
                ->onDelete('cascade');

            // Índices
            $table->index(['turma_id', 'dia_semana']);
            $table->index(['professor_id', 'dia_semana']);
            $table->index(['sala_id', 'dia_semana']);
            $table->index(['dia_semana','hora_inicio','hora_fim'], 'horarios_dia_horas_idx');

            // Unicidade por turma+slot
            $table->unique(['turma_id','dia_semana','hora_inicio','hora_fim'], 'horarios_turma_slot_uk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
