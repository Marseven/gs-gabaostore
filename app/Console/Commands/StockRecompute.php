<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\MouvementService;
use Illuminate\Console\Command;

class StockRecompute extends Command
{
    protected $signature = 'stock:recompute';

    protected $description = 'Recalcule stock_actuel de tous les articles suivis depuis stock_initial + mouvements';

    public function handle(MouvementService $service): int
    {
        $articles = Article::suivis()->get();
        $this->info("Recalcul du stock pour {$articles->count()} article(s) suivi(s)...");

        $bar = $this->output->createProgressBar($articles->count());
        $bar->start();

        foreach ($articles as $article) {
            $avant = $article->stock_actuel;
            $service->recalculer($article);
            if ($avant !== $article->stock_actuel) {
                $this->newLine();
                $this->warn("  {$article->reference} : {$avant} -> {$article->stock_actuel}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Réconciliation terminée.');

        return self::SUCCESS;
    }
}
