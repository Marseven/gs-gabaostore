<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->decimal('prix', 12, 2)->nullable()->after('unite');
        });

        Schema::table('mouvements', function (Blueprint $table) {
            $table->decimal('prix', 12, 2)->nullable()->after('quantite');
            $table->string('telephone')->nullable()->after('destination');
            $table->string('vendeur')->nullable()->after('livreur');
            // Mode de remise d'une sortie : 'livraison' (livreur) ou 'sur_place' (reçu par)
            $table->string('mode_remise')->default('livraison')->after('vendeur');
            $table->string('recu_par')->nullable()->after('mode_remise');
            // Statut de livraison : 'valide' | 'rate' | 'a_reprogrammer'
            $table->string('statut_livraison')->nullable()->after('recu_par');
            $table->text('commentaire_statut')->nullable()->after('statut_livraison');

            $table->index('statut_livraison');
            $table->index('vendeur');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('prix');
        });

        Schema::table('mouvements', function (Blueprint $table) {
            $table->dropIndex(['statut_livraison']);
            $table->dropIndex(['vendeur']);
            $table->dropColumn([
                'prix', 'telephone', 'vendeur', 'mode_remise',
                'recu_par', 'statut_livraison', 'commentaire_statut',
            ]);
        });
    }
};
