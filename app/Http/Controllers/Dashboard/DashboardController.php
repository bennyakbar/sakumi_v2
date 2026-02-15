<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StudentObligation;
use App\Models\Transaction;
use App\Models\Unit;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    public function index(Request $request): View
    {
        $scope = $request->input('scope', 'unit');
        $consolidated = $scope === 'all' && auth()->user()->hasRole('super_admin');
        $scope = $consolidated ? 'all' : 'unit';

        $today = now()->toDateString();

        $todayIncomeQuery = Transaction::where('status', 'completed')
            ->where('type', 'income')
            ->whereDate('transaction_date', $today);

        $monthIncomeQuery = Transaction::where('status', 'completed')
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);

        $arrearsQuery = StudentObligation::where('is_paid', false);

        $recentQuery = Transaction::query()
            ->where('status', 'completed')
            ->latest('transaction_date')
            ->limit(10);

        if ($consolidated) {
            $todayIncomeQuery->withoutGlobalScope('unit');
            $monthIncomeQuery->withoutGlobalScope('unit');
            $arrearsQuery->withoutGlobalScope('unit');
            $recentQuery->withoutGlobalScope('unit')->with([
                'unit',
                'student' => fn ($q) => $q->withoutGlobalScope('unit'),
                'student.schoolClass' => fn ($q) => $q->withoutGlobalScope('unit'),
                'creator',
            ]);
        } else {
            $recentQuery->with(['student.schoolClass', 'creator']);
        }

        $todayIncome = $todayIncomeQuery->sum('total_amount');
        $monthIncome = $monthIncomeQuery->sum('total_amount');
        $totalArrears = $arrearsQuery->sum('amount');
        $recentTransactions = $recentQuery->get();

        $chartData = $this->reportService->getChartData(6, $consolidated);

        $unitBreakdown = [];
        if ($consolidated) {
            $units = Unit::where('is_active', true)->get();
            foreach ($units as $unit) {
                $unitBreakdown[] = [
                    'name' => $unit->name,
                    'code' => $unit->code,
                    'today_income' => Transaction::withoutGlobalScope('unit')
                        ->where('unit_id', $unit->id)
                        ->where('status', 'completed')
                        ->where('type', 'income')
                        ->whereDate('transaction_date', $today)
                        ->sum('total_amount'),
                    'month_income' => Transaction::withoutGlobalScope('unit')
                        ->where('unit_id', $unit->id)
                        ->where('status', 'completed')
                        ->where('type', 'income')
                        ->whereMonth('transaction_date', now()->month)
                        ->whereYear('transaction_date', now()->year)
                        ->sum('total_amount'),
                    'arrears' => StudentObligation::withoutGlobalScope('unit')
                        ->where('unit_id', $unit->id)
                        ->where('is_paid', false)
                        ->sum('amount'),
                ];
            }
        }

        return view('dashboard', compact(
            'todayIncome',
            'monthIncome',
            'totalArrears',
            'recentTransactions',
            'chartData',
            'scope',
            'consolidated',
            'unitBreakdown',
        ));
    }
}
