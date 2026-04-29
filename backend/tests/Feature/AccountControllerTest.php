<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_list_accounts(): void
    {
        $response = $this->getJson('/api/v1/accounts');

        $response->assertUnauthorized();
    }

    public function test_user_can_list_only_own_active_accounts(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Account::factory()->count(2)->for($user)->create();
        Account::factory()->for($user)->archived()->create();
        Account::factory()->for($other)->create();

        $response = $this->actingAs($user)->getJson('/api/v1/accounts');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_user_can_create_account(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'Nubank',
            'type' => 'checking',
            'initial_balance' => '1500.50',
            'color' => '#820AD1',
            'icon' => 'wallet',
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/accounts', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Nubank')
            ->assertJsonPath('data.type', 'checking')
            ->assertJsonPath('data.initial_balance', '1500.50')
            ->assertJsonPath('data.color', '#820AD1')
            ->assertJsonPath('data.currency', 'BRL');

        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'name' => 'Nubank',
        ]);
    }

    public function test_create_account_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/accounts', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'type']);
    }

    public function test_create_account_validates_type_enum(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/accounts', [
            'name' => 'Foo',
            'type' => 'crypto',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    public function test_user_can_show_own_account(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        $response = $this->actingAs($user)->getJson("/api/v1/accounts/{$account->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $account->id);
    }

    public function test_user_cannot_show_others_account(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $account = Account::factory()->for($other)->create();

        $response = $this->actingAs($user)->getJson("/api/v1/accounts/{$account->id}");

        $response->assertForbidden();
    }

    public function test_user_can_update_own_account(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->patchJson("/api/v1/accounts/{$account->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'New Name',
        ]);
    }

    public function test_user_cannot_update_others_account(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $account = Account::factory()->for($other)->create();

        $response = $this->actingAs($user)->patchJson("/api/v1/accounts/{$account->id}", [
            'name' => 'Hijacked',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_archive_own_account(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        $response = $this->actingAs($user)->deleteJson("/api/v1/accounts/{$account->id}");

        $response->assertNoContent();

        $this->assertNotNull($account->fresh()->archived_at);
    }

    public function test_archived_account_does_not_appear_in_listing(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        $this->actingAs($user)->deleteJson("/api/v1/accounts/{$account->id}");

        $response = $this->actingAs($user)->getJson('/api/v1/accounts');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }
}
