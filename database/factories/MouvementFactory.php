<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Mouvement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mouvement>
 */
class MouvementFactory extends Factory
{
    protected $model = Mouvement::class;

    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'type' => 'entree',
            'quantite' => fake()->numberBetween(1, 20),
            'date_mouvement' => now()->toDateString(),
            'source' => fake()->company(),
            'user_id' => User::factory(),
        ];
    }

    public function sortie(): static
    {
        return $this->state(fn () => [
            'type' => 'sortie',
            'source' => null,
            'livreur' => fake()->name(),
            'destination' => fake()->city(),
        ]);
    }
}
