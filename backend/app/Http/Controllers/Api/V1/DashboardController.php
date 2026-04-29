<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Calculators\BurnRateCalculator;
use App\Domain\Calculators\NetWorthCalculator;
use App\Domain\Calculators\SavingsRateCalculator;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TransactionResource;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private NetWorthCalculator $netWorth,
        private SavingsRateCalculator $savingsRate,
        private BurnRateCalculator $burnRate,
    ) {}

    public function __invoke(Request $request)
    {
        $user        = $request->user();
        $currentMonth = now()->format('Y-m');

        // ── Core calculations ────────────────────────────────────────────
        $netWorth    = $this->netWorth->calculate($user);
        $monthStats  = $this->savingsRate->forMonth($user, $currentMonth);
        $burn        = $this->burnRate->lastMonths($user, 3);

        // ── Runway (months of expenses covered by net worth) ─────────────
        $burnFloat  = (float) $burn['burn_rate'];
        $runway     = $burnFloat > 0
            ? round((float) $netWorth['total'] / $burnFloat, 1)
            : null;

        // ── Top 5 expense categories this month ──────────────────────────
        $start = now()->startOfMonth()->toDateString();
        $end   = now()->endOfMonth()->toDateString();

        $topExpenses = $user->transactions()
            ->with('category:id,name,color,icon')
            ->whereBetween('occurred_on', [$start, $end])
            ->where('direction', 'out')
            ->where('out_of_scope', false)
            ->selectRaw('category_id, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'category_id'   => $row->category_id,
                'category_name' => $row->category?->name ?? 'Sem categoria',
                'category_color'=> $row->category?->color,
                'total'         => number_format((float) $row->total, 2, '.', ''),
                'count'         => $row->count,
            ]);

        // ── Last 5 transactions ───────────────────────────────────────────
        $recent = $user->transactions()
            ->with(['account:id,name,color', 'category:id,name,color'])
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'net_worth'          => $netWorth['total'],
                'net_worth_by_account' => $netWorth['by_account'],
                'month'              => $currentMonth,
                'income'             => $monthStats['income'],
                'expenses'           => $monthStats['expenses'],
                'saved'              => $monthStats['saved'],
                'savings_rate'       => $monthStats['savings_rate'],
                'burn_rate_3m'       => $burn['burn_rate'],
                'burn_rate_months_sampled' => $burn['months_sampled'],
                'runway_months'      => $runway,
                'top_expenses'       => $topExpenses,
                'recent_transactions' => TransactionResource::collection($recent)->resolve(),
            ],
        ]);
    }
}
