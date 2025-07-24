<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rotas', function (Blueprint $table) {
            $table->id();
            $table->string('origem');
            $table->string('destino');
            $table->integer('distancia'); // em quilômetros
            $table->string('tempo_medio')->nullable(); // ex: "6h 30min"
            $table->decimal('valor_base', 10, 2);
            $table->enum('status', ['ativa', 'inativa'])->default('ativa');
            $table->integer('utilizacoes')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rotas');
    }
};