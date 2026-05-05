<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Wishlist\CheckpointEvaluator;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreWishlistItemRequest;
use App\Http\Requests\V1\UpdateWishlistItemRequest;
use App\Http\Resources\V1\WishlistItemResource;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class WishlistController extends Controller
{
    public function __construct(private readonly CheckpointEvaluator $evaluator) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = WishlistItem::where('user_id', $request->user()->id)
            ->with('category');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->query('priority')) {
            $query->where('priority', $priority);
        }

        $items = $query->orderByDesc('priority')->orderBy('created_at')->get();

        return WishlistItemResource::collection($items);
    }

    public function store(StoreWishlistItemRequest $request): JsonResponse
    {
        $item = WishlistItem::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'priority' => $request->input('priority', 3),
            'quarantine_days' => $request->input('quarantine_days', 30),
            'status' => WishlistItem::STATUS_WAITING,
        ]);

        return response()->json([
            'data' => new WishlistItemResource($item->load('category')),
        ], 201);
    }

    public function show(Request $request, WishlistItem $wishlist): JsonResponse
    {
        $this->authorizeOwner($request, $wishlist);

        $checkpoints = $this->evaluator->evaluate($wishlist, $request->user());
        $resource = (new WishlistItemResource($wishlist->load('category')))->resolve();
        $resource['checkpoints'] = $checkpoints;

        return response()->json(['data' => $resource]);
    }

    public function update(UpdateWishlistItemRequest $request, WishlistItem $wishlist): WishlistItemResource
    {
        $this->authorizeOwner($request, $wishlist);

        $wishlist->update($request->validated());

        return new WishlistItemResource($wishlist->fresh()->load('category'));
    }

    public function destroy(Request $request, WishlistItem $wishlist): JsonResponse
    {
        $this->authorizeOwner($request, $wishlist);
        $wishlist->delete();
        return response()->json(null, 204);
    }

    public function extendQuarantine(Request $request, WishlistItem $wishlist): WishlistItemResource
    {
        $this->authorizeOwner($request, $wishlist);

        $wishlist->update([
            'quarantine_days' => $wishlist->quarantine_days + 30,
            'last_review_prompt_at' => now(),
        ]);

        return new WishlistItemResource($wishlist->fresh()->load('category'));
    }

    public function abandon(Request $request, WishlistItem $wishlist): WishlistItemResource
    {
        $this->authorizeOwner($request, $wishlist);

        $wishlist->update([
            'status' => WishlistItem::STATUS_ABANDONED,
            'abandoned_at' => now(),
        ]);

        return new WishlistItemResource($wishlist->fresh()->load('category'));
    }

    public function purchase(Request $request, WishlistItem $wishlist): WishlistItemResource
    {
        $this->authorizeOwner($request, $wishlist);

        $request->validate([
            'transaction_id' => [
                'required', 'integer',
                Rule::exists('transactions', 'id')->where('user_id', $request->user()->id),
            ],
        ]);

        $wishlist->update([
            'status' => WishlistItem::STATUS_PURCHASED,
            'purchased_transaction_id' => $request->integer('transaction_id'),
        ]);

        return new WishlistItemResource($wishlist->fresh()->load('category'));
    }

    public function checkPrices(Request $request, WishlistItem $wishlist): JsonResponse
    {
        $this->authorizeOwner($request, $wishlist);

        return response()->json([
            'message' => 'Busca de preços ainda não disponível. Disponível na v1.5 via integração SerpAPI.',
        ], 501);
    }

    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $items = WishlistItem::where('user_id', $userId)
            ->whereIn('status', [WishlistItem::STATUS_WAITING, WishlistItem::STATUS_READY_TO_BUY])
            ->get();

        $total = $items->sum(fn ($i) => (float) $i->target_price);

        $oldest = $items->sortBy('created_at')->first();

        return response()->json([
            'data' => [
                'count_active' => $items->count(),
                'total_target_amount' => number_format($total, 2, '.', ''),
                'oldest_item_days' => $oldest ? (int) $oldest->created_at->diffInDays(now()) : null,
                'ready_to_buy_count' => $items->where('status', WishlistItem::STATUS_READY_TO_BUY)->count(),
            ],
        ]);
    }

    private function authorizeOwner(Request $request, WishlistItem $item): void
    {
        abort_if($item->user_id !== $request->user()->id, 403);
    }
}
