<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sesi Telah Berakhir (419) - ICM Sponsor Management</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-icm-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('fonts/sora/sora-latin-wght-normal.woff2') }}">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #0b0f2a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .aurora-backdrop {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(60rem 32rem at 50% -8rem, rgba(234, 124, 26, 0.28), transparent 60%),
                radial-gradient(45rem 30rem at 20% 80%, rgba(24, 34, 94, 0.85), transparent 70%),
                radial-gradient(50rem 35rem at 85% 65%, rgba(59, 73, 184, 0.5), transparent 65%),
                #0b0f2a;
            pointer-events: none;
            z-index: 1;
        }
        .error-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 30rem;
            padding: 2.25rem 2rem;
            background: rgba(16, 25, 53, 0.85);
            backdrop-filter: blur(24px) saturate(190%);
            -webkit-backdrop-filter: blur(24px) saturate(190%);
            border: 1px solid rgba(234, 124, 26, 0.28);
            border-radius: 1.25rem;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.7), 0 0 35px -5px rgba(234, 124, 26, 0.15);
            animation: fadeInScale 240ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .badge-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            background: rgba(234, 124, 26, 0.12);
            color: #ea7c1a;
            border: 1px solid rgba(234, 124, 26, 0.3);
        }
        .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #ea7c1a;
            box-shadow: 0 0 6px #ea7c1a;
        }
        .status-code {
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            background: rgba(148, 163, 184, 0.1);
            padding: 0.2rem 0.6rem;
            border-radius: 0.375rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }
        .hero {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.15rem;
        }
        .icon-wrap {
            width: 3rem;
            height: 3rem;
            border-radius: 0.85rem;
            background: rgba(234, 124, 26, 0.15);
            border: 1px solid rgba(234, 124, 26, 0.3);
            color: #ea7c1a;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .icon-wrap svg {
            width: 1.5rem;
            height: 1.5rem;
        }
        h1 {
            font-family: 'Sora', -apple-system, sans-serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.25;
            letter-spacing: -0.02em;
        }
        .subtitle {
            font-size: 0.845rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }
        .description {
            font-size: 0.875rem;
            line-height: 1.6;
            color: #cbd5e1;
            margin-bottom: 1.75rem;
        }
        .actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        .btn-secondary {
            padding: 0.6rem 1.15rem;
            border-radius: 0.5rem;
            font-size: 0.845rem;
            font-weight: 500;
            color: #94a3b8;
            background: transparent;
            border: 1px solid #334155;
            text-decoration: none;
            transition: all 150ms ease;
            cursor: pointer;
        }
        .btn-secondary:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
            border-color: #64748b;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.35rem;
            border-radius: 0.5rem;
            font-size: 0.845rem;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(135deg, #18225e 0%, #25348c 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 14px rgba(24, 34, 94, 0.4);
            cursor: pointer;
            text-decoration: none;
            transition: all 160ms ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #ea7c1a 0%, #f59e0b 100%);
            box-shadow: 0 4px 18px rgba(234, 124, 26, 0.4);
            transform: translateY(-1px);
        }
        .btn-primary svg {
            width: 1rem;
            height: 1rem;
        }
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(8px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="aurora-backdrop"></div>
    <main class="error-card">
        <div class="badge-row">
            <div class="security-badge">
                <span class="badge-dot"></span>
                <span>Protokol Keamanan</span>
            </div>
            <span class="status-code">HTTP 419</span>
        </div>

        <div class="hero">
            <div class="icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
            </div>
            <div>
                <h1>Sesi Anda Telah Berakhir</h1>
                <p class="subtitle">Token autentikasi formulir telah kedaluwarsa</p>
            </div>
        </div>

        <p class="description">
            Demi menjaga integritas transaksi dan melindungi kerahasiaan data sponsor ICM, token CSRF Anda telah disegarkan secara otomatis. Silakan muat ulang halaman untuk memperbarui token dan melanjutkan aktivitas Anda.
        </p>

        <div class="actions">
            <a href="{{ url('/admin/login') }}" class="btn-secondary">
                Kembali ke Login
            </a>
            <button type="button" class="btn-primary" onclick="window.location.reload()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                </svg>
                <span>Muat Ulang Halaman</span>
            </button>
        </div>
    </main>
</body>
</html>

