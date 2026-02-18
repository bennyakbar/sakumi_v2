<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ReceiptVerificationService;
use App\Services\SchoolIdentityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function __construct(
        private readonly ReceiptVerificationService $verificationService,
        private readonly SchoolIdentityService $schoolIdentityService,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function print(Transaction $transaction): View
    {
        $transaction->load(['student.schoolClass', 'items.feeType', 'creator']);
        $verificationCode = $this->verificationService->makeCode($transaction);
        $verificationUrl = $this->verificationService->makeVerifyUrl($transaction, $verificationCode);
        $watermarkText = $this->verificationService->makeWatermark($transaction);
        $school = $this->schoolIdentityService->resolve($transaction->unit_id);

        $view = $transaction->type === 'expense'
            ? 'receipts.print-expense'
            : 'receipts.print';

        return view($view, [
            'transaction' => $transaction,
            'verificationCode' => $verificationCode,
            'verificationUrl' => $verificationUrl,
            'watermarkText' => $watermarkText,
            ...$school,
        ]);
    }

    public function verify(Request $request, string $transactionNumber): View
    {
        $transaction = Transaction::query()
            ->withoutGlobalScope('unit')
            ->with(['student.schoolClass', 'items.feeType', 'creator'])
            ->where('transaction_number', $transactionNumber)
            ->firstOrFail();

        $code = (string) $request->query('code', '');
        $isValid = $this->verificationService->isValid($transaction, $code);
        $expectedCode = $this->verificationService->makeCode($transaction);

        return view('receipts.verify', compact('transaction', 'code', 'isValid', 'expectedCode'));
    }
}
