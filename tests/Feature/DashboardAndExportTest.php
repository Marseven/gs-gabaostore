<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAndExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_reports_alerts(): void
    {
        $user = User::factory()->create();
        Article::factory()->withStock(2, 5)->create();  // en alerte (2 <= 5)
        Article::factory()->withStock(50, 5)->create();  // ok

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('stats.articles_suivis', 2)
            ->assertJsonPath('stats.en_alerte', 1);
    }

    public function test_alertes_endpoint_lists_low_stock(): void
    {
        $user = User::factory()->create();
        Article::factory()->withStock(1, 5)->create(['designation' => 'Bas']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/stock/alertes')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_export_mouvements_returns_xlsx(): void
    {
        $user = User::factory()->create();

        $res = $this->actingAs($user, 'sanctum')->get('/api/v1/export/mouvements');

        $res->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml',
            $res->headers->get('content-type')
        );
    }

    public function test_export_stock_returns_xlsx(): void
    {
        $user = User::factory()->create();
        Article::factory()->withStock(10)->create();

        $res = $this->actingAs($user, 'sanctum')->get('/api/v1/export/stock');

        $res->assertOk();
        $this->assertStringContainsString('spreadsheetml', $res->headers->get('content-type'));
    }
}
