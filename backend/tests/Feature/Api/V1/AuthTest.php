<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_and_returns_201(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Manu',
            'email' => 'manu@finance.local',
            'password' => 'segredo123',
            'password_confirmation' => 'segredo123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'manu@finance.local')
            ->assertJsonPath('data.name', 'Manu')
            ->assertJsonPath('data.timezone', 'America/Sao_Paulo')
            ->assertJsonMissingPath('data.password');

        $this->assertDatabaseHas('users', ['email' => 'manu@finance.local']);
    }

    public function test_register_rejects_short_password(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Manu',
            'email' => 'manu@finance.local',
            'password' => '123',
            'password_confirmation' => '123',
        ])->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@finance.local']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Manu',
            'email' => 'taken@finance.local',
            'password' => 'segredo123',
            'password_confirmation' => 'segredo123',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_login_returns_user_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'manu@finance.local',
            'password' => 'segredo123',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'manu@finance.local',
            'password' => 'segredo123',
        ])->assertOk()
            ->assertJsonPath('data.email', 'manu@finance.local');
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'manu@finance.local',
            'password' => 'segredo123',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'manu@finance.local',
            'password' => 'wrong',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_me_rejects_unauthenticated(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_logout_clears_session(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this
            ->postJson('/api/v1/auth/logout')
            ->assertNoContent();
    }

    public function test_logout_rejects_unauthenticated(): void
    {
        $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
    }
}
