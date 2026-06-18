<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->enum('type', ['entree', 'sortie']);
            $table->integer('quantite');
            $table->date('date_mouvement');
            $table->string('livreur')->nullable();
            $table->string('destination')->nullable();
            $table->string('source')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index(['article_id', 'date_mouvement']);
            $table->index('type');
            $table->index('livreur');
            $table->index('date_mouvement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements');
    }
};
