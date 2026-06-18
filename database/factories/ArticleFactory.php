<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $initial = fake()->numberBetween(0, 100);

        return [
            'reference' => strtoupper(fake()->unique()->bothify('??-###')),
            'designation' => fake()->words(3, true),
            'categorie_id' => null,
            'unite' => fake()->randomElement(['pièce', 'carton', 'sac']),
            'suivi_stock' => true,
            'seuil_alerte' => fake()->numberBetween(1, 10),
            'stock_initial' => $initial,
            'stock_actuel' => $initial,
            'actif' => true,
        ];
    }

    /** Article non suivi en stock. */
    public function nonSuivi(): static
    {
        return $this->state(fn () => [
            'suivi_stock' => false,
            'seuil_alerte' => null,
            'stock_initial' => 0,
            'stock_actuel' => 0,
        ]);
    }

    /** Article avec un stock et un seuil précis. */
    public function withStock(int $stock, int $seuil = 5): static
    {
        return $this->state(fn () => [
            'stock_initial' => $stock,
            'stock_actuel' => $stock,
            'seuil_alerte' => $seuil,
        ]);
    }
}
