<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\FeeType;
use App\Models\Invoice;
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
        $incomeFeeTypes = FeeType::query()
            ->where('is_active', true)
            ->where('code', 'not like', 'EXP-%')
            ->orderBy('name')
            ->get(['id', 'name']);
        $expenseFeeTypes = FeeType::query()
            ->with(['expenseFeeSubcategory.category'])
            ->where('is_active', true)
            ->whereNotNull('expense_fee_subcategory_id')
            ->get()
            ->sortBy(fn (FeeType $feeType): string => sprintf(
                '%03d-%03d-%s',
                (int) ($feeType->expenseFeeSubcategory?->category?->sort_order ?? 999),
                (int) ($feeType->expenseFeeSubcategory?->sort_order ?? 999),
                $feeType->name
            ))
            ->map(fn (FeeType $feeType): array => [
                'id' => $feeType->id,
                'name' => $feeType->name,
                'category' => $feeType->expenseFeeSubcategory?->category?->name ?? 'Uncategorized',
                'subcategory' => $feeType->expenseFeeSubcategory?->name ?? 'General',
            ])->values();
        $canCreateExpense = auth()->user()?->can('transactions.expense.create') ?? false;

        // Phase 3: Collect student IDs with outstanding invoices for client-side warning
        $studentsWithOutstandingInvoices = Invoice::whereIn('status', ['unpaid', 'partially_paid'])
            ->pluck('student_id')
            ->unique()
            ->values();

        return view('transactions.create', compact('students', 'incomeFeeTypes', 'expenseFeeTypes', 'canCreateExpense', 'studentsWithOutstandingInvoices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $unitId = session('current_unit_id');
        $transactionType = (string) $request->input('type', 'income');

        if (! in_array($transactionType, ['income', 'expense'], true)) {
            return back()->withInput()->withErrors(['type' => 'Invalid transaction type.']);
        }

        if ($transactionType === 'expense' && ! (auth()->user()?->can('transactions.expense.create'))) {
            abort(403, 'You are not authorized to create expense transactions.');
        }

        $validated = $request->validate([
            'type' => 'nullable|in:income,expense',
            'student_id' => [
                Rule::requiredIf($transactionType === 'income'),
                'nullable',
                Rule::exists('students', 'id')->where('unit_id', $unitId),
            ],
            'transaction_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,qris',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.fee_type_id' => ['required', Rule::exists('fee_types', 'id')->where('unit_id', $unitId)],
            'items.*.amount' => 'required|numeric|gt:0',
            'items.*.description' => 'nullable|string',
        ]);

        // Phase 3: Hard block — reject income if student has outstanding invoices
        if ($transactionType === 'income' && !empty($validated['student_id'])) {
            $hasOutstandingInvoices = Invoice::where('student_id', $validated['student_id'])
                ->whereIn('status', ['unpaid', 'partially_paid'])
                ->exists();
            if ($hasOutstandingInvoices) {
                return back()->withErrors([
                    'student_id' => 'Siswa ini memiliki invoice yang belum lunas. Gunakan modul Settlement untuk pembayaran invoice.',
                ])->withInput();
            }
        }

        try {
            $items = collect($validated['items'])
                ->values()
                ->map(fn (array $item): array => [
                    'fee_type_id' => (int) $item['fee_type_id'],
                    'amount' => (float) $item['amount'],
                    'description' => $item['description'] ?? null,
                ])->all();

            $transaction = $transactionType === 'expense'
                ? $this->transactionService->createExpense(
                    data: [
                        'transaction_date' => $validated['transaction_date'],
                        'payment_method' => $validated['payment_method'],
                        'description' => $validated['description'] ?? null,
                    ],
                    items: $items,
                    userId: (int) auth()->id(),
                )
                : $this->transactionService->createIncome(
                    data: [
                        'student_id' => (int) $validated['student_id'],
                        'transaction_date' => $validated['transaction_date'],
                        'payment_method' => $validated['payment_method'],
                        'description' => $validated['description'] ?? null,
                    ],
                    items: $items,
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
