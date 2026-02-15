<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kwitansi - {{ $transaction->transaction_number }}</title>
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
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            font-size: 11px;
            line-height: 1.35;
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
            font-weight: 700;
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
        $school_name = $school_name ?? 'MI NURUL FALAH';
        $school_address = $school_address ?? 'Komplek Sukamenak Indah Blok G No.4A, RT.06/RW.01, Sayati, Margahayu, Sayati, Kec. Margahayu, Kabupaten Bandung, Jawa Barat 40228';
        $school_phone = $school_phone ?? '-';
    @endphp

    <main class="receipt">
        @if (!empty($cancelled))
            <div class="watermark">CANCELLED</div>
        @endif

        <section class="header">
            <div class="header-left">
                <div class="logo-box">
                    @if (!empty($show_logo) && !empty($school_logo))
                        <img src="{{ public_path('storage/' . $school_logo) }}" alt="Logo">
                    @elseif (file_exists(public_path('images/kwitansi-logo.png')))
                        <img src="{{ public_path('images/kwitansi-logo.png') }}" alt="Logo">
                    @else
                        Logo
                    @endif
                </div>
            </div>
            <div class="header-right">
                <h1 class="title">KWITANSI PEMBAYARAN</h1>
                <div><strong>{{ $school_name }}</strong></div>
                <div>{{ $school_address ?: '-' }}</div>
                <div>Telp: {{ $school_phone ?: '-' }}</div>
            </div>
        </section>

        <table class="meta">
            <tr>
                <td>
                    <table class="meta-table">
                        <tr>
                            <td>No. Transaksi</td>
                            <td>: {{ $transaction->transaction_number }}</td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td>: {{ $transaction->transaction_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td>Metode Bayar</td>
                            <td>: {{ ucfirst($transaction->payment_method ?? '-') }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="meta-table">
                        <tr>
                            <td>Nama Siswa</td>
                            <td>: {{ $transaction->student->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Kelas</td>
                            <td>: {{ $transaction->student->schoolClass->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Petugas</td>
                            <td>: {{ $transaction->creator->name ?? 'System' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th class="text-center" style="width:10%;">No</th>
                    <th style="width:45%;">Item Transaksi</th>
                    <th style="width:25%;">Keterangan</th>
                    <th class="text-right" style="width:20%;">Jumlah</th>
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
                        <td colspan="4" class="text-center">Tidak ada item transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="total">
            <tr>
                <td>Total Pembayaran</td>
                <td>Rp {{ number_format((float) $transaction->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>

        <section class="footer">
            <div class="signature">
                <div class="label">Digital Signature<br>Admin TU</div>
                <div class="line">{{ $transaction->creator->name ?? 'Administration Admin' }}</div>
                <div class="hint">Ditandatangani secara digital</div>
            </div>
        </section>
    </main>
</body>

</html>
