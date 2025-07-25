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
            $table->string('nome');
            $table->enum('status', ['ativa', 'inativa'])->default('ativa');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rotas');
    }
};
