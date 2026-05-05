<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Calculators\BurnRateCalculator;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreGoalRequest;
use App\Http\Requests\V1\UpdateGoalRequest;
use App\Http\Resources\V1\GoalResource;
use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GoalController extends Controller
{
    public function __construct(private readonly BurnRateCalculator $burnRate) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $goals = Goal::where('user_id', $request->user()->id)
            ->with('account')
            ->orderByDesc('is_emergency_fund')
            ->orderByDesc('created_at')
            ->get();

        return GoalResource::collection($goals);
    }

    public function store(StoreGoalRequest $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Enforce single emergency fund per user
        if ($request->boolean('is_emergency_fund')) {
            $exists = Goal::where('user_id', $userId)
                ->where('is_emergency_fund', true)
                ->exists();

            abort_if($exists, 422, 'Já existe uma reserva de emergência cadastrada.');
        }

        $goal = Goal::create([
            ...$request->validated(),
            'user_id' => $userId,
        ]);

        return response()->json([
            'data' => new GoalResource($goal->load('account')),
        ], 201);
    }

    public function show(Request $request, Goal $goal): GoalResource
    {
        $this->authorizeOwner($request, $goal);

        return new GoalResource($goal->load('account'));
    }

    public function update(UpdateGoalRequest $request, Goal $goal): GoalResource
    {
        $this->authorizeOwner($request, $goal);

        $goal->update($request->validated());
        $this->maybeMarkAchieved($goal);

        return new GoalResource($goal->fresh()->load('account'));
    }

    public function destroy(Request $request, Goal $goal): JsonResponse
    {
        $this->authorizeOwner($request, $goal);

        $goal->delete();

        return response()->json(null, 204);
    }

    public function deposit(Request $request, Goal $goal): GoalResource
    {
        $this->authorizeOwner($request, $goal);

        $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $goal->increment('current_amount', $request->float('amount'));
        $this->maybeMarkAchieved($goal->fresh());

        return new GoalResource($goal->fresh()->load('account'));
    }

    public function emergencyFund(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $goal = Goal::where('user_id', $userId)
            ->where('is_emergency_fund', true)
            ->with('account')
            ->first();

        if ($goal === null) {
            return response()->json(['data' => null]);
        }

        $burn = $this->burnRate->lastMonths($request->user(), 3);
        $burnRate = (float) ($burn['burn_rate'] ?? 0);
        $coverage = $burnRate > 0 ? round((float) $goal->current_amount / $burnRate, 1) : null;

        return response()->json([
            'data' => array_merge(
                (new GoalResource($goal))->resolve(),
                [
                    'burn_rate_3m' => number_format($burnRate, 2, '.', ''),
                    'coverage_months' => $coverage,
                ],
            ),
        ]);
    }

    public function autoTargetEmergencyFund(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $goal = Goal::where('user_id', $userId)
            ->where('is_emergency_fund', true)
            ->first();

        abort_if($goal === null, 404, 'Reserva de emergência não cadastrada.');

        $burn = $this->burnRate->lastMonths($request->user(), 6);
        $burnRate = (float) ($burn['burn_rate'] ?? 0);
        $newTarget = number_format($burnRate * 6, 2, '.', '');

        $goal->update(['target_amount' => $newTarget]);

        return response()->json([
            'data' => new GoalResource($goal->fresh()->load('account')),
            'computed_from' => [
                'burn_rate_6m' => number_format($burnRate, 2, '.', ''),
                'multiplier' => 6,
            ],
        ]);
    }

    private function maybeMarkAchieved(Goal $goal): void
    {
        if ($goal->current_amount >= $goal->target_amount && $goal->achieved_at === null) {
            $goal->update(['achieved_at' => now()]);
        }
    }

    private function authorizeOwner(Request $request, Goal $goal): void
    {
        abort_if($goal->user_id !== $request->user()->id, 403);
    }
}
