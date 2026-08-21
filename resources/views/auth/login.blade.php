<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System | PT Kawai Indonesia</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bg-dark: #0b0f19;
            --card-glass: rgba(18, 24, 38, 0.85);
            --border-glass: rgba(255, 255, 255, 0.12);
            --accent-gold: #f59e0b;
            --accent-blue: #3b82f6;
        }

        body {
            background: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(245, 158, 11, 0.15) 0px, transparent 50%);
            color: #f3f4f6;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
        }

        .login-card {
            background: var(--card-glass);
            border: 1px solid var(--border-glass);
            border-radius: 24px;
            padding: 2.5rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .brand-icon {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: rgba(245, 158, 11, 0.12);
            border: 1.5px solid rgba(245, 158, 11, 0.3);
            color: var(--accent-gold);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            box-shadow: 0 0 25px rgba(245, 158, 11, 0.2);
        }

        .form-control-dark {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control-dark:focus {
            border-color: var(--accent-gold) !important;
            box-shadow: 0 0 0 0.25rem rgba(245, 158, 11, 0.2) !important;
        }

        .form-control-dark::placeholder {
            color: #64748b;
        }

        .input-group-text-dark {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #94a3b8;
            border-radius: 12px;
        }

        .btn-gold {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #0f172a;
            border: none;
            font-weight: 700;
            border-radius: 12px;
            padding: 0.8rem 1.5rem;
            transition: all 0.2s ease;
        }

        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.35);
            color: #000;
        }

        .quick-login-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 0.85rem 1rem;
            color: #cbd5e1;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
            margin-bottom: 0.5rem;
        }

        .quick-login-item:hover {
            background: rgba(59, 130, 246, 0.12);
            border-color: rgba(59, 130, 246, 0.4);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .quick-scroll {
            max-height: 220px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .quick-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .quick-scroll::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- HEADER -->
        <div class="text-center mb-4">
            <div class="brand-icon mb-3">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h3 class="fw-bold text-white mb-1" style="letter-spacing: 0.5px;">PT KAWAI INDONESIA</h3>
            <p class="text-muted small mb-0 fw-semibold">Sistem Purchasing &amp; Procurement RBAC Portal</p>
        </div>

        <!-- ERROR ALERT -->
        @if($errors->any())
            <div class="alert alert-danger bg-danger bg-opacity-25 text-white border border-danger border-opacity-50 rounded-3 mb-4 py-2 px-3 small d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success bg-success bg-opacity-25 text-white border border-success border-opacity-50 rounded-3 mb-4 py-2 px-3 small d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- FORM LOGIN -->
        <form action="{{ route('login.post') }}" method="POST" class="mb-4">
            @csrf
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">User Name / Email Perusahaan</label>
                <div class="input-group">
                    <span class="input-group-text input-group-text-dark border-end-0"><i class="bi bi-person-fill text-warning"></i></span>
                    <input type="text" name="login" class="form-control form-control-dark border-start-0 ps-0" placeholder="admin atau supervisor@kawai.co.id" value="{{ old('login') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Password</label>
                <div class="input-group">
                    <span class="input-group-text input-group-text-dark border-end-0"><i class="bi bi-key-fill text-warning"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control form-control-dark border-start-0 border-end-0 ps-0" placeholder="••••••••" required>
                    <button class="btn input-group-text-dark border-start-0" type="button" onclick="togglePasswordVisibility()">
                        <i class="bi bi-eye-fill" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input bg-dark border-secondary" type="checkbox" name="remember" id="rememberMe">
                    <label class="form-check-label text-muted small fw-semibold" for="rememberMe">
                        Ingat Saya (Remember Me)
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-gold w-100 d-flex align-items-center justify-content-center gap-2 shadow">
                <i class="bi bi-box-arrow-in-right fs-5"></i>
                <span>Masuk ke Sistem</span>
            </button>
        </form>

        <div class="text-center mt-4 pt-2">
            <a href="{{ route('dashboard.overview') }}" class="text-muted small text-decoration-none hover-white d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard (Public View)
            </a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
            }
        }
    </script>
</body>
</html>
