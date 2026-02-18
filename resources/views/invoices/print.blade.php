<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('receipt.title.invoice') }} - {{ $invoice->invoice_number }}</title>
    <style>
        :root {
            --border: #1f2937;
            --muted: #6b7280;
            --bg-soft: #f8fafc;
            --font-size: 10px;
            --line-height: 1.2;
            --page-pad: 6mm;
            --section-gap: 3mm;
            --cell-pad: 1.2mm;
            --meta-pad: 0.7mm;
            --footer-sign-gap: 4mm;
            --footer-min-height: 14mm;
        }

        * {
            box-sizing: border-box;
        }

        @page {
            size: A5 landscape;
            margin: 6mm;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            background: #fff;
            font-size: var(--font-size);
            line-height: var(--line-height);
        }

        .invoice {
            width: 100%;
            border: 1px solid var(--border);
            padding: var(--page-pad);
            display: grid;
            grid-template-rows: auto auto minmax(0, 1fr) auto;
            gap: var(--section-gap);
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .invoice.compact {
            --font-size: 9px;
            --line-height: 1.15;
            --page-pad: 4.5mm;
            --section-gap: 2mm;
            --cell-pad: 0.8mm;
            --meta-pad: 0.45mm;
            --footer-sign-gap: 3mm;
            --footer-min-height: 12mm;
        }

        .header {
            display: grid;
            grid-template-columns: 24mm 1fr 24mm;
            gap: 4mm;
            align-items: center;
            border-bottom: 1px solid var(--border);
            padding-bottom: 2.5mm;
            min-height: 0;
        }

        .logo-box {
            width: 24mm;
            height: 24mm;
            border: 1px dashed var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 8px;
            color: var(--muted);
            overflow: hidden;
        }

        .logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .title h1 {
            margin: 0;
            font-size: 15px;
            letter-spacing: 0.25px;
            line-height: 1.1;
        }

        .title p {
            margin: 0.5mm 0 0;
            color: #374151;
        }

        .title .line-clip {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .title .address {
            white-space: pre-line;
            overflow: visible;
            display: block;
            line-height: 1.15;
        }

        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4mm;
            min-height: 0;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .meta-table td {
            padding: var(--meta-pad) 0;
            vertical-align: top;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .meta-table td:first-child {
            width: 34mm;
            color: var(--muted);
        }

        .items {
            min-height: 0;
            display: grid;
            grid-template-rows: minmax(0, 1fr) auto;
            gap: 1.5mm;
        }

        .items-table-wrap {
            min-height: 0;
            overflow: hidden;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .items-table th,
        .items-table td {
            border: 1px solid var(--border);
            padding: var(--cell-pad);
            vertical-align: middle;
            line-height: 1.1;
        }

        .items-table th {
            background: var(--bg-soft);
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.2px;
            white-space: nowrap;
        }

        .col-no {
            width: 8%;
            text-align: center;
        }

        .col-item {
            width: 46%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .col-period {
            width: 18%;
            text-align: center;
            white-space: nowrap;
        }

        .col-amount {
            width: 28%;
            text-align: right;
            white-space: nowrap;
        }

        .totals {
            margin-left: auto;
            width: 64mm;
            border-collapse: collapse;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .totals td {
            border: 1px solid var(--border);
            padding: var(--cell-pad);
        }

        .totals td:first-child {
            background: var(--bg-soft);
            font-weight: 600;
        }

        .totals td:last-child {
            text-align: right;
            font-weight: 700;
            white-space: nowrap;
        }

        .footer {
            border-top: 1px solid var(--border);
            padding-top: 2.5mm;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4mm;
            align-items: start;
            min-height: 0;
            min-height: var(--footer-min-height);
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .payment-info p {
            margin: 0.5mm 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .signature {
            justify-self: end;
            width: 54mm;
            text-align: center;
            align-self: end;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .signature .label {
            margin: 0;
            font-size: 9px;
            color: #374151;
        }

        .signature .line {
            border-top: 1px solid var(--border);
            padding-top: 1mm;
            margin-top: var(--footer-sign-gap);
            font-weight: 700;
            font-size: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .signature .hint {
            margin-top: 0.5mm;
            font-size: 8px;
            color: var(--muted);
        }

        .status-badge {
            display: inline-block;
            padding: 0.7mm 2mm;
            font-size: 8px;
            font-weight: 700;
            border-radius: 1.5mm;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            white-space: nowrap;
        }

        .status-unpaid {
            background: #fef3c7;
            color: #92400e;
        }

        .status-partially_paid {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }

        @media screen {
            body {
                padding: 8px;
                display: flex;
                justify-content: center;
                align-items: flex-start;
            }

            .invoice {
                width: 198mm;
                min-height: 136mm;
            }
        }

        @media print {
            html,
            body {
                margin: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .invoice,
            .items,
            .items-table,
            .footer {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .invoice {
                width: 100%;
                min-height: 100%;
                page-break-after: avoid;
                break-after: avoid;
            }
        }
    </style>
</head>

<body onload="window.print()">
    @php
        $schoolName = $school_name ?? __('School');
        $schoolAddress = trim(str_replace('\n', "\n", (string) ($school_address ?? '')));
        if ($schoolAddress === '') {
            $schoolAddress = __('receipt.address_not_set');
        }
        $schoolPhone = $school_phone ?? '-';
        $schoolLogo = $school_logo ?? '';
        $foundationLogo = $foundation_logo ?? '';
        $schoolInitials = collect(preg_split('/\s+/', trim($schoolName)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
        $schoolInitials = $schoolInitials !== '' ? $schoolInitials : 'SC';
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
        $itemCount = $invoice->items->count();
        $isCompact = $itemCount > 15;
    @endphp

    <main class="invoice {{ $isCompact ? 'compact' : '' }}">
        <section class="header">
            <div class="logo-box" aria-label="{{ __('receipt.logo_fallback') }}">
                @if ($foundationLogoSrc)
                    <img src="{{ $foundationLogoSrc }}" alt="Logo Yayasan">
                @else
                    YYS
                @endif
            </div>
            <div class="title">
                <h1>{{ __('receipt.title.invoice') }}</h1>
                <p class="line-clip"><strong>{{ $schoolName }}</strong></p>
                <p class="address">{!! nl2br(e($schoolAddress !== '' ? $schoolAddress : __('receipt.address_not_set'))) !!}</p>
            </div>
            <div class="logo-box" aria-label="{{ __('receipt.logo_fallback') }}" style="margin-left:auto;">
                @if ($schoolLogoSrc)
                    <img src="{{ $schoolLogoSrc }}" alt="{{ __('receipt.logo_fallback') }} {{ $schoolName }}">
                @else
                    {{ $schoolInitials }}
                @endif
            </div>
        </section>

        <section class="meta">
            <table class="meta-table">
                <tr>
                    <td>{{ __('receipt.label_invoice_no') }}</td>
                    <td>: {{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td>{{ __('receipt.label.date') }}</td>
                    <td>: {{ $invoice->invoice_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>{{ __('receipt.label_due_date') }}</td>
                    <td>: {{ $invoice->due_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>{{ __('receipt.label_period') }}</td>
                    <td>: <span style="text-transform:uppercase">{{ $invoice->period_type }}</span> — {{ $invoice->period_identifier }}</td>
                </tr>
                <tr>
                    <td>{{ __('receipt.label_status') }}</td>
                    <td>: <span class="status-badge status-{{ $invoice->status }}">{{ str_replace('_', ' ', $invoice->status) }}</span></td>
                </tr>
            </table>

            <table class="meta-table">
                <tr>
                    <td>{{ __('receipt.label.student_name') }}</td>
                    <td>: {{ $invoice->student->name }}</td>
                </tr>
                <tr>
                    <td>{{ __('receipt.label_nis') }}</td>
                    <td>: {{ $invoice->student->nis ?? '-' }}</td>
                </tr>
                <tr>
                    <td>{{ __('receipt.label.class') }}</td>
                    <td>: {{ $invoice->student->schoolClass->name ?? '-' }}</td>
                </tr>
            </table>
        </section>

        <section class="items">
            <div class="items-table-wrap">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th class="col-no">{{ __('receipt.table.no') }}</th>
                            <th class="col-item">{{ __('receipt.table.invoice_item') }}</th>
                            <th class="col-period">{{ __('receipt.table.period') }}</th>
                            <th class="col-amount">{{ __('receipt.table.nominal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoice->items as $index => $item)
                            <tr>
                                <td class="col-no">{{ $index + 1 }}</td>
                                <td class="col-item">{{ $item->feeType->name ?? $item->description }}</td>
                                <td class="col-period">
                                    @if($item->month && $item->year)
                                        {{ sprintf('%02d/%d', $item->month, $item->year) }}
                                    @elseif($item->year)
                                        {{ $item->year }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="col-amount">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center; color: #6b7280;">{{ __('receipt.no_invoice_items') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <table class="totals">
                <tr>
                    <td>{{ __('receipt.total.invoice') }}</td>
                    <td>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>{{ __('receipt.total.paid') }}</td>
                    <td>Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>{{ __('receipt.total.outstanding') }}</td>
                    <td>Rp {{ number_format($invoice->outstanding, 0, ',', '.') }}</td>
                </tr>
            </table>
        </section>

        <section class="footer">
            <div class="payment-info">
                @if($invoice->notes)
                    <p><strong>{{ __('receipt.label.notes') }}:</strong> {{ $invoice->notes }}</p>
                @endif
                <p>{{ __('receipt.label.printed_at') }}: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
            <div class="signature">
                <div class="label">{{ __('receipt.label.school_treasurer') }}</div>
                <div class="line">{{ $invoice->creator->name ?? 'Administration' }}</div>
                <p class="hint">{{ __('receipt.footer.digitally_signed') }}</p>
            </div>
        </section>
    </main>
</body>

</html>
