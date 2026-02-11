<?php

namespace App\Services;

use App\Models\FeeMatrix;
use App\Models\Student;
use App\Models\StudentObligation;
use Illuminate\Support\Facades\DB;

class ArrearsService
{
    public function generateMonthlyObligations(int $month, int $year): int
    {
        $students = Student::where('status', 'active')->get();
        $created = 0;

        foreach ($students as $student) {
            $feeEntries = FeeMatrix::where('is_active', true)
                ->whereHas('feeType', fn ($q) => $q->where('is_monthly', true)->where('is_active', true))
                ->where(function ($q) use ($student) {
                    $q->whereNull('class_id')->orWhere('class_id', $student->class_id);
                })
                ->where(function ($q) use ($student) {
                    $q->whereNull('category_id')->orWhere('category_id', $student->category_id);
                })
                ->where('effective_from', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('effective_to')->orWhere('effective_to', '>=', now());
                })
                ->orderByRaw('class_id DESC NULLS LAST, category_id DESC NULLS LAST')
                ->get()
                ->unique('fee_type_id');

            foreach ($feeEntries as $entry) {
                $inserted = DB::table('student_obligations')
                    ->insertOrIgnore([
                        'student_id' => $student->id,
                        'fee_type_id' => $entry->fee_type_id,
                        'month' => $month,
                        'year' => $year,
                        'amount' => $entry->amount,
                        'is_paid' => false,
                        'paid_amount' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                $created += $inserted;
            }
        }

        return $created;
    }

    public function getArrearsByStudent(int $studentId): array
    {
        return StudentObligation::where('student_id', $studentId)
            ->where('is_paid', false)
            ->with('feeType')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    public function getArrearsSummaryByClass(int $classId): array
    {
        return StudentObligation::where('is_paid', false)
            ->whereHas('student', fn ($q) => $q->where('class_id', $classId)->where('status', 'active'))
            ->with('student', 'feeType')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->groupBy('student_id')
            ->toArray();
    }
}
