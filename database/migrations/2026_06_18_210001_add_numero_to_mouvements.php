<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mouvements', function (Blueprint $table) {
            // Numéro de commande / vente (champ libre).
            $table->string('numero')->nullable()->after('prix');
            $table->index('numero');
        });
    }

    public function down(): void
    {
        Schema::table('mouvements', function (Blueprint $table) {
            $table->dropIndex(['numero']);
            $table->dropColumn('numero');
        });
    }
};
