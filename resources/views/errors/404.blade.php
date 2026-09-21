<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Tidak Ditemukan (404) - ICM Sponsor Management</title>
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
            max-width: 32rem;
            padding: 2.5rem 2.25rem;
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
        .hero-numeral {
            display: flex;
            align-items: baseline;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }
        .hero-code {
            font-family: 'Sora', -apple-system, sans-serif;
            font-size: 4rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.04em;
            background: linear-gradient(135deg, #ffffff 40%, #ea7c1a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-divider {
            width: 2px;
            height: 2.25rem;
            background: rgba(234, 124, 26, 0.4);
            border-radius: 2px;
        }
        .hero-label {
            font-family: 'Sora', -apple-system, sans-serif;
            font-size: 1.125rem;
            font-weight: 700;
            color: #f8fafc;
            letter-spacing: -0.015em;
        }
        .subtitle {
            font-size: 0.845rem;
            color: #94a3b8;
            margin-bottom: 1.15rem;
        }
        .description {
            font-size: 0.875rem;
            line-height: 1.6;
            color: #cbd5e1;
            margin-bottom: 1.75rem;
        }
        .quick-nav {
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            background: rgba(11, 15, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.8125rem;
        }
        .quick-nav span {
            color: #94a3b8;
        }
        .quick-nav a {
            color: #ea7c1a;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: color 150ms ease;
        }
        .quick-nav a:hover {
            color: #f59e0b;
            text-decoration: underline;
        }
        .actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
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
        .btn-secondary svg {
            width: 0.95rem;
            height: 0.95rem;
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
                <span>Navigasi Sistem</span>
            </div>
            <span class="status-code">HTTP 404</span>
        </div>

        <div class="hero-numeral">
            <span class="hero-code">404</span>
            <span class="hero-divider"></span>
            <span class="hero-label">Halaman Tidak Ditemukan</span>
        </div>
        <p class="subtitle">Tautan yang Anda tuju tidak terdaftar di sistem</p>

        <p class="description">
            Alamat URL yang Anda tuju mungkin salah ketik, telah dipindahkan ke rute baru, atau hak akses Anda tidak mencakup halaman ini. Seluruh data sponsor Anda tetap aman di basis data.
        </p>

        <div class="quick-nav">
            <span>Perlu mencari data sponsor?</span>
            <a href="{{ url('/admin/kontaks') }}">
                Buka Kontak Sponsor
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="actions">
            <button type="button" class="btn-secondary" onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ url('/admin') }}'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                <span>Kembali</span>
            </button>
            <a href="{{ url('/admin') }}" class="btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <span>Dashboard Utama</span>
            </a>
        </div>
    </main>
</body>
</html>

