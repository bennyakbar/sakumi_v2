<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\StudentObligation;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function resolveScope(Request $request): array
    {
        $scope = $request->input('scope', 'unit');
        $consolidated = $scope === 'all' && auth()->user()->hasRole('super_admin');

        return [$consolidated ? 'all' : 'unit', $consolidated];
    }

    public function daily(Request $request): View
    {
        [$scope, $consolidated] = $this->resolveScope($request);
        $date = $request->input('date', date('Y-m-d'));

        $query = Transaction::query();

        if ($consolidated) {
            $query->withoutGlobalScope('unit')->with([
                'unit',
                'student' => fn ($q) => $q->withoutGlobalScope('unit'),
                'student.schoolClass' => fn ($q) => $q->withoutGlobalScope('unit'),
                'items.feeType' => fn ($q) => $q->withoutGlobalScope('unit'),
                'creator',
            ]);
        } else {
            $query->with(['student.schoolClass', 'items.feeType', 'creator']);
        }

        $transactions = $query
            ->whereDate('transaction_date', $date)
            ->where('status', 'completed')
            ->latest()
            ->get();

        $totalAmount = $transactions->sum('total_amount');

        return view('reports.daily', compact('transactions', 'date', 'totalAmount', 'scope', 'consolidated'));
    }

    public function monthly(Request $request): View
    {
        [$scope, $consolidated] = $this->resolveScope($request);
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $query = Transaction::query();

        if ($consolidated) {
            $query->withoutGlobalScope('unit')->with([
                'unit',
                'student' => fn ($q) => $q->withoutGlobalScope('unit'),
                'student.schoolClass' => fn ($q) => $q->withoutGlobalScope('unit'),
                'items.feeType' => fn ($q) => $q->withoutGlobalScope('unit'),
            ]);
        } else {
            $query->with(['student.schoolClass', 'items.feeType']);
        }

        $transactions = $query
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->where('status', 'completed')
            ->orderBy('transaction_date')
            ->get();

        // Group by Date for summary
        $dailySummary = $transactions->groupBy(function ($item) {
            return $item->transaction_date->format('Y-m-d');
        })->map(function ($dayTransactions) {
            return $dayTransactions->sum('total_amount');
        });

        $totalAmount = $transactions->sum('total_amount');

        return view('reports.monthly', compact('transactions', 'dailySummary', 'month', 'year', 'totalAmount', 'scope', 'consolidated'));
    }

    public function arrears(Request $request): View
    {
        [$scope, $consolidated] = $this->resolveScope($request);
        $classId = $request->input('class_id');
        $month = $request->input('month', date('n')); // 1-12
        $year = $request->input('year', date('Y'));

        $query = StudentObligation::query();

        if ($consolidated) {
            $query->withoutGlobalScope('unit')->with([
                'unit',
                'student' => fn ($q) => $q->withoutGlobalScope('unit'),
                'student.schoolClass' => fn ($q) => $q->withoutGlobalScope('unit'),
                'feeType' => fn ($q) => $q->withoutGlobalScope('unit'),
            ]);
        } else {
            $query->with(['student.schoolClass', 'feeType']);
        }

        $query->where('is_paid', false)
            ->where('month', $month)
            ->where('year', $year);

        if ($classId) {
            $query->whereHas('student', function ($q) use ($classId, $consolidated) {
                if ($consolidated) {
                    $q->withoutGlobalScope('unit');
                }
                $q->where('class_id', $classId);
            });
        }

        $arrears = $query->paginate(20);

        $classQuery = SchoolClass::query();
        if ($consolidated) {
            $classQuery->withoutGlobalScope('unit');
        }
        $classes = $classQuery->get();

        return view('reports.arrears', compact('arrears', 'classes', 'classId', 'month', 'year', 'scope', 'consolidated'));
    }
}
