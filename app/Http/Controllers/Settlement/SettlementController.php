<?php

namespace App\Http\Controllers\Settlement;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Settlement;
use App\Models\Student;
use App\Services\SettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SettlementController extends Controller
{
    public function __construct(
        private readonly SettlementService $settlementService,
    ) {}

    public function index(Request $request): View
    {
        $query = Settlement::with(['student.schoolClass', 'creator']);
        $likeOperator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search, $likeOperator) {
                $q->where('settlement_number', $likeOperator, "%{$search}%")
                    ->orWhereHas('student', fn ($sq) => $sq->where('name', $likeOperator, "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $settlements = $query->latest()->paginate(15)->withQueryString();

        return view('settlements.index', compact('settlements'));
    }

    public function create(Request $request): View
    {
        $students = Student::with('schoolClass')->where('status', 'active')->orderBy('name')->get();
        $selectedStudentId = $request->input('student_id');
        $outstandingInvoices = collect();

        if ($selectedStudentId) {
            $outstandingInvoices = Invoice::where('student_id', $selectedStudentId)
                ->whereIn('status', ['unpaid', 'partially_paid'])
                ->with('items.feeType')
                ->orderBy('due_date')
                ->get();
        }

        return view('settlements.create', compact('students', 'outstandingInvoices', 'selectedStudentId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $unitId = session('current_unit_id');

        $validated = $request->validate([
            'student_id' => ['required', Rule::exists('students', 'id')->where('unit_id', $unitId)],
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,qris',
            'total_amount' => 'required|numeric|gt:0',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'allocations' => 'required|array|min:1',
            'allocations.*.invoice_id' => ['required', Rule::exists('invoices', 'id')->where('unit_id', $unitId)],
            'allocations.*.amount' => 'required|numeric|min:0',
        ]);

        // Build allocations map: invoice_id => amount
        $allocations = [];
        foreach ($validated['allocations'] as $alloc) {
            $amount = (float) $alloc['amount'];
            if ($amount > 0) {
                $allocations[(int) $alloc['invoice_id']] = $amount;
            }
        }

        if (empty($allocations)) {
            return back()->withInput()->with('error', 'At least one invoice must have an allocation amount greater than zero.');
        }

        try {
            $settlement = $this->settlementService->createSettlement(
                data: [
                    'student_id' => (int) $validated['student_id'],
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'total_amount' => (float) $validated['total_amount'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ],
                allocations: $allocations,
                userId: (int) auth()->id(),
            );

            return redirect()->route('settlements.show', $settlement)
                ->with('success', 'Settlement created: ' . $settlement->settlement_number);
        } catch (\Throwable $e) {
            Log::error('Failed to create settlement', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create settlement: ' . $e->getMessage());
        }
    }

    public function show(Settlement $settlement): View
    {
        $settlement->load([
            'student.schoolClass',
            'allocations.invoice',
            'creator',
            'canceller',
        ]);

        return view('settlements.show', compact('settlement'));
    }

    public function destroy(Request $request, Settlement $settlement): RedirectResponse
    {
        try {
            $this->settlementService->cancel(
                settlement: $settlement,
                userId: (int) auth()->id(),
                reason: (string) ($request->input('cancellation_reason') ?: 'Cancelled by administrator'),
            );

            return redirect()->route('settlements.index')
                ->with('success', 'Settlement cancelled successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
