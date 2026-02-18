<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('receipt.title.expense_receipt') }} - {{ $transaction->transaction_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@500&family=Inter:wght@400&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #2a1715; --muted: #76524a; --line: #d7b9a9; --soft: #fcf7f4;
            --brand-1: #7a2431; --brand-2: #8d342b; --brand-3: #fff2ea; --ok: #b45309;
        }
        * { box-sizing: border-box; }
        @page { size: A5 landscape; margin: 6mm; }
        html, body { margin: 0; padding: 0; font-family: "Inter", "Segoe UI", Tahoma, Arial, sans-serif; font-weight: 400; color: var(--ink); background: #ffffff; font-size: 10px; line-height: 1.25; }
        .sheet { min-height: 136mm; max-height: 136mm; border: 1px solid #c8a091; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; position: relative; }
        .dynamic-watermark { position: absolute; top: 52%; left: 50%; transform: translate(-50%, -50%) rotate(-24deg); font-family: "IBM Plex Sans", "Inter", "Segoe UI", Tahoma, Arial, sans-serif; font-weight: 500; font-size: 20px; letter-spacing: 0.8px; color: rgba(122, 36, 49, 0.08); white-space: nowrap; pointer-events: none; z-index: 0; }
        .topbar { padding: 2.5mm 4mm; color: #fff; background: linear-gradient(180deg, var(--brand-1), var(--brand-2)); display: grid; grid-template-columns: 14mm 1fr 14mm; align-items: center; gap: 3mm; }
        .head-center { min-width: 0; text-align: center; }
        .logo-wrap { width: 14mm; height: 14mm; border-radius: 999px; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #ffffff55; flex: 0 0 auto; }
        .logo-wrap-square { border-radius: 3px; }
        .logo-wrap img { width: 100%; height: 100%; object-fit: contain; }
        .logo-fallback { font-size: 7px; color: var(--brand-1); font-weight: 700; text-align: center; }
        .head-center .name { font-size: 11.5px; font-family: "IBM Plex Sans", "Inter", "Segoe UI", Tahoma, Arial, sans-serif; font-weight: 500; letter-spacing: 0.1px; white-space: normal; overflow: visible; text-overflow: clip; line-height: 1.15; }
        .head-center .address { font-size: 8.5px; opacity: 0.92; white-space: normal; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; line-height: 1.2; max-height: 2.4em; }
        .doc-title { margin: 0; font-size: 11.5px; font-family: "IBM Plex Sans", "Inter", "Segoe UI", Tahoma, Arial, sans-serif; font-weight: 500; letter-spacing: 0.35px; line-height: 1.1; }
        .body { padding: 3.5mm 4mm 3mm; display: flex; flex-direction: column; gap: 2.5mm; flex: 1; min-height: 0; position: relative; z-index: 1; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 2mm; }
        .meta-card { background: var(--soft); border: 1px solid var(--line); border-radius: 6px; padding: 1.8mm 2.2mm; }
        .meta-label { font-size: 8px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 0.8mm; }
        .meta-value { font-size: 10px; font-weight: 400; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .table-wrap { border: 1px solid var(--line); border-radius: 6px; overflow: hidden; flex: 1; min-height: 0; background: #fff; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 1.4mm 1.8mm; vertical-align: top; }
        th { background: var(--brand-3); color: var(--brand-1); font-size: 8px; text-transform: uppercase; letter-spacing: 0.35px; font-weight: 700; }
        tbody tr:nth-child(even) { background: #fff9f5; }
        tbody tr:last-child td { border-bottom: none; }
        .c-no { width: 8%; text-align: center; } .c-item { width: 46%; } .c-note { width: 24%; } .c-amt { width: 22%; text-align: right; white-space: nowrap; }
        .item-name { font-weight: 400; }
        .item-note { color: #475569; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 0; }
        .footer { display: grid; grid-template-columns: 1fr auto; gap: 3mm; align-items: end; }
        .foot-note { color: var(--muted); font-size: 8.2px; }
        .total-box { min-width: 56mm; border: 1px solid #e6b087; border-radius: 6px; overflow: hidden; }
        .total-head { background: #fff1e7; padding: 1.3mm 2mm; font-size: 8px; color: var(--brand-1); text-transform: uppercase; letter-spacing: 0.3px; font-weight: 700; }
        .total-value { padding: 2mm; text-align: right; font-size: 14px; font-family: "IBM Plex Sans", "Inter", "Segoe UI", Tahoma, Arial, sans-serif; font-weight: 500; color: var(--ok); }
        .stamp { margin-top: 0.7mm; text-align: right; font-size: 7.8px; color: var(--muted); }
        @media print { html, body { width: 100%; height: 100%; -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>

<body onload="window.print()">
    @php
        $schoolName = $school_name ?? __('School');
        $schoolAddress = $school_address ?? '';
        $schoolPhone = $school_phone ?? '-';
        $schoolInitials = collect(preg_split('/\s+/', trim($schoolName)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
        $schoolInitials = $schoolInitials !== '' ? $schoolInitials : 'SC';
        $foundationLogo = $foundation_logo ?? '';
        $schoolLogo = $school_logo ?? '';
        $toDataUri = static function (array $candidates): ?string {
            foreach ($candidates as $path) {
                if (!is_string($path) || $path === '' || !file_exists($path)) {
                    continue;
                }
                $contents = @file_get_contents($path);
                if ($contents === false) {
                    continue;
                }
                $mime = @mime_content_type($path) ?: 'image/png';
                return 'data:' . $mime . ';base64,' . base64_encode($contents);
            }
            return null;
        };
        $foundationLogoSrc = $toDataUri([
            $foundationLogo !== '' ? storage_path('app/public/' . ltrim($foundationLogo, '/')) : null,
            public_path('images/logo-yayasan.png'),
            public_path('images/logo-yayasan.jpg'),
            public_path('images/logo-yayasan.jpeg'),
            public_path('images/logo-yayasan.webp'),
            storage_path('app/public/yayasan_logo.png'),
            storage_path('app/public/yayasan_logo.jpg'),
            storage_path('app/public/yayasan_logo.jpeg'),
            storage_path('app/public/yayasan_logo.webp'),
        ]);
        $schoolLogoSrc = $toDataUri([
            $schoolLogo !== '' ? storage_path('app/public/' . ltrim($schoolLogo, '/')) : null,
            public_path('images/kwitansi-logo.png'),
            public_path('images/kwitansi-logo.jpg'),
            public_path('images/kwitansi-logo.jpeg'),
            public_path('images/kwitansi-logo.webp'),
            storage_path('app/public/logo.png'),
            storage_path('app/public/logo.jpg'),
            storage_path('app/public/logo.jpeg'),
            storage_path('app/public/logo.webp'),
        ]);
    @endphp

    <main class="sheet">
        <div class="dynamic-watermark">{{ $watermarkText ?? '' }}</div>
        <section class="topbar">
            <div class="logo-wrap logo-wrap-square" aria-label="Logo Yayasan">
                @if ($foundationLogoSrc)
                    <img src="{{ $foundationLogoSrc }}" alt="Logo Yayasan">
                @else
                    <div class="logo-fallback">YYS</div>
                @endif
            </div>

            <div class="head-center">
                <div class="name">{{ $schoolName }}</div>
                <div class="address">{!! nl2br(e($schoolAddress !== '' ? $schoolAddress : __('receipt.address_not_set'))) !!}</div>
                <h1 class="doc-title">{{ __('receipt.title.expense_receipt') }}</h1>
            </div>

            <div class="logo-wrap" aria-label="Logo {{ $schoolName }}">
                @if ($schoolLogoSrc)
                    <img src="{{ $schoolLogoSrc }}" alt="Logo {{ $schoolName }}">
                @else
                    <div class="logo-fallback">{{ $schoolInitials }}</div>
                @endif
            </div>
        </section>

        <section class="body">
            <div class="meta-grid">
                <div class="meta-card">
                    <div class="meta-label">{{ __('receipt.label.voucher_no') }}</div>
                    <div class="meta-value">{{ $transaction->transaction_number }}</div>
                </div>
                <div class="meta-card">
                    <div class="meta-label">{{ __('receipt.label.date') }}</div>
                    <div class="meta-value">{{ $transaction->transaction_date->format('d/m/Y') }}</div>
                </div>
                <div class="meta-card">
                    <div class="meta-label">{{ __('receipt.label.method') }}</div>
                    <div class="meta-value">{{ strtoupper($transaction->payment_method ?? '-') }}</div>
                </div>
            </div>

            <div class="meta-grid">
                <div class="meta-card">
                    <div class="meta-label">{{ __('receipt.label.transaction_type') }}</div>
                    <div class="meta-value">{{ __('receipt.expense_type') }}</div>
                </div>
                <div class="meta-card">
                    <div class="meta-label">{{ __('receipt.label.notes') }}</div>
                    <div class="meta-value">{{ $transaction->description ?: '-' }}</div>
                </div>
                <div class="meta-card">
                    <div class="meta-label">{{ __('receipt.label.officer') }}</div>
                    <div class="meta-value">{{ $transaction->creator->name ?? 'SYSTEM' }}</div>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="c-no">{{ __('receipt.table.no') }}</th>
                            <th class="c-item">{{ __('receipt.table.expense_desc') }}</th>
                            <th class="c-note">{{ __('receipt.table.detail') }}</th>
                            <th class="c-amt">{{ __('receipt.table.nominal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transaction->items as $index => $item)
                            <tr>
                                <td class="c-no">{{ $index + 1 }}</td>
                                <td class="c-item">
                                    <div class="item-name">{{ $item->feeType->name ?? '-' }}</div>
                                </td>
                                <td class="c-note">
                                    <div class="item-note">{{ $item->description ?: '-' }}</div>
                                </td>
                                <td class="c-amt">Rp {{ number_format((float) $item->amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center; color:#64748b;">{{ __('receipt.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <section class="footer">
                <div class="foot-note">
                    Dokumen ini merupakan bukti pembayaran resmi sekolah.<br>
                    {{ __('receipt.label.issued_at') }}: {{ optional($receiptIssuedAt ?? null)->format('d/m/Y H:i:s') ?? '-' }}<br>
                    {{ __('receipt.label.printed_at') }}: {{ optional($receiptPrintedAt ?? null)->format('d/m/Y H:i:s') ?? now()->format('d/m/Y H:i:s') }}<br>
                    {{ __('receipt.label.print_status') }}: {{ $receiptPrintStatus ?? 'ORIGINAL' }}<br>
                    {{ __('receipt.footer.verification') }}: {{ $verificationCode ?? '-' }}
                </div>
                <div>
                    <div class="total-box">
                        <div class="total-head">{{ __('receipt.total.expense') }}</div>
                        <div class="total-value">Rp {{ number_format((float) $transaction->total_amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="stamp">
                        Digital Signature: {{ $transaction->creator->name ?? 'Administration Admin' }}<br>
                        {{ __('receipt.footer.verification') }}: {{ $verificationUrl ?? '-' }}
                    </div>
                </div>
            </section>
        </section>
    </main>
</body>

</html>
