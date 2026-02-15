<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\StudentObligation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function daily(Request $request): View
    {
        $date = $request->input('date', date('Y-m-d'));

        $transactions = Transaction::with(['student.schoolClass', 'items.feeType', 'creator'])
            ->whereDate('transaction_date', $date)
            ->where('status', 'completed')
            ->latest()
            ->get();

        $totalAmount = $transactions->sum('total_amount');

        return view('reports.daily', compact('transactions', 'date', 'totalAmount'));
    }

    public function monthly(Request $request): View
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $transactions = Transaction::with(['student.schoolClass', 'items.feeType'])
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

        return view('reports.monthly', compact('transactions', 'dailySummary', 'month', 'year', 'totalAmount'));
    }

    public function arrears(Request $request): View
    {
        // This report shows unpaid obligations.
        // Assuming StudentObligation model tracks required payments.
        // For this iteration, we might not have full obligation tracking populated yet,
        // so we'll query StudentObligations where status is unpaid.

        $classId = $request->input('class_id');
        $month = $request->input('month', date('n')); // 1-12
        $year = $request->input('year', date('Y'));

        $query = StudentObligation::with(['student.schoolClass', 'feeType'])
            ->where('is_paid', false)
            ->where('month', $month)
            ->where('year', $year);

        if ($classId) {
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $arrears = $query->paginate(20);
        $classes = \App\Models\SchoolClass::all();

        return view('reports.arrears', compact('arrears', 'classes', 'classId', 'month', 'year'));
    }
}
