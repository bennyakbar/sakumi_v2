<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SAKUMI - Sistem Keuangan Sekolah</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        :root {
            --bg-1: #0b132b;
            --bg-2: #1c2541;
            --accent: #3a86ff;
            --accent-2: #2ec4b6;
            --text: #f8fafc;
            --muted: #cbd5e1;
            --card: rgba(255, 255, 255, 0.08);
            --line: rgba(255, 255, 255, 0.18);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background:
                radial-gradient(1200px 700px at 10% -10%, #3a86ff44, transparent),
                radial-gradient(900px 600px at 100% 0%, #2ec4b644, transparent),
                linear-gradient(140deg, var(--bg-1), var(--bg-2));
        }

        .wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 20px 40px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 48px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow: 0 0 0 6px #ffffff11;
        }

        .nav a {
            color: var(--text);
            text-decoration: none;
            font-size: 14px;
            padding: 8px 12px;
            border: 1px solid transparent;
            border-radius: 10px;
        }

        .nav a:hover { border-color: var(--line); }

        .hero {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 24px;
            align-items: stretch;
        }

        .panel {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            backdrop-filter: blur(4px);
            padding: 28px;
        }

        h1 {
            margin: 0;
            font-size: clamp(28px, 4vw, 44px);
            line-height: 1.1;
        }

        .lead {
            margin: 14px 0 0;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.6;
        }

        .actions {
            margin-top: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn {
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid transparent;
            transition: 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #4cc9f0);
            color: #fff;
        }

        .btn-ghost {
            color: var(--text);
            border-color: var(--line);
        }

        .btn:hover { transform: translateY(-1px); }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .stat {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 14px;
            background: #ffffff08;
        }

        .label {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .value {
            font-size: 16px;
            font-weight: 700;
        }

        .foot {
            margin-top: 26px;
            color: #cbd5e1;
            font-size: 12px;
        }

        @media (max-width: 900px) {
            .hero { grid-template-columns: 1fr; }
            .topbar { margin-bottom: 28px; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <header class="topbar">
            <div class="brand">
                <span class="dot"></span>
                <span>SAKUMI</span>
            </div>

            <nav class="nav">
                @auth
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a href="{{ route('login') }}">Login</a>
                @endauth
            </nav>
        </header>

        <main class="hero">
            <section class="panel">
                <h1>Sistem Keuangan Sekolah</h1>
                <p class="lead">
                    Kelola transaksi, invoice, settlement, laporan, dan master data sekolah dalam satu aplikasi.
                    Halaman ini sudah dikustomisasi dan tidak lagi menggunakan tampilan default Laravel.
                </p>

                <div class="actions">
                    @auth
                        <a class="btn btn-primary" href="{{ route('dashboard') }}">Masuk Dashboard</a>
                    @else
                        <a class="btn btn-primary" href="{{ route('login') }}">Masuk Aplikasi</a>
                    @endauth
                    <a class="btn btn-ghost" href="{{ url('/health/live') }}">Cek Status Sistem</a>
                </div>
            </section>

            <aside class="panel">
                <div class="stats">
                    <div class="stat">
                        <div class="label">Modul</div>
                        <div class="value">Transaksi, Invoice, Settlement</div>
                    </div>
                    <div class="stat">
                        <div class="label">Unit</div>
                        <div class="value">MI, RA, DTA</div>
                    </div>
                    <div class="stat">
                        <div class="label">Data Scope</div>
                        <div class="value">Isolasi per Unit</div>
                    </div>
                    <div class="stat">
                        <div class="label">Akses</div>
                        <div class="value">Role-based</div>
                    </div>
                </div>

                <div class="foot">
                    Laravel v{{ Illuminate\Foundation\Application::VERSION }} | PHP v{{ PHP_VERSION }}
                </div>
            </aside>
        </main>
    </div>
</body>
</html>
