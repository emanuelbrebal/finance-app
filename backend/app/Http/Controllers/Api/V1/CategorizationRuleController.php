<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Categorization\CategorizationRuleApplier;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreCategorizationRuleRequest;
use App\Http\Requests\V1\UpdateCategorizationRuleRequest;
use App\Http\Resources\V1\CategorizationRuleResource;
use App\Models\CategorizationRule;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategorizationRuleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CategorizationRule::where('user_id', $request->user()->id)
            ->with('category')
            ->orderByDesc('priority')
            ->orderByDesc('hits');

        if ($request->has('auto_learned')) {
            $query->where('auto_learned', $request->boolean('auto_learned'));
        }

        return CategorizationRuleResource::collection($query->get());
    }

    public function store(StoreCategorizationRuleRequest $request): JsonResponse
    {
        $rule = CategorizationRule::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'data' => new CategorizationRuleResource($rule->load('category')),
        ], 201);
    }

    public function update(UpdateCategorizationRuleRequest $request, CategorizationRule $categorizationRule): CategorizationRuleResource
    {
        $this->authorizeOwner($request, $categorizationRule);

        $categorizationRule->update($request->validated());

        return new CategorizationRuleResource($categorizationRule->fresh()->load('category'));
    }

    public function destroy(Request $request, CategorizationRule $categorizationRule): JsonResponse
    {
        $this->authorizeOwner($request, $categorizationRule);

        $categorizationRule->delete();

        return response()->json(null, 204);
    }

    public function applyToExisting(Request $request, CategorizationRule $categorizationRule): JsonResponse
    {
        $this->authorizeOwner($request, $categorizationRule);

        $applier = app(CategorizationRuleApplier::class);
        $userId = $request->user()->id;

        $matched = 0;
        Transaction::where('user_id', $userId)
            ->whereNull('category_id')
            ->cursor()
            ->each(function (Transaction $tx) use ($categorizationRule, &$matched) {
                if ($this->matchesRule($tx->description, $categorizationRule)) {
                    $tx->update(['category_id' => $categorizationRule->category_id]);
                    $matched++;
                }
            });

        if ($matched > 0) {
            $categorizationRule->increment('hits', $matched);
        }

        return response()->json(['matched_count' => $matched]);
    }

    private function matchesRule(string $description, CategorizationRule $rule): bool
    {
        $haystack = mb_strtolower($description);
        $needle = mb_strtolower($rule->pattern);

        return match ($rule->match_type) {
            'contains' => str_contains($haystack, $needle),
            'starts_with' => str_starts_with($haystack, $needle),
            'exact' => $haystack === $needle,
            'regex' => (bool) @preg_match("/{$rule->pattern}/i", $description),
            default => false,
        };
    }

    private function authorizeOwner(Request $request, CategorizationRule $rule): void
    {
        abort_if($rule->user_id !== $request->user()->id, 403);
    }
}
