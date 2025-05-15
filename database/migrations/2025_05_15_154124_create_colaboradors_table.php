<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('colaboradors', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('telefone')->nullable();
            $table->string('cpf')->unique()->nullable();
            $table->enum('tipo', ['amigo', 'familiar', 'colega', 'outro'])->default('amigo');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        // Tabela pivot para relacionamento muitos-para-muitos entre roteiros e colaboradors
        Schema::create('colaborador_roteiro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained()->onDelete('cascade');
            $table->foreignId('roteiro_id')->constrained()->onDelete('cascade');
            $table->decimal('contribuicao', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colaborador_roteiro');
        Schema::dropIfExists('colaboradors');
    }
};
