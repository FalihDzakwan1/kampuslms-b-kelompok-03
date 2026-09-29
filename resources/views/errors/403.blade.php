<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Ditolak | KampusLMS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8ecf4 100%);
            color: #334155;
        }
        .container {
            text-align: center;
            padding: 2rem;
            max-width: 480px;
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .code {
            font-size: 5rem;
            font-weight: 800;
            color: #dc2626;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1e293b;
        }
        p {
            font-size: 1rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔒</div>
        <div class="code">403</div>
        <h1>Akses Ditolak</h1>
        <p>
            Anda tidak memiliki izin untuk mengakses halaman ini. 
            Jika Anda yakin ini adalah kesalahan, silakan hubungi administrator.
        </p>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="btn">
            &larr; Kembali
        </a>
    </div>
</body>
</html>
