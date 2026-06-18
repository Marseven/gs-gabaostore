<?php

namespace Tests\Unit;

use App\Models\Article;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires purs (sans base de données) de la logique d'alerte stock.
 */
class ArticleStockTest extends TestCase
{
    public function test_en_alerte_when_stock_at_or_below_threshold(): void
    {
        $article = new Article([
            'suivi_stock' => true,
            'seuil_alerte' => 5,
            'stock_actuel' => 5,
        ]);

        $this->assertTrue($article->en_alerte);
    }

    public function test_not_en_alerte_when_stock_above_threshold(): void
    {
        $article = new Article([
            'suivi_stock' => true,
            'seuil_alerte' => 5,
            'stock_actuel' => 6,
        ]);

        $this->assertFalse($article->en_alerte);
    }

    public function test_non_suivi_article_is_never_en_alerte(): void
    {
        $article = new Article([
            'suivi_stock' => false,
            'seuil_alerte' => null,
            'stock_actuel' => 0,
        ]);

        $this->assertFalse($article->en_alerte);
    }

    public function test_no_threshold_means_no_alert(): void
    {
        $article = new Article([
            'suivi_stock' => true,
            'seuil_alerte' => null,
            'stock_actuel' => 0,
        ]);

        $this->assertFalse($article->en_alerte);
    }
}
