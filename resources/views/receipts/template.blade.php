<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <title>{{ __('receipt.title.payment_receipt') }} - {{ $transaction->transaction_number }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Inter", Arial, Helvetica, sans-serif;
            font-weight: 400;
            color: #111827;
            font-size: 11px;
            line-height: 1.35;
        }

        .school-address {
            white-space: pre-line;
        }

        .receipt {
            border: 1px solid #1f2937;
            padding: 12mm;
            min-height: 100%;
            position: relative;
        }

        .watermark {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-20deg);
            font-size: 44px;
            font-weight: 700;
            letter-spacing: 4px;
            color: rgba(220, 38, 38, 0.2);
        }

        .watermark.dynamic {
            top: 60%;
            font-size: 24px;
            letter-spacing: 2px;
            color: rgba(15, 23, 42, 0.08);
            transform: translate(-50%, -50%) rotate(-24deg);
        }

        .header {
            display: table;
            width: 100%;
            border-bottom: 1px solid #1f2937;
            padding-bottom: 6mm;
            margin-bottom: 6mm;
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: middle;
        }

        .header-left {
            width: 34mm;
        }

        .logo-box {
            width: 30mm;
            height: 30mm;
            border: 1px dashed #1f2937;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            line-height: 30mm;
            overflow: hidden;
        }

        .logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .title {
            margin: 0;
            font-size: 18px;
            font-family: "IBM Plex Sans", "Inter", Arial, Helvetica, sans-serif;
            font-weight: 500;
        }

        .meta {
            width: 100%;
            margin-bottom: 6mm;
        }

        .meta td {
            vertical-align: top;
            width: 50%;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 1mm 0;
        }

        .meta-table td:first-child {
            width: 36mm;
            color: #6b7280;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
        }

        .items th,
        .items td {
            border: 1px solid #1f2937;
            padding: 2mm;
            vertical-align: top;
        }

        .items th {
            background: #f3f4f6;
            font-size: 10px;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total {
            width: 70mm;
            margin-top: 4mm;
            margin-left: auto;
            border-collapse: collapse;
        }

        .total td {
            border: 1px solid #1f2937;
            padding: 2mm;
        }

        .total td:first-child {
            background: #f3f4f6;
            font-weight: 700;
        }

        .total td:last-child {
            text-align: right;
            font-family: "IBM Plex Sans", "Inter", Arial, Helvetica, sans-serif;
            font-weight: 500;
        }

        .footer {
            border-top: 1px solid #1f2937;
            margin-top: 8mm;
            padding-top: 6mm;
        }

        .signature {
            width: 60mm;
            margin-left: auto;
            text-align: center;
        }

        .signature .label {
            margin-bottom: 16mm;
            font-size: 10px;
            color: #374151;
        }

        .signature .line {
            border-top: 1px solid #1f2937;
            padding-top: 1.5mm;
            font-weight: 700;
            font-size: 11px;
        }

        .signature .hint {
            font-size: 9px;
            color: #6b7280;
            margin-top: 1mm;
        }
    </style>
</head>

<body>
    @php
        $school_name = $school_name ?? __('School');
        $school_address = $school_address ?? '';
        $school_phone = $school_phone ?? '-';
        $isExpense = ($transaction->type ?? 'income') === 'expense';
    @endphp

    <main class="receipt">
        @if (!empty($cancelled))
            <div class="watermark">CANCELLED</div>
        @endif
        <div class="watermark dynamic">{{ $watermark_text ?? '' }}</div>

        <section class="header">
            <div class="header-left">
                <div class="logo-box">
                    @if (!empty($show_logo) && !empty($school_logo))
                        <img src="{{ public_path('storage/' . $school_logo) }}" alt="{{ __('receipt.logo_fallback') }}">
                    @elseif (file_exists(public_path('images/kwitansi-logo.png')))
                        <img src="{{ public_path('images/kwitansi-logo.png') }}" alt="{{ __('receipt.logo_fallback') }}">
                    @else
                        {{ __('receipt.logo_fallback') }}
                    @endif
                </div>
            </div>
            <div class="header-right">
                <h1 class="title">{{ $isExpense ? __('receipt.title.expense_receipt') : __('receipt.title.payment_receipt') }}</h1>
                <div><strong>{{ $school_name }}</strong></div>
                <div class="school-address">{{ $school_address !== '' ? $school_address : __('receipt.address_not_set') }}</div>
                <div>{{ __('receipt.label.phone') }}: {{ $school_phone ?: '-' }}</div>
            </div>
        </section>

        <table class="meta">
            <tr>
                <td>
                    <table class="meta-table">
                        <tr>
                            <td>{{ __('receipt.label.transaction_no') }}</td>
                            <td>: {{ $transaction->transaction_number }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('receipt.label.date') }}</td>
                            <td>: {{ $transaction->transaction_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('receipt.label.payment_method') }}</td>
                            <td>: {{ ucfirst($transaction->payment_method ?? '-') }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="meta-table">
                        <tr>
                            <td>{{ $isExpense ? __('receipt.label.transaction_type') : __('receipt.label.student_name') }}</td>
                            <td>: {{ $isExpense ? __('receipt.expense_type') : ($transaction->student->name ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td>{{ $isExpense ? __('receipt.label.notes') : __('receipt.label.class') }}</td>
                            <td>: {{ $isExpense ? ($transaction->description ?: '-') : ($transaction->student->schoolClass->name ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('receipt.label.officer') }}</td>
                            <td>: {{ $transaction->creator->name ?? 'System' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th class="text-center" style="width:10%;">{{ __('receipt.table.no') }}</th>
                    <th style="width:45%;">{{ $isExpense ? __('receipt.table.expense_desc') : __('receipt.table.description') }}</th>
                    <th style="width:25%;">{{ __('receipt.table.detail') }}</th>
                    <th class="text-right" style="width:20%;">{{ __('receipt.table.nominal') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaction->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->feeType->name ?? '-' }}</td>
                        <td>{{ $item->description ?: '-' }}</td>
                        <td class="text-right">Rp {{ number_format((float) $item->amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">{{ __('receipt.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="total">
            <tr>
                <td>{{ $isExpense ? __('receipt.total.expense') : __('receipt.total.payment') }}</td>
                <td>Rp {{ number_format((float) $transaction->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>

        <section class="footer">
            <div style="font-size:10px; color:#6b7280; margin-bottom:4mm;">
                {{ __('receipt.footer.verification') }}: <strong>{{ $verification_code ?? '-' }}</strong><br>
                {{ __('receipt.footer.verification') }}: {{ $verification_url ?? '-' }}
            </div>
            <div class="signature">
                <div class="label">{{ __('receipt.label_digital_sig') }}<br>{{ __('receipt.label_admin_tu') }}</div>
                <div class="line">{{ $transaction->creator->name ?? 'Administration Admin' }}</div>
                <div class="hint">{{ __('receipt.footer.digitally_signed') }}</div>
            </div>
        </section>
    </main>
</body>

</html>
