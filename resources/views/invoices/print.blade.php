<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        :root {
            --border: #1f2937;
            --muted: #6b7280;
            --bg-soft: #f8fafc;
        }

        * {
            box-sizing: border-box;
        }

        @page {
            size: A5 landscape;
            margin: 10mm;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            background: #fff;
            font-size: 11px;
            line-height: 1.35;
        }

        .invoice {
            border: 1px solid var(--border);
            padding: 12mm;
            min-height: calc(100vh - 2mm);
            display: flex;
            flex-direction: column;
            gap: 8mm;
        }

        .header {
            display: grid;
            grid-template-columns: 30mm 1fr;
            gap: 8mm;
            align-items: center;
            border-bottom: 1px solid var(--border);
            padding-bottom: 6mm;
        }

        .logo-box {
            width: 30mm;
            height: 30mm;
            border: 1px dashed var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 9px;
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
            font-size: 18px;
            letter-spacing: 0.4px;
        }

        .title p {
            margin: 2px 0;
            color: #374151;
        }

        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8mm;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 1.5mm 0;
            vertical-align: top;
        }

        .meta-table td:first-child {
            width: 34mm;
            color: var(--muted);
        }

        .items {
            flex: 1;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            border: 1px solid var(--border);
            padding: 2.5mm;
            vertical-align: top;
        }

        .items-table th {
            background: var(--bg-soft);
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.2px;
        }

        .col-no {
            width: 10%;
            text-align: center;
        }

        .col-item {
            width: 40%;
        }

        .col-period {
            width: 20%;
            text-align: center;
        }

        .col-amount {
            width: 30%;
            text-align: right;
            white-space: nowrap;
        }

        .totals {
            margin-top: 4mm;
            margin-left: auto;
            width: 80mm;
            border-collapse: collapse;
        }

        .totals td {
            border: 1px solid var(--border);
            padding: 2.2mm;
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
            padding-top: 6mm;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8mm;
            align-items: end;
        }

        .payment-info p {
            margin: 1.5mm 0;
        }

        .signature {
            justify-self: end;
            width: 60mm;
            text-align: center;
        }

        .signature .label {
            margin-bottom: 16mm;
            font-size: 10px;
            color: #374151;
        }

        .signature .line {
            border-top: 1px solid var(--border);
            padding-top: 1.5mm;
            font-weight: 700;
            font-size: 11px;
        }

        .signature .hint {
            margin-top: 1mm;
            font-size: 9px;
            color: var(--muted);
        }

        .status-badge {
            display: inline-block;
            padding: 1mm 3mm;
            font-size: 10px;
            font-weight: 700;
            border-radius: 2mm;
            text-transform: uppercase;
            letter-spacing: 0.3px;
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

        @media print {
            html,
            body {
                width: 100%;
                height: 100%;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body onload="window.print()">
    @php
        $schoolName = getSetting('school_name', 'MI SAKUMI');
        $schoolAddress = getSetting('school_address', 'Alamat sekolah belum diatur');
        $schoolPhone = getSetting('school_phone', '-');
        $schoolLogo = getSetting('school_logo', '');
    @endphp

    <main class="invoice">
        <section class="header">
            <div class="logo-box" aria-label="Header Logo">
                @if ($schoolLogo)
                    <img src="{{ asset('storage/' . $schoolLogo) }}" alt="Logo {{ $schoolName }}">
                @else
                    Header Logo
                @endif
            </div>
            <div class="title">
                <h1>TAGIHAN PEMBAYARAN</h1>
                <p><strong>{{ $schoolName }}</strong></p>
                <p>{{ $schoolAddress }}</p>
                <p>Telp: {{ $schoolPhone }}</p>
            </div>
        </section>

        <section class="meta">
            <table class="meta-table">
                <tr>
                    <td>No. Invoice</td>
                    <td>: {{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: {{ $invoice->invoice_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>Jatuh Tempo</td>
                    <td>: {{ $invoice->due_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>Periode</td>
                    <td>: <span style="text-transform:uppercase">{{ $invoice->period_type }}</span> — {{ $invoice->period_identifier }}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>: <span class="status-badge status-{{ $invoice->status }}">{{ str_replace('_', ' ', $invoice->status) }}</span></td>
                </tr>
            </table>

            <table class="meta-table">
                <tr>
                    <td>Nama Siswa</td>
                    <td>: {{ $invoice->student->name }}</td>
                </tr>
                <tr>
                    <td>NIS</td>
                    <td>: {{ $invoice->student->nis ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Kelas</td>
                    <td>: {{ $invoice->student->schoolClass->name ?? '-' }}</td>
                </tr>
            </table>
        </section>

        <section class="items">
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="col-no">No</th>
                        <th class="col-item">Item Tagihan</th>
                        <th class="col-period">Periode</th>
                        <th class="col-amount">Jumlah</th>
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
                            <td colspan="4" style="text-align:center; color: #6b7280;">Tidak ada item tagihan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <table class="totals">
                <tr>
                    <td>Total Tagihan</td>
                    <td>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Sudah Dibayar</td>
                    <td>Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Sisa Tagihan</td>
                    <td>Rp {{ number_format($invoice->outstanding, 0, ',', '.') }}</td>
                </tr>
            </table>
        </section>

        <section class="footer">
            <div class="payment-info">
                @if($invoice->notes)
                    <p><strong>Catatan:</strong> {{ $invoice->notes }}</p>
                @endif
                <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
            <div class="signature">
                <div class="label">Bendahara Sekolah</div>
                <div class="line">{{ $invoice->creator->name ?? 'Administration' }}</div>
                <p class="hint">Ditandatangani secara digital</p>
            </div>
        </section>
    </main>
</body>

</html>
