<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Calculators\NetWorthCalculator;
use App\Http\Controllers\Controller;
use App\Models\NetWorthSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    public function __construct(private readonly NetWorthCalculator $netWorth) {}

    public function netWorthEvolution(Request $request): JsonResponse
    {
        $user = $request->user();

        $snapshots = NetWorthSnapshot::where('user_id', $user->id)
            ->orderBy('captured_on')
            ->get(['captured_on', 'total_assets']);

        // Append current (in-progress) month estimate
        $currentTotal = $this->netWorth->total($user);

        $history = $snapshots->map(fn ($s) => [
            'date' => $s->captured_on->toDateString(),
            'value' => (string) $s->total_assets,
            'is_projection' => false,
        ])->all();

        $history[] = [
            'date' => now()->endOfMonth()->toDateString(),
            'value' => $currentTotal,
            'is_projection' => false,
            'is_current' => true,
        ];

        // Linear projection until target_date
        $projection = [];
        if ($user->target_net_worth && $user->target_date) {
            $monthlyDelta = $this->estimateMonthlyDelta($snapshots, (float) $currentTotal);
            $monthsRemaining = max(0, (int) round(now()->diffInMonths($user->target_date, false)));

            for ($i = 1; $i <= $monthsRemaining; $i++) {
                $projection[] = [
                    'date' => now()->addMonths($i)->endOfMonth()->toDateString(),
                    'value' => number_format((float) $currentTotal + $monthlyDelta * $i, 2, '.', ''),
                    'is_projection' => true,
                ];
            }
        }

        return response()->json([
            'data' => [
                'history' => $history,
                'projection' => $projection,
                'target' => $user->target_net_worth ? (string) $user->target_net_worth : null,
                'target_date' => $user->target_date?->toDateString(),
            ],
        ]);
    }

    public function incomeVsExpenses(Request $request): JsonResponse
    {
        $months = max(1, min(36, (int) $request->query('months', 12)));
        $start = now()->subMonths($months - 1)->startOfMonth()->toDateString();

        $rows = DB::table('transactions')
            ->where('user_id', $request->user()->id)
            ->whereNull('deleted_at')
            ->where('out_of_scope', false)
            ->whereDate('occurred_on', '>=', $start)
            ->selectRaw("TO_CHAR(occurred_on, 'YYYY-MM') as month, direction, SUM(amount) as total")
            ->groupBy('month', 'direction')
            ->orderBy('month')
            ->get();

        $byMonth = [];
        foreach ($rows as $row) {
            $byMonth[$row->month] ??= ['month' => $row->month, 'income' => '0.00', 'expenses' => '0.00'];
            $key = $row->direction === 'in' ? 'income' : 'expenses';
            $byMonth[$row->month][$key] = number_format((float) $row->total, 2, '.', '');
        }

        return response()->json(['data' => array_values($byMonth)]);
    }

    public function categoryDistribution(Request $request): JsonResponse
    {
        $period = $request->query('period', 'current_month');
        [$start, $end] = match ($period) {
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'last_3m' => [now()->subMonths(3)->startOfMonth(), now()->endOfMonth()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $rows = DB::table('transactions')
            ->where('transactions.user_id', $request->user()->id)
            ->whereNull('transactions.deleted_at')
            ->where('transactions.out_of_scope', false)
            ->where('transactions.direction', 'out')
            ->whereBetween('transactions.occurred_on', [$start->toDateString(), $end->toDateString()])
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw("
                COALESCE(categories.id, 0) as category_id,
                COALESCE(categories.name, 'Sem categoria') as category_name,
                COALESCE(categories.color, '#94a3b8') as color,
                SUM(transactions.amount) as total
            ")
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderByDesc('total')
            ->get();

        $sum = $rows->sum(fn ($r) => (float) $r->total);

        return response()->json([
            'data' => $rows->map(fn ($r) => [
                'category_id' => (int) $r->category_id ?: null,
                'name' => $r->category_name,
                'color' => $r->color,
                'amount' => number_format((float) $r->total, 2, '.', ''),
                'pct' => $sum > 0 ? round(((float) $r->total / $sum) * 100, 1) : 0,
            ])->all(),
        ]);
    }

    public function dayOfWeekHeatmap(Request $request): JsonResponse
    {
        $months = max(1, min(12, (int) $request->query('months', 3)));
        $start = now()->subMonths($months)->startOfMonth()->toDateString();

        $rows = DB::table('transactions')
            ->where('user_id', $request->user()->id)
            ->whereNull('deleted_at')
            ->where('out_of_scope', false)
            ->where('direction', 'out')
            ->whereDate('occurred_on', '>=', $start)
            ->selectRaw("EXTRACT(DOW FROM occurred_on)::int as dow, SUM(amount) as total, COUNT(*) as count")
            ->groupBy('dow')
            ->orderBy('dow')
            ->get();

        $names = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

        $byDow = collect($rows)->keyBy('dow');
        $data = [];
        for ($i = 0; $i <= 6; $i++) {
            $row = $byDow->get($i);
            $data[] = [
                'dow' => $i,
                'name' => $names[$i],
                'amount' => number_format((float) ($row->total ?? 0), 2, '.', ''),
                'count' => (int) ($row->count ?? 0),
            ];
        }

        return response()->json(['data' => $data]);
    }

    private function estimateMonthlyDelta($snapshots, float $currentTotal): float
    {
        if ($snapshots->count() < 2) {
            return 0;
        }
        $oldest = (float) $snapshots->first()->total_assets;
        $monthsCovered = max(1, (int) $snapshots->count());
        return ($currentTotal - $oldest) / $monthsCovered;
    }
}
