<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Compte admin initial (Khal)
        User::updateOrCreate(
            ['email' => 'khal@gabaostore.ga'],
            [
                'name' => 'Khal',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'actif' => true,
            ]
        );

        // Un opérateur de démonstration
        User::updateOrCreate(
            ['email' => 'operateur@gabaostore.ga'],
            [
                'name' => 'Opérateur Démo',
                'password' => Hash::make('password'),
                'role' => 'operateur',
                'actif' => true,
            ]
        );

        // Catégories de base
        $categories = collect(['Matériel électrique', 'Plomberie', 'Quincaillerie', 'Consommables'])
            ->mapWithKeys(fn ($nom) => [$nom => Category::firstOrCreate(['nom' => $nom])->id]);

        // Quelques articles d'exemple
        $articles = [
            ['reference' => 'EL-001', 'designation' => 'Câble électrique 2.5mm (rouleau)', 'categorie' => 'Matériel électrique', 'unite' => 'rouleau', 'prix' => 15000, 'suivi_stock' => true, 'seuil_alerte' => 5, 'stock_initial' => 20],
            ['reference' => 'EL-002', 'designation' => 'Disjoncteur 16A', 'categorie' => 'Matériel électrique', 'unite' => 'pièce', 'prix' => 4500, 'suivi_stock' => true, 'seuil_alerte' => 10, 'stock_initial' => 8],
            ['reference' => 'PL-001', 'designation' => 'Tuyau PVC 100mm', 'categorie' => 'Plomberie', 'unite' => 'barre', 'prix' => 8000, 'suivi_stock' => true, 'seuil_alerte' => 15, 'stock_initial' => 50],
            ['reference' => 'QU-001', 'designation' => 'Vis 4x40 (boîte 100)', 'categorie' => 'Quincaillerie', 'unite' => 'boîte', 'prix' => 2500, 'suivi_stock' => true, 'seuil_alerte' => 3, 'stock_initial' => 12],
            ['reference' => 'CO-001', 'designation' => 'Gants de protection', 'categorie' => 'Consommables', 'unite' => 'paire', 'prix' => 1500, 'suivi_stock' => false, 'seuil_alerte' => null, 'stock_initial' => 0],
        ];

        foreach ($articles as $a) {
            Article::firstOrCreate(
                ['reference' => $a['reference']],
                [
                    'designation' => $a['designation'],
                    'categorie_id' => $categories[$a['categorie']],
                    'unite' => $a['unite'],
                    'prix' => $a['prix'],
                    'suivi_stock' => $a['suivi_stock'],
                    'seuil_alerte' => $a['seuil_alerte'],
                    'stock_initial' => $a['stock_initial'],
                    'stock_actuel' => $a['stock_initial'],
                    'actif' => true,
                ]
            );
        }
    }
}
