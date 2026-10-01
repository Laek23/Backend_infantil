<?php

namespace Tests\Feature;

use App\Models\Activity;
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
            ->assertJsonPath('message', 'Falta configurar STRIPE_SECRET o FRONTEND_URL en Render.');
    }
}