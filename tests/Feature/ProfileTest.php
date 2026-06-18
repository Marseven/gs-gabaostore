<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_own_profile(): void
    {
        $user = User::factory()->create(['name' => 'Ancien', 'email' => 'old@a.com']);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/profile', ['name' => 'Nouveau', 'email' => 'new@a.com'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Nouveau')
            ->assertJsonPath('data.email', 'new@a.com');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'new@a.com']);
    }

    public function test_email_must_be_unique_to_others(): void
    {
        User::factory()->create(['email' => 'taken@a.com']);
        $user = User::factory()->create(['email' => 'me@a.com']);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/profile', ['name' => 'X', 'email' => 'taken@a.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_keeping_same_email_is_allowed(): void
    {
        $user = User::factory()->create(['email' => 'me@a.com', 'name' => 'Moi']);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/profile', ['name' => 'Moi 2', 'email' => 'me@a.com'])
            ->assertOk();
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/profile/password', [
                'current_password' => 'password',
                'password' => 'nouveau-mdp-123',
                'password_confirmation' => 'nouveau-mdp-123',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('nouveau-mdp-123', $user->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/profile/password', [
                'current_password' => 'faux',
                'password' => 'nouveau-mdp-123',
                'password_confirmation' => 'nouveau-mdp-123',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('current_password');
    }

    public function test_profile_routes_require_authentication(): void
    {
        $this->putJson('/api/v1/profile', ['name' => 'X', 'email' => 'x@a.com'])->assertStatus(401);
    }
}
