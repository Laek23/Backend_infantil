<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Reward;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_normalizes_email_case_and_whitespace(): void
    {
        User::create([
            'name' => 'Existing Player',
            'email' => 'player@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $this->postJson('/api/auth/login', [
            'email' => '  PLAYER@EXAMPLE.COM  ',
            'password' => 'correct-password',
        ])->assertOk()->assertJsonStructure(['user' => ['id'], 'token']);
    }

    public function test_database_seeder_creates_starter_subjects_and_activities(): void
    {
        $this->seed();

        $this->assertSame(3, Subject::count());
        $this->assertSame(30, Activity::count());
    }

    public function test_checkout_reports_missing_production_configuration(): void
    {
        config([
            'services.stripe.secret' => null,
            'services.frontend_url' => null,
        ]);

        $user = User::create([
            'name' => 'Player',
            'email' => 'player@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/billing/checkout')
            ->assertStatus(503)
            ->assertJsonPath('message', 'Falta configurar en Render: STRIPE_SECRET, FRONTEND_URL.')
            ->assertJsonPath('missing', ['STRIPE_SECRET', 'FRONTEND_URL']);
    }

    public function test_admin_can_review_players_manage_subscriptions_and_rewards(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'admin-password',
            'role' => 'admin',
        ]);
        $player = User::create([
            'name' => 'Player',
            'email' => 'player@example.com',
            'password' => 'player-password',
        ]);

        $this->actingAs($player, 'sanctum')->getJson('/api/admin/players')->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/players')->assertOk()->assertJsonPath('players.0.id', $player->id);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/players/{$player->id}/subscription", ['active' => true])
            ->assertOk();
        $this->assertSame(14, $player->fresh()->lives);
        $this->assertNotNull($player->fresh()->premium_until);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/rewards', [
                'name' => 'Estrella',
                'category' => 'aura',
                'asset' => 'stars',
                'description' => 'Brillo espacial',
                'unlock_xp' => 25,
            ])->assertCreated();

        $this->assertSame(1, Reward::count());
    }
}