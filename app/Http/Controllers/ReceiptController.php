<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function print(Transaction $transaction): View
    {
        $transaction->load(['student.schoolClass', 'items.feeType', 'creator']);

        return view('receipts.print', compact('transaction'));
    }
}
