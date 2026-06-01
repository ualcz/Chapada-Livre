<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration consolidada: cria a tabela native_sponsors com estrutura final.
     */
    public function up(): void
    {
        Schema::create('native_sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('Título/Nome do Anunciante');
            $table->string('link')->nullable()->comment('URL de destino');
            $table->string('carousel_image_path')->nullable()->comment('Imagem para o carrossel (Horizontal)');
            $table->string('sidebar_image_path')->nullable()->comment('Imagem para a barra lateral (Vertical/Quadrada)');
            $table->string('card_image_path')->nullable()->comment('Imagem para o card intercalado (Card)');
            $table->integer('frequency')->default(5)->comment('Frequência (1-10)');
            $table->integer('category_id')->nullable()->comment('Categoria Específica (nulo = Global)');
            $table->date('expires_at')->nullable()->comment('Data de Validade');
            $table->boolean('active')->default(1)->comment('Anúncio Ativo?');
            $table->timestamps();

            $table->index('category_id');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('native_sponsors');
    }
};
