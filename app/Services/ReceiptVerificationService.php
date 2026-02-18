<?php

namespace App\Services;

use App\Models\Transaction;

class ReceiptVerificationService
{
    public function makeCode(Transaction $transaction): string
    {
        $payload = implode('|', [
            $transaction->transaction_number,
            $transaction->transaction_date?->format('Y-m-d'),
            (string) $transaction->total_amount,
            $transaction->type,
            $transaction->status,
        ]);

        $appKey = (string) config('app.key', 'sakumi-default-key');
        $raw = hash_hmac('sha256', $payload, $appKey);

        return strtoupper(substr($raw, 0, 10));
    }

    public function makeWatermark(Transaction $transaction): string
    {
        return sprintf(
            '%s • %s • %s',
            __('message.watermark_original'),
            $this->makeCode($transaction),
            now()->format('Ymd-His')
        );
    }

    public function makeVerifyUrl(Transaction $transaction, string $code): string
    {
        return route('receipts.verify', ['transactionNumber' => $transaction->transaction_number, 'code' => $code]);
    }

    public function isValid(Transaction $transaction, ?string $code): bool
    {
        if (! $code) {
            return false;
        }

        return hash_equals($this->makeCode($transaction), strtoupper(trim($code)));
    }
}
