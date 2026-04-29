<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Services\DefaultCategoriesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_cannot_list(): void
    {
        $this->getJson('/api/v1/categories')->assertUnauthorized();
    }

    public function test_lists_only_active_for_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Category::factory()->count(3)->for($user)->create();
        Category::factory()->for($user)->archived()->create();
        Category::factory()->for($other)->create();

        $response = $this->actingAs($user)->getJson('/api/v1/categories');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    public function test_filter_by_kind(): void
    {
        $user = User::factory()->create();
        Category::factory()->count(2)->for($user)->income()->create();
        Category::factory()->count(3)->for($user)->expense()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/categories?kind=income');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        foreach ($response->json('data') as $category) {
            $this->assertSame('income', $category['kind']);
        }
    }

    public function test_filter_archived_returns_only_archived(): void
    {
        $user = User::factory()->create();
        Category::factory()->count(2)->for($user)->create();
        Category::factory()->for($user)->archived()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/categories?archived=true');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_invalid_kind_filter_returns_422(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/categories?kind=invalid')
            ->assertStatus(422);
    }

    public function test_can_create_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/categories', [
            'name' => 'Mercado',
            'kind' => 'expense',
            'color' => '#10b981',
            'icon' => 'shopping-cart',
            'is_essential' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Mercado')
            ->assertJsonPath('data.kind', 'expense')
            ->assertJsonPath('data.is_essential', true);

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Mercado',
        ]);
    }

    public function test_create_validates_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/v1/categories', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'kind', 'color', 'icon']);
    }

    public function test_create_validates_unique_name_per_user(): void
    {
        $user = User::factory()->create();
        Category::factory()->for($user)->create(['name' => 'Lazer']);

        $this->actingAs($user)
            ->postJson('/api/v1/categories', [
                'name' => 'Lazer',
                'kind' => 'expense',
                'color' => '#a855f7',
                'icon' => 'gamepad',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_unique_name_scoped_to_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        Category::factory()->for($userA)->create(['name' => 'Lazer']);

        $this->actingAs($userB)
            ->postJson('/api/v1/categories', [
                'name' => 'Lazer',
                'kind' => 'expense',
                'color' => '#a855f7',
                'icon' => 'gamepad',
            ])
            ->assertCreated();
    }

    public function test_can_update_own(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create(['name' => 'Old']);

        $this->actingAs($user)
            ->patchJson("/api/v1/categories/{$category->id}", ['name' => 'New'])
            ->assertOk()
            ->assertJsonPath('data.name', 'New');
    }

    public function test_cannot_update_others(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $category = Category::factory()->for($other)->create();

        $this->actingAs($user)
            ->patchJson("/api/v1/categories/{$category->id}", ['name' => 'Hijack'])
            ->assertForbidden();
    }

    public function test_archive_soft_archives(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)
            ->deleteJson("/api/v1/categories/{$category->id}")
            ->assertNoContent();

        $this->assertNotNull($category->fresh()->archived_at);
    }

    public function test_seed_creates_defaults(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/categories/seed');

        $response->assertOk();
        $expectedCreated = count(DefaultCategoriesService::DEFAULTS);
        $this->assertSame($expectedCreated, $response->json('data.created'));
        $this->assertSame($expectedCreated, $user->categories()->count());
    }

    public function test_seed_is_idempotent(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/v1/categories/seed')->assertOk();
        $countAfterFirst = $user->categories()->count();

        $response = $this->actingAs($user)->postJson('/api/v1/categories/seed');
        $response->assertOk()
            ->assertJsonPath('data.created', 0);

        $this->assertSame($countAfterFirst, $user->categories()->count());
    }
}
