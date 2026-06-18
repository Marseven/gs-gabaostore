<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('designation');
            $table->foreignId('categorie_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('unite')->default('pièce');
            $table->boolean('suivi_stock')->default(true);
            $table->integer('seuil_alerte')->nullable();
            $table->integer('stock_initial')->default(0);
            $table->integer('stock_actuel')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index('actif');
            $table->index('suivi_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
