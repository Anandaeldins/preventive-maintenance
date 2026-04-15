<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --blue: #2563eb;
            --blue-light: #60a5fa;
            --bg: #eff6ff;
            --card: #ffffff;
            --text: #1e293b;
            --muted: #64748b;
            --gradient: linear-gradient(135deg, #60a5fa, #2563eb);
        }

        body.dark {
            --bg: #0f172a;
            --card: #020617;
            --text: #e5e7eb;
            --muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Inter, Poppins, system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            transition: 0.4s ease;
        }

        /* ===== HEADER ===== */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 64px;
            background: linear-gradient(90deg, #f6faff, #eaf2ff);
            border-bottom: 2px solid rgba(37, 99, 235, 0.35);
            position: relative;
        }

        /* ===== LOGO ===== */
        .logo-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(37, 99, 235, 0.25);
        }

        .logo-circle img {
            width: 75%;
            height: 75%;
            object-fit: contain;
        }

        /* ===== MENU ===== */
        .menu {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 28px;
        }

        .menu a {
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 999px;
            background: var(--gradient);
            color: #ffffff;
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 10px 26px rgba(37, 99, 235, 0.35);
            transition: 0.3s ease;
        }

        .menu a:hover {
            transform: translateY(-3px) scale(1.03);
        }

        .menu a.active {
            background: linear-gradient(135deg, #2563eb, #1e40af);
        }

        /* ===== RIGHT AREA ===== */
        .right-area {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .toggle {
            width: 44px;
            height: 24px;
            background: #cbd5f5;
            border-radius: 20px;
            position: relative;
            cursor: pointer;
        }

        .toggle::before {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 18px;
            height: 18px;
            background: #ffffff;
            border-radius: 50%;
            transition: 0.3s;
        }

        body.dark .toggle {
            background: #334155;
        }

        body.dark .toggle::before {
            left: 23px;
        }

        /* ===== PROFILE ===== */
        .profile-wrapper {
            position: relative;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .profile i {
            font-size: 22px;
            color: #3b82f6;
        }

        .profile-dropdown {
            position: absolute;
            top: 48px;
            right: 0;
            width: 240px;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(37, 99, 235, 0.25);
            padding: 10px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: 0.25s ease;
            z-index: 999;
        }

        .profile-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .profile-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            color: var(--text);
            text-decoration: none;
        }

        .profile-dropdown a:hover {
            background: rgba(37, 99, 235, 0.08);
        }

        .logout-btn {
            width: 100%;
            background: none;
            border: none;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #dc2626;
            font-size: 14px;
            cursor: pointer;
            text-align: left;
        }

        .logout-btn:hover {
            background: rgba(220, 38, 38, 0.08);
        }

        /* ===== CONTAINER ===== */
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 56px 64px;
        }

        footer {
            text-align: center;
            padding: 48px 0 16px;
            color: var(--muted);
        }

        @media (max-width: 900px) {
            header {
                padding: 16px 24px;
            }

            .menu {
                position: static;
                transform: none;
                margin: 16px 0;
            }
        }
    </style>

    @stack('style')
</head>

<body>


    <header>
        <div class="logo-circle">
            <img src="{{ asset('assets/image/logo_pgn.png') }}" alt="Logo">
        </div>
        <div class="menu">
            @auth
                @php $role = auth()->user()->role; @endphp

                @if ($role == 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                        class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">Beranda</a>
                @elseif($role == 'teknisi')
                    <a href="{{ route('teknisi.dashboard') }}"
                        class="{{ request()->is('teknisi/dashboard') ? 'active' : '' }}">Beranda</a>
                @elseif($role == 'kepala_ro')
                    <a href="{{ route('kepalaro.dashboard') }}"
                        class="{{ request()->is('approval/dashboard') ? 'active' : '' }}">Beranda</a>
                @elseif($role == 'pusat')
                    <a href="{{ route('pusat.dashboard') }}"
                        class="{{ request()->is('approval/pusat-reports') ? 'active' : '' }}">Beranda</a>
                @endif
            @endauth

            <a href="/admin/admin-fitur" class="{{ request()->is('admin/admin-fitur') ? 'active' : '' }}">
                Fitur
            </a>

            <a href="/tentang" class="{{ request()->is('tentang') ? 'active' : '' }}">
                Tentang
            </a>

            <a href="/bantuan" class="{{ request()->is('bantuan') ? 'active' : '' }}">
                Bantuan
            </a>
        </div>


        <div class="right-area">
            <div class="toggle" onclick="toggleDark()"></div>

            <div class="profile-wrapper">
                <div class="profile" onclick="toggleProfile()">
                    Profile <i class="bi bi-person-circle"></i>
                </div>

                <div class="profile-dropdown" id="profileDropdown">
                    @auth
                        @php $role = auth()->user()->role; @endphp

                        @if ($role == 'admin')
                            <a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                        @elseif($role == 'teknisi')
                            <a href="{{ route('teknisi.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                        @elseif($role == 'kepala_ro')
                            <a href="{{ route('kepalaro.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                        @elseif($role == 'pusat')
                            <a href="{{ route('pusat.dashboard') }}"><i class="bi bi-speedometer2"></i>
                                Dashboard</a>
                        @endif
                    @endauth
                    <a href="{{ route('account.index') }}"><i class="bi bi-person-gear"></i> Account Settings</a>
                    <a href="{{ route('settings.index') }}"><i class="bi bi-gear"></i> Pengaturan</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </header>

    <div class="container">
        @yield('content')
    </div>

    <footer>
        Sistem Preventive Maintenance • © {{ date('Y') }}
    </footer>

    <script>
        function toggleDark() {
            document.body.classList.toggle('dark');
        }

        function toggleProfile() {
            document.getElementById('profileDropdown').classList.toggle('show');
        }

        document.addEventListener('click', function(e) {
            const profile = document.querySelector('.profile-wrapper');
            if (!profile.contains(e.target)) {
                document.getElementById('profileDropdown').classList.remove('show');
            }
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')

</body>

</html>
