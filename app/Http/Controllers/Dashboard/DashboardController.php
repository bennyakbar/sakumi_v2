<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StudentObligation;
use App\Models\Transaction;
use App\Services\ReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    public function index(): View
    {
        $today = now()->toDateString();

        $todayIncome = Transaction::where('status', 'completed')
            ->where('type', 'income')
            ->whereDate('transaction_date', $today)
            ->sum('total_amount');

        $monthIncome = Transaction::where('status', 'completed')
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('total_amount');

        $totalArrears = StudentObligation::where('is_paid', false)
            ->sum('amount');

        $recentTransactions = Transaction::with(['student.schoolClass', 'creator'])
            ->where('status', 'completed')
            ->latest('transaction_date')
            ->limit(10)
            ->get();

        $chartData = $this->reportService->getChartData(6);

        return view('dashboard', compact(
            'todayIncome',
            'monthIncome',
            'totalArrears',
            'recentTransactions',
            'chartData',
        ));
    }
}
