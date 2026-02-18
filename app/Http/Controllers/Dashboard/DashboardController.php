<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Settlement;
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

        $todayDirectIncomeQuery = Transaction::where('status', 'completed')
            ->where('type', 'income')
            ->whereNull('student_id')
            ->whereDate('transaction_date', $today);

        $monthDirectIncomeQuery = Transaction::where('status', 'completed')
            ->where('type', 'income')
            ->whereNull('student_id')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);

        $todayExpenseQuery = Transaction::where('status', 'completed')
            ->where('type', 'expense')
            ->whereDate('transaction_date', $today);

        $monthExpenseQuery = Transaction::where('status', 'completed')
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);

        $todaySettlementQuery = Settlement::where('status', 'completed')
            ->whereDate('payment_date', $today);

        $monthSettlementQuery = Settlement::where('status', 'completed')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year);

        $arrearsQuery = Invoice::query()
            ->whereDate('due_date', '<', $today)
            ->whereRaw(
                "(invoices.total_amount - COALESCE((SELECT SUM(sa.amount) FROM settlement_allocations sa INNER JOIN settlements s ON s.id = sa.settlement_id WHERE sa.invoice_id = invoices.id AND s.status = 'completed'), 0)) > 0"
            )
            ->selectRaw(
                "(invoices.total_amount - COALESCE((SELECT SUM(sa.amount) FROM settlement_allocations sa INNER JOIN settlements s ON s.id = sa.settlement_id WHERE sa.invoice_id = invoices.id AND s.status = 'completed'), 0)) as outstanding_amount"
            );

        $recentQuery = Transaction::query()
            ->where('status', 'completed')
            ->latest('transaction_date')
            ->limit(10);

        if ($consolidated) {
            $todayDirectIncomeQuery->withoutGlobalScope('unit');
            $monthDirectIncomeQuery->withoutGlobalScope('unit');
            $todayExpenseQuery->withoutGlobalScope('unit');
            $monthExpenseQuery->withoutGlobalScope('unit');
            $todaySettlementQuery->withoutGlobalScope('unit');
            $monthSettlementQuery->withoutGlobalScope('unit');
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

        $todayIncome = $todaySettlementQuery->sum('allocated_amount')
            + $todayDirectIncomeQuery->sum('total_amount')
            - $todayExpenseQuery->sum('total_amount');
        $monthIncome = $monthSettlementQuery->sum('allocated_amount')
            + $monthDirectIncomeQuery->sum('total_amount')
            - $monthExpenseQuery->sum('total_amount');
        $totalArrears = (float) $arrearsQuery->get()->sum('outstanding_amount');
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
                        ->where('status', 'completed')
                        ->where('type', 'income')
                        ->whereNull('student_id')
                        ->where('unit_id', $unit->id)
                        ->whereDate('transaction_date', $today)
                        ->sum('total_amount')
                        + Settlement::withoutGlobalScope('unit')
                        ->where('status', 'completed')
                        ->where('unit_id', $unit->id)
                        ->whereDate('payment_date', $today)
                        ->sum('allocated_amount')
                        - Transaction::withoutGlobalScope('unit')
                        ->where('status', 'completed')
                        ->where('type', 'expense')
                        ->where('unit_id', $unit->id)
                        ->whereDate('transaction_date', $today)
                        ->sum('total_amount'),
                    'month_income' => Transaction::withoutGlobalScope('unit')
                        ->where('status', 'completed')
                        ->where('type', 'income')
                        ->whereNull('student_id')
                        ->where('unit_id', $unit->id)
                        ->whereMonth('transaction_date', now()->month)
                        ->whereYear('transaction_date', now()->year)
                        ->sum('total_amount')
                        + Settlement::withoutGlobalScope('unit')
                        ->where('status', 'completed')
                        ->where('unit_id', $unit->id)
                        ->whereMonth('payment_date', now()->month)
                        ->whereYear('payment_date', now()->year)
                        ->sum('allocated_amount')
                        - Transaction::withoutGlobalScope('unit')
                        ->where('status', 'completed')
                        ->where('type', 'expense')
                        ->where('unit_id', $unit->id)
                        ->whereMonth('transaction_date', now()->month)
                        ->whereYear('transaction_date', now()->year)
                        ->sum('total_amount'),
                    'arrears' => (float) Invoice::withoutGlobalScope('unit')
                        ->where('unit_id', $unit->id)
                        ->whereDate('due_date', '<', $today)
                        ->whereRaw(
                            "(invoices.total_amount - COALESCE((SELECT SUM(sa.amount) FROM settlement_allocations sa INNER JOIN settlements s ON s.id = sa.settlement_id WHERE sa.invoice_id = invoices.id AND s.status = 'completed'), 0)) > 0"
                        )
                        ->selectRaw(
                            "(invoices.total_amount - COALESCE((SELECT SUM(sa.amount) FROM settlement_allocations sa INNER JOIN settlements s ON s.id = sa.settlement_id WHERE sa.invoice_id = invoices.id AND s.status = 'completed'), 0)) as outstanding_amount"
                        )
                        ->get()
                        ->sum('outstanding_amount'),
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
