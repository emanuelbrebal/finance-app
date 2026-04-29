<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_cannot_access(): void
    {
        $this->getJson('/api/v1/profile')->assertUnauthorized();
        $this->patchJson('/api/v1/profile')->assertUnauthorized();
    }

    public function test_get_profile_returns_user_data(): void
    {
        $user = User::factory()->create(['name' => 'Manu', 'email' => 'manu@test.dev']);

        $this->actingAs($user)
            ->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.name', 'Manu')
            ->assertJsonPath('data.email', 'manu@test.dev')
            ->assertJsonMissing(['password']);
    }

    public function test_update_name_and_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patchJson('/api/v1/profile', [
                'name'  => 'Novo Nome',
                'email' => 'novo@email.dev',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Novo Nome')
            ->assertJsonPath('data.email', 'novo@email.dev');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Novo Nome']);
    }

    public function test_update_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patchJson('/api/v1/profile', [
                'password'              => 'novasenha123',
                'password_confirmation' => 'novasenha123',
            ])
            ->assertOk();

        // Should be able to authenticate with the new password
        $this->assertTrue(
            auth()->attempt(['email' => $user->email, 'password' => 'novasenha123'])
        );
    }

    public function test_email_must_be_unique_across_users(): void
    {
        $other = User::factory()->create(['email' => 'taken@email.dev']);
        $user  = User::factory()->create();

        $this->actingAs($user)
            ->patchJson('/api/v1/profile', ['email' => 'taken@email.dev'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_keep_same_email(): void
    {
        $user = User::factory()->create(['email' => 'mine@email.dev']);

        $this->actingAs($user)
            ->patchJson('/api/v1/profile', ['email' => 'mine@email.dev'])
            ->assertOk();
    }

    public function test_password_confirmation_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patchJson('/api/v1/profile', ['password' => 'novasenha123'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_update_financial_goals(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patchJson('/api/v1/profile', [
                'target_net_worth'         => '100000.00',
                'estimated_monthly_income' => '8000.00',
                'target_date'              => now()->addYears(2)->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('data.target_net_worth', '100000.00')
            ->assertJsonPath('data.estimated_monthly_income', '8000.00');
    }
}
