<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makePayload(Account $account, array $overrides = []): array
    {
        return array_merge([
            'account_id' => $account->id,
            'occurred_on' => '2026-04-01',
            'description' => 'Mercado',
            'amount' => '150.00',
            'direction' => 'out',
        ], $overrides);
    }

    public function test_unauthenticated_cannot_list(): void
    {
        $this->getJson('/api/v1/transactions')->assertUnauthorized();
    }

    public function test_lists_own_transactions_paginated(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        Transaction::factory()->count(3)->for($user)->for($account)->create();
        Transaction::factory()->for(User::factory()->create())->for(Account::factory()->create())->create();

        $response = $this->actingAs($user)->getJson('/api/v1/transactions');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
        $this->assertArrayHasKey('meta', $response->json());
    }

    public function test_can_create_transaction(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        $response = $this->actingAs($user)->postJson('/api/v1/transactions', $this->makePayload($account));

        $response->assertCreated()
            ->assertJsonPath('data.description', 'Mercado')
            ->assertJsonPath('data.amount', '150.00')
            ->assertJsonPath('data.direction', 'out');

        $this->assertNotNull($response->json('data.dedup_hash'));
        $this->assertDatabaseHas('transactions', ['user_id' => $user->id, 'description' => 'Mercado']);
    }

    public function test_duplicate_returns_existing_200(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $payload = $this->makePayload($account);

        $first = $this->actingAs($user)->postJson('/api/v1/transactions', $payload);
        $first->assertCreated();

        $second = $this->actingAs($user)->postJson('/api/v1/transactions', $payload);
        $second->assertOk();
        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/v1/transactions', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['account_id', 'occurred_on', 'description', 'amount', 'direction']);
    }

    public function test_store_validates_account_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $foreignAccount = Account::factory()->for($other)->create();

        $this->actingAs($user)->postJson('/api/v1/transactions', $this->makePayload($foreignAccount))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['account_id']);
    }

    public function test_store_validates_direction_enum(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        $this->actingAs($user)->postJson('/api/v1/transactions', $this->makePayload($account, ['direction' => 'left']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['direction']);
    }

    public function test_store_validates_amount_positive(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        $this->actingAs($user)->postJson('/api/v1/transactions', $this->makePayload($account, ['amount' => '0']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_can_show_own_transaction(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->for($account)->create();

        $this->actingAs($user)->getJson("/api/v1/transactions/{$transaction->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $transaction->id);
    }

    public function test_cannot_show_others_transaction(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $account = Account::factory()->for($other)->create();
        $transaction = Transaction::factory()->for($other)->for($account)->create();

        $this->actingAs($user)->getJson("/api/v1/transactions/{$transaction->id}")
            ->assertForbidden();
    }

    public function test_can_update_own_transaction(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->for($account)->create(['description' => 'Old']);

        $this->actingAs($user)->patchJson("/api/v1/transactions/{$transaction->id}", ['description' => 'New'])
            ->assertOk()
            ->assertJsonPath('data.description', 'New');
    }

    public function test_update_recomputes_dedup_hash(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->for($account)->create();
        $originalHash = $transaction->dedup_hash;

        $this->actingAs($user)->patchJson("/api/v1/transactions/{$transaction->id}", [
            'description' => 'Completely different description',
        ])->assertOk();

        $this->assertNotSame($originalHash, $transaction->fresh()->dedup_hash);
    }

    public function test_can_soft_delete_transaction(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->for($account)->create();

        $this->actingAs($user)->deleteJson("/api/v1/transactions/{$transaction->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
    }

    public function test_soft_deleted_excluded_from_list(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        Transaction::factory()->count(2)->for($user)->for($account)->create();
        $deleted = Transaction::factory()->for($user)->for($account)->create();

        $this->actingAs($user)->deleteJson("/api/v1/transactions/{$deleted->id}");

        $response = $this->actingAs($user)->getJson('/api/v1/transactions');
        $this->assertCount(2, $response->json('data'));
    }

    public function test_filter_by_direction(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        Transaction::factory()->count(2)->for($user)->for($account)->income()->create();
        Transaction::factory()->count(3)->for($user)->for($account)->expense()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/transactions?direction=in');
        $this->assertCount(2, $response->json('data'));

        $response = $this->actingAs($user)->getJson('/api/v1/transactions?direction=out');
        $this->assertCount(3, $response->json('data'));
    }

    public function test_bulk_categorize(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $category = Category::factory()->for($user)->create();
        $transactions = Transaction::factory()->count(3)->for($user)->for($account)->create();

        $response = $this->actingAs($user)->postJson('/api/v1/transactions/bulk-categorize', [
            'ids' => $transactions->pluck('id')->all(),
            'category_id' => $category->id,
        ]);

        $response->assertOk()->assertJsonPath('data.updated', 3);
        foreach ($transactions as $tx) {
            $this->assertSame($category->id, $tx->fresh()->category_id);
        }
    }

    public function test_bulk_categorize_cannot_touch_other_users_transactions(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $otherAccount = Account::factory()->for($other)->create();
        $category = Category::factory()->for($user)->create();
        $otherTransactions = Transaction::factory()->count(2)->for($other)->for($otherAccount)->create();

        $response = $this->actingAs($user)->postJson('/api/v1/transactions/bulk-categorize', [
            'ids' => $otherTransactions->pluck('id')->all(),
            'category_id' => $category->id,
        ]);

        $response->assertOk()->assertJsonPath('data.updated', 0);
        foreach ($otherTransactions as $tx) {
            $this->assertNull($tx->fresh()->category_id);
        }
    }

    public function test_summary_by_month(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        Transaction::factory()->for($user)->for($account)->income()->create([
            'occurred_on' => '2026-01-15',
            'amount' => '1000.00',
        ]);
        Transaction::factory()->for($user)->for($account)->expense()->create([
            'occurred_on' => '2026-02-10',
            'amount' => '300.00',
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/transactions/summary?group_by=month');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }
}
