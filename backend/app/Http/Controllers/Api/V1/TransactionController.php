<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreTransactionRequest;
use App\Http\Requests\V1\UpdateTransactionRequest;
use App\Http\Resources\V1\TransactionResource;
use App\Models\Transaction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = $request->user()->transactions()
            ->with(['account:id,name,color', 'category:id,name,color,kind'])
            ->orderByDesc('occurred_on')
            ->orderByDesc('id');

        if ($from = $request->query('from')) {
            $query->whereDate('occurred_on', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('occurred_on', '<=', $to);
        }
        if ($accountId = $request->query('account_id')) {
            $query->where('account_id', $accountId);
        }
        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($direction = $request->query('direction')) {
            abort_unless(in_array($direction, ['in', 'out'], true), 422);
            $query->where('direction', $direction);
        }
        if ($search = $request->query('search')) {
            $query->where('description', 'ilike', '%'.$search.'%');
        }
        if ($tag = $request->query('tag')) {
            $query->whereJsonContains('tags', $tag);
        }
        if ($request->has('out_of_scope')) {
            $query->where('out_of_scope', $request->boolean('out_of_scope'));
        }

        $perPage = min((int) ($request->query('per_page', 25)), 100);
        $paginated = $query->paginate($perPage);

        return TransactionResource::collection($paginated)
            ->additional(['meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ]]);
    }

    public function store(StoreTransactionRequest $request)
    {
        $data = $request->validated();

        $hash = Transaction::computeHash(
            $data['occurred_on'],
            (string) $data['amount'],
            $data['direction'],
            $data['description'],
            (int) $data['account_id'],
        );

        // Return existing if already imported (idempotent for manual entry too)
        $existing = $request->user()->transactions()->where('dedup_hash', $hash)->first();
        if ($existing) {
            return TransactionResource::make($existing)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        }

        $transaction = $request->user()->transactions()->create([
            ...$data,
            'dedup_hash' => $hash,
        ]);

        return TransactionResource::make($transaction)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        $transaction->load(['account:id,name,color', 'category:id,name,color,kind']);

        return TransactionResource::make($transaction);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $data = $request->validated();

        // Recompute hash only when hash-relevant fields change
        $hashFields = array_intersect_key($data, array_flip([
            'occurred_on', 'amount', 'direction', 'description', 'account_id',
        ]));

        if ($hashFields) {
            $data['dedup_hash'] = Transaction::computeHash(
                $hashFields['occurred_on'] ?? $transaction->occurred_on->toDateString(),
                isset($hashFields['amount']) ? (string) $hashFields['amount'] : (string) $transaction->amount,
                $hashFields['direction'] ?? $transaction->direction,
                $hashFields['description'] ?? $transaction->description,
                isset($hashFields['account_id']) ? (int) $hashFields['account_id'] : $transaction->account_id,
            );
        }

        $transaction->update($data);

        return TransactionResource::make($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        $transaction->delete(); // soft delete

        return response()->noContent();
    }

    public function summary(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d',
            'group_by' => ['nullable', Rule::in(['category', 'account', 'month', 'day_of_week'])],
        ]);

        $query = $request->user()->transactions();

        if ($from = $request->query('from')) {
            $query->whereDate('occurred_on', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('occurred_on', '<=', $to);
        }

        $groupBy = $request->query('group_by', 'category');

        $results = match ($groupBy) {
            'category' => $query->select(
                'category_id',
                'direction',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count'),
            )->groupBy('category_id', 'direction')->with('category:id,name,color,kind')->get(),

            'account' => $query->select(
                'account_id',
                'direction',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count'),
            )->groupBy('account_id', 'direction')->with('account:id,name,color')->get(),

            'month' => $query->select(
                DB::raw("TO_CHAR(occurred_on, 'YYYY-MM') as month"),
                'direction',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count'),
            )->groupBy('month', 'direction')->orderBy('month')->get(),

            'day_of_week' => $query->select(
                DB::raw('EXTRACT(DOW FROM occurred_on)::integer as day_of_week'),
                'direction',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count'),
            )->groupBy('day_of_week', 'direction')->orderBy('day_of_week')->get(),
        };

        return response()->json(['data' => $results]);
    }

    public function bulkCategorize(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $request->user()->id),
            ],
        ]);

        $updated = $request->user()->transactions()
            ->whereIn('id', $request->input('ids'))
            ->update(['category_id' => $request->input('category_id')]);

        return response()->json(['data' => ['updated' => $updated]]);
    }
}
