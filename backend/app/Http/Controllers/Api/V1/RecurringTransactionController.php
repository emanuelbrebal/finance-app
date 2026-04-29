<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRecurringTransactionRequest;
use App\Http\Requests\V1\UpdateRecurringTransactionRequest;
use App\Http\Resources\V1\RecurringTransactionResource;
use App\Http\Resources\V1\TransactionResource;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecurringTransactionController extends Controller
{
    public function __construct(private readonly RecurringTransactionService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $items = RecurringTransaction::where('user_id', $request->user()->id)
            ->with(['account', 'category'])
            ->orderByDesc('active')
            ->orderBy('description')
            ->get();

        return RecurringTransactionResource::collection($items);
    }

    public function store(StoreRecurringTransactionRequest $request): JsonResponse
    {
        $rt = RecurringTransaction::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'data' => new RecurringTransactionResource($rt->load(['account', 'category'])),
        ], 201);
    }

    public function update(UpdateRecurringTransactionRequest $request, RecurringTransaction $recurringTransaction): RecurringTransactionResource
    {
        $this->authorizeOwner($request, $recurringTransaction);

        $recurringTransaction->update($request->validated());

        return new RecurringTransactionResource($recurringTransaction->fresh()->load(['account', 'category']));
    }

    public function destroy(Request $request, RecurringTransaction $recurringTransaction): JsonResponse
    {
        $this->authorizeOwner($request, $recurringTransaction);

        // Deactivate instead of deleting to preserve history
        $recurringTransaction->update(['active' => false]);

        return response()->json(null, 204);
    }

    public function generateNow(Request $request, RecurringTransaction $recurringTransaction): JsonResponse
    {
        $this->authorizeOwner($request, $recurringTransaction);

        $tx = $this->service->generateForMonth($recurringTransaction);

        if ($tx === null) {
            return response()->json([
                'message' => 'Já gerada para este mês ou inativa.',
            ], 200);
        }

        return response()->json([
            'data' => new TransactionResource($tx),
        ], 201);
    }

    private function authorizeOwner(Request $request, RecurringTransaction $rt): void
    {
        abort_if($rt->user_id !== $request->user()->id, 403);
    }
}
