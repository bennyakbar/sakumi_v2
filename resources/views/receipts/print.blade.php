<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi - {{ $transaction->transaction_number }}</title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --line: #cbd5e1;
            --soft: #f8fafc;
            --brand-1: #0b3a74;
            --brand-2: #1558aa;
            --brand-3: #e8f1ff;
            --ok: #0f766e;
        }

        * {
            box-sizing: border-box;
        }

        @page {
            size: A5 landscape;
            margin: 6mm;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            color: var(--ink);
            background: #ffffff;
            font-size: 10px;
            line-height: 1.25;
        }

        .sheet {
            min-height: 136mm;
            max-height: 136mm;
            border: 1px solid #94a3b8;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            padding: 2.5mm 4mm;
            color: #fff;
            background: linear-gradient(120deg, var(--brand-1), var(--brand-2));
            display: grid;
            grid-template-columns: 14mm 1fr 14mm;
            align-items: center;
            gap: 3mm;
        }

        .head-center {
            min-width: 0;
            text-align: center;
        }

        .logo-wrap {
            width: 14mm;
            height: 14mm;
            border-radius: 999px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 1px solid #ffffff55;
            flex: 0 0 auto;
        }

        .logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .logo-fallback {
            font-size: 7px;
            color: var(--brand-1);
            font-weight: 700;
            text-align: center;
        }

        .head-center .name {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .head-center .address {
            font-size: 8.5px;
            opacity: 0.92;
            white-space: normal;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            line-height: 1.2;
            max-height: 2.4em;
        }

        .doc-title {
            margin: 0;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.7px;
        }

        .doc-sub {
            margin: 0.5mm 0 0;
            font-size: 8.5px;
            opacity: 0.9;
        }

        .body {
            padding: 3.5mm 4mm 3mm;
            display: flex;
            flex-direction: column;
            gap: 2.5mm;
            flex: 1;
            min-height: 0;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 2mm;
        }

        .meta-card {
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 1.8mm 2.2mm;
        }

        .meta-label {
            font-size: 8px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 0.8mm;
        }

        .meta-value {
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table-wrap {
            border: 1px solid var(--line);
            border-radius: 6px;
            overflow: hidden;
            flex: 1;
            min-height: 0;
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border-bottom: 1px solid #e2e8f0;
            padding: 1.4mm 1.8mm;
            vertical-align: top;
        }

        th {
            background: var(--brand-3);
            color: #0c3f7f;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            font-weight: 700;
        }

        tbody tr:nth-child(even) {
            background: #fcfdff;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .c-no {
            width: 8%;
            text-align: center;
        }

        .c-item {
            width: 46%;
        }

        .c-note {
            width: 24%;
        }

        .c-amt {
            width: 22%;
            text-align: right;
            white-space: nowrap;
        }

        .item-name {
            font-weight: 600;
        }

        .item-note {
            color: #475569;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 0;
        }

        .compact th,
        .compact td {
            padding-top: 1.1mm;
            padding-bottom: 1.1mm;
            font-size: 9px;
        }

        .summary-row td {
            background: #f8fafc;
            color: #334155;
            font-style: italic;
        }

        .footer {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 3mm;
            align-items: end;
        }

        .foot-note {
            color: var(--muted);
            font-size: 8.2px;
        }

        .total-box {
            min-width: 56mm;
            border: 1px solid #93c5fd;
            border-radius: 6px;
            overflow: hidden;
        }

        .total-head {
            background: #eff6ff;
            padding: 1.3mm 2mm;
            font-size: 8px;
            color: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 700;
        }

        .total-value {
            padding: 2mm;
            text-align: right;
            font-size: 14px;
            font-weight: 800;
            color: var(--ok);
        }

        .stamp {
            margin-top: 0.7mm;
            text-align: right;
            font-size: 7.8px;
            color: var(--muted);
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
        $schoolName = getSetting('school_name', 'MI NURUL FALAH');
        $schoolAddress = getSetting('school_address', 'Komplek Sukamenak Indah Blok G No.4A, RT.06/RW.01, Sayati, Margahayu, Sayati, Kec. Margahayu, Kabupaten Bandung, Jawa Barat 40228');
        $schoolPhone = getSetting('school_phone', '-');
        $foundationLogo = getSetting('foundation_logo', '');
        $foundationLogoPath = public_path('images/logo-yayasan.png');
        $hasFoundationLogo = file_exists($foundationLogoPath);
        $schoolLogo = getSetting('school_logo', '');
        $defaultLogoPath = public_path('images/kwitansi-logo.png');
        $hasDefaultLogo = file_exists($defaultLogoPath);

        $items = $transaction->items;
        $itemCount = $items->count();
        $maxRows = 18;
        $displayItems = $items->take($maxRows);
        $remainingCount = max($itemCount - $maxRows, 0);
        $remainingAmount = $remainingCount > 0 ? (float) $items->slice($maxRows)->sum('amount') : 0;
        $compactMode = $itemCount > 10;
    @endphp

    <main class="sheet">
        <section class="topbar">
            <div class="logo-wrap" aria-label="Logo Yayasan">
                @if ($foundationLogo)
                    <img src="{{ asset('storage/' . $foundationLogo) }}" alt="Logo Yayasan">
                @elseif ($hasFoundationLogo)
                    <img src="{{ asset('images/logo-yayasan.png') }}" alt="Logo Yayasan">
                @else
                    <div class="logo-fallback">YYS</div>
                @endif
            </div>

            <div class="head-center">
                <div class="name">{{ $schoolName }}</div>
                <div class="address">{{ $schoolAddress }}</div>
                <h1 class="doc-title">KWITANSI PEMBAYARAN</h1>
            </div>

            <div class="logo-wrap" aria-label="Logo MI">
                @if ($schoolLogo)
                    <img src="{{ asset('storage/' . $schoolLogo) }}" alt="Logo {{ $schoolName }}">
                @elseif ($hasDefaultLogo)
                    <img src="{{ asset('images/kwitansi-logo.png') }}" alt="Logo {{ $schoolName }}">
                @else
                    <div class="logo-fallback">MI</div>
                @endif
            </div>
        </section>

        <section class="body">
            <div class="meta-grid">
                <div class="meta-card">
                    <div class="meta-label">No. Kwitansi</div>
                    <div class="meta-value">{{ $transaction->transaction_number }}</div>
                </div>
                <div class="meta-card">
                    <div class="meta-label">Tanggal Bayar</div>
                    <div class="meta-value">{{ $transaction->transaction_date->format('d/m/Y') }}</div>
                </div>
                <div class="meta-card">
                    <div class="meta-label">Metode</div>
                    <div class="meta-value">{{ strtoupper($transaction->payment_method ?? '-') }}</div>
                </div>
            </div>

            <div class="meta-grid">
                <div class="meta-card">
                    <div class="meta-label">Nama Siswa</div>
                    <div class="meta-value">{{ $transaction->student->name ?? '-' }}</div>
                </div>
                <div class="meta-card">
                    <div class="meta-label">Kelas</div>
                    <div class="meta-value">{{ $transaction->student->schoolClass->name ?? '-' }}</div>
                </div>
                <div class="meta-card">
                    <div class="meta-label">Petugas</div>
                    <div class="meta-value">{{ $transaction->creator->name ?? 'SYSTEM' }}</div>
                </div>
            </div>

            <div class="table-wrap {{ $compactMode ? 'compact' : '' }}">
                <table>
                    <thead>
                        <tr>
                            <th class="c-no">No</th>
                            <th class="c-item">Uraian Transaksi</th>
                            <th class="c-note">Keterangan</th>
                            <th class="c-amt">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($displayItems as $index => $item)
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
                                <td colspan="4" style="text-align:center; color:#64748b;">Tidak ada item transaksi.</td>
                            </tr>
                        @endforelse

                        @if ($remainingCount > 0)
                            <tr class="summary-row">
                                <td class="c-no">+</td>
                                <td colspan="2">{{ $remainingCount }} item tambahan diringkas agar tetap 1 lembar</td>
                                <td class="c-amt">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <section class="footer">
                <div class="foot-note">
                    Dokumen ini merupakan bukti pembayaran resmi sekolah.<br>
                    Dicetak: {{ now()->format('d/m/Y H:i') }}
                </div>
                <div>
                    <div class="total-box">
                        <div class="total-head">Total Pembayaran</div>
                        <div class="total-value">Rp {{ number_format((float) $transaction->total_amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="stamp">Digital Signature: {{ $transaction->creator->name ?? 'Administration Admin' }}</div>
                </div>
            </section>
        </section>
    </main>
</body>

</html>
