<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\FeeType;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Transaction::with(['student.schoolClass', 'items', 'creator']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', (string) $request->input('payment_method'));
        }

        if ($request->filled('class_id')) {
            $classId = (int) $request->input('class_id');
            $query->whereHas('student', fn ($sq) => $sq->where('class_id', $classId));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', (string) $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', (string) $request->input('date_to'));
        }

        $transactions = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $classes = SchoolClass::query()->orderBy('name')->get(['id', 'name']);

        return view('transactions.index', compact('transactions', 'classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $students = Student::with('schoolClass')->where('status', 'active')->get();
        $feeTypes = FeeType::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('transactions.create', compact('students', 'feeTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $unitId = session('current_unit_id');

        $validated = $request->validate([
            'student_id' => ['required', Rule::exists('students', 'id')->where('unit_id', $unitId)],
            'transaction_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,qris',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.fee_type_id' => ['required', Rule::exists('fee_types', 'id')->where('unit_id', $unitId)],
            'items.*.amount' => 'required|numeric|gt:0',
            'items.*.description' => 'nullable|string',
        ]);

        try {
            $transaction = $this->transactionService->createIncome(
                data: [
                    'student_id' => (int) $validated['student_id'],
                    'transaction_date' => $validated['transaction_date'],
                    'payment_method' => $validated['payment_method'],
                    'description' => $validated['description'] ?? null,
                ],
                items: collect($validated['items'])
                    ->values()
                    ->map(fn (array $item): array => [
                        'fee_type_id' => (int) $item['fee_type_id'],
                        'amount' => (float) $item['amount'],
                        'description' => $item['description'] ?? null,
                    ])->all(),
                userId: (int) auth()->id(),
            );

            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Transaction created successfully. Number: ' . $transaction->transaction_number);
        } catch (\Throwable $e) {
            Log::error('Failed to create transaction', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Failed to create transaction: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): View
    {
        $transaction->load(['student.schoolClass', 'items.feeType', 'creator']);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Transactions usually shouldn't be edited directly to maintain audit trail.
        // If needed, can implement void/cancel logic instead.
        return back()->with('error', 'Transactions cannot be edited.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        try {
            $this->transactionService->cancel(
                transaction: $transaction->load('items'),
                userId: (int) auth()->id(),
                reason: (string) ($request->input('cancellation_reason') ?: 'Cancelled by administrator'),
            );

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction cancelled successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

    }
}
