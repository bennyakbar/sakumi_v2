<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getDailyReport(string $date): array
    {
        $transactions = Transaction::with('items.feeType', 'student', 'creator')
            ->where('transaction_date', $date)
            ->where('status', 'completed')
            ->orderBy('created_at')
            ->get();

        return [
            'date' => $date,
            'income' => $transactions->where('type', 'income')->sum('total_amount'),
            'expense' => $transactions->where('type', 'expense')->sum('total_amount'),
            'balance' => $transactions->where('type', 'income')->sum('total_amount')
                - $transactions->where('type', 'expense')->sum('total_amount'),
            'transactions' => $transactions,
            'income_by_type' => $transactions->where('type', 'income')
                ->flatMap->items
                ->groupBy('fee_type_id')
                ->map(fn ($group) => [
                    'fee_type' => $group->first()->feeType->name,
                    'total' => $group->sum('amount'),
                    'count' => $group->count(),
                ]),
        ];
    }

    public function getMonthlyReport(int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $transactions = Transaction::where('status', 'completed')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $dailyStats = Transaction::where('status', 'completed')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->select(
                'transaction_date',
                'type',
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('transaction_date', 'type')
            ->orderBy('transaction_date')
            ->get();

        return [
            'month' => $month,
            'year' => $year,
            'income' => $transactions->where('type', 'income')->sum('total_amount'),
            'expense' => $transactions->where('type', 'expense')->sum('total_amount'),
            'balance' => $transactions->where('type', 'income')->sum('total_amount')
                - $transactions->where('type', 'expense')->sum('total_amount'),
            'transaction_count' => $transactions->count(),
            'daily_stats' => $dailyStats,
        ];
    }

    public function getChartData(int $months = 6, bool $consolidated = false): array
    {
        $labels = [];
        $incomeData = [];
        $expenseData = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');

            $incomeQuery = Transaction::where('status', 'completed')
                ->where('type', 'income')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year);

            $expenseQuery = Transaction::where('status', 'completed')
                ->where('type', 'expense')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year);

            if ($consolidated) {
                $incomeQuery->withoutGlobalScope('unit');
                $expenseQuery->withoutGlobalScope('unit');
            }

            $incomeData[] = $incomeQuery->sum('total_amount');
            $expenseData[] = $expenseQuery->sum('total_amount');
        }

        return compact('labels', 'incomeData', 'expenseData');
    }
}
