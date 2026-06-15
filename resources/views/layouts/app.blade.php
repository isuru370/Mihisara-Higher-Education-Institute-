<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', 'Minipalasa Education System')
    </title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        :root {

            --primary: #2563eb;
            --primary-dark: #1d4ed8;

            --secondary: #64748b;

            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #0ea5e9;

            --dark: #0f172a;
            --dark-light: #1e293b;

            --light: #f8fafc;

            --sidebar-gradient:
                linear-gradient(180deg,
                    #0f172a 0%,
                    #111827 50%,
                    #1e293b 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {

            font-family: 'Inter', sans-serif;

            background:
                linear-gradient(135deg,
                    #f8fafc,
                    #e2e8f0);

            color: #1e293b;

            min-height: 100vh;

            overflow-x: hidden;
        }

        /* APP WRAPPER */

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */

        .sidebar {

            width: 280px;

            background: var(--sidebar-gradient);

            position: fixed;

            left: 0;
            top: 0;
            bottom: 0;

            z-index: 1030;

            transition: all 0.3s ease;

            box-shadow:
                6px 0 25px rgba(0, 0, 0, 0.25);

            border-right:
                1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-header {

            padding: 1.5rem 1.25rem;

            border-bottom:
                1px solid rgba(255, 255, 255, 0.08);
        }

        .brand {

            display: flex;
            align-items: center;
            gap: 14px;

            text-decoration: none;
        }

        .brand-icon {

            width: 48px;
            height: 48px;

            border-radius: 14px;

            background:
                linear-gradient(135deg,
                    #3b82f6,
                    #2563eb);

            display: flex;
            align-items: center;
            justify-content: center;

            color: white;

            font-size: 1.4rem;
            font-weight: 800;

            box-shadow:
                0 10px 20px rgba(37, 99, 235, 0.35);
        }

        .brand-text h4 {

            margin: 0;

            color: white;

            font-size: 1.2rem;
            font-weight: 700;
        }

        .brand-text small {

            color: #94a3b8;

            font-size: 0.75rem;
        }

        /* NAVIGATION */

        .sidebar-nav {
            padding: 1.5rem 1rem;
        }

        .nav-item {
            margin-bottom: 0.35rem;
        }

        .nav-link-custom {

            display: flex;
            align-items: center;
            gap: 14px;

            padding: 0.9rem 1rem;

            border-radius: 14px;

            color: #cbd5e1;

            text-decoration: none;

            font-size: 0.95rem;
            font-weight: 500;

            transition: all 0.25s ease;
        }

        .nav-link-custom i {

            font-size: 1.2rem;

            width: 24px;
        }

        .nav-link-custom:hover {

            background:
                rgba(255, 255, 255, 0.08);

            color: white;

            transform: translateX(5px);
        }

        .nav-link-custom.active {

            background:
                linear-gradient(135deg,
                    #2563eb,
                    #1d4ed8);

            color: white;

            box-shadow:
                0 10px 18px rgba(37, 99, 235, 0.30);
        }

        /* MAIN CONTENT */

        .main-content {

            flex: 1;

            margin-left: 280px;

            min-width: 0;
        }

        /* TOP NAVBAR */

        .top-navbar {

            background:
                rgba(255, 255, 255, 0.80);

            backdrop-filter: blur(12px);

            border-bottom:
                1px solid #e2e8f0;

            padding: 1rem 2rem;

            position: sticky;

            top: 0;

            z-index: 1020;

            display: flex;
            align-items: center;
            justify-content: space-between;

            box-shadow:
                0 4px 20px rgba(0, 0, 0, 0.04);
        }

        .page-title h1 {

            margin: 0;

            font-size: 1.5rem;
            font-weight: 700;

            color: #0f172a;
        }

        .page-title p {

            margin: 0;

            font-size: 0.85rem;

            color: #64748b;
        }

        /* USER MENU */

        .user-menu {

            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-dropdown {

            display: flex;
            align-items: center;
            gap: 12px;

            padding: 0.5rem;

            border-radius: 50px;

            transition: 0.2s ease;

            cursor: pointer;
        }

        .user-dropdown:hover {
            background: #f1f5f9;
        }

        .user-avatar {

            width: 45px;
            height: 45px;

            border-radius: 50%;

            background:
                linear-gradient(135deg,
                    #2563eb,
                    #60a5fa);

            display: flex;
            align-items: center;
            justify-content: center;

            color: white;

            font-weight: 700;

            box-shadow:
                0 8px 18px rgba(37, 99, 235, 0.25);
        }

        .user-info {
            text-align: right;
        }

        .user-info .name {

            font-size: 0.9rem;
            font-weight: 700;

            color: #1e293b;
        }

        .user-info .role {

            font-size: 0.75rem;

            color: #64748b;
        }

        /* CONTENT AREA */

        .content-area {

            padding: 2rem;

            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {

            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ALERTS */

        .alert {

            border: none;

            border-radius: 14px;

            padding: 1rem 1.25rem;

            font-weight: 500;

            box-shadow:
                0 6px 18px rgba(0, 0, 0, 0.05);
        }

        /* MOBILE */

        .mobile-toggle {

            display: none;

            background: none;
            border: none;

            font-size: 1.5rem;

            color: #334155;

            cursor: pointer;
        }

        /* RESPONSIVE */

        @media(max-width: 991px) {

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .content-area {
                padding: 1rem;
            }

            .top-navbar {
                padding: 1rem;
            }

            .mobile-toggle {
                display: block;
            }
        }

        /* SCROLLBAR */

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-thumb {

            background:
                linear-gradient(180deg,
                    #2563eb,
                    #1d4ed8);

            border-radius: 20px;
        }

        ::-webkit-scrollbar-track {
            background: #e2e8f0;
        }
    </style>

    @stack('styles')

</head>

<body>

    <div class="app-wrapper">

        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main -->
        <main class="main-content">

            <!-- Header -->
            @include('layouts.header')

            <!-- Content -->
            <div class="content-area">

                <!-- Success Alert -->
                @if(session('success'))

                    <div class="alert alert-success alert-dismissible fade show" role="alert">

                        <i class="bi bi-check-circle-fill me-2"></i>

                        {{ session('success') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert">
                        </button>

                    </div>

                @endif

                <!-- Error Alert -->
                @if(session('error'))

                    <div class="alert alert-danger alert-dismissible fade show" role="alert">

                        <i class="bi bi-exclamation-triangle-fill me-2"></i>

                        {{ session('error') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert">
                        </button>

                    </div>

                @endif

                <!-- Page Content -->
                @yield('content')

            </div>

        </main>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>

        // Mobile Sidebar Toggle

        const mobileToggle =
            document.getElementById('mobileToggle');

        const sidebar =
            document.getElementById('sidebar');

        if (mobileToggle && sidebar) {

            mobileToggle.addEventListener('click', function () {

                sidebar.classList.toggle('show');

            });

            document.addEventListener('click', function (event) {

                const isInside =
                    sidebar.contains(event.target);

                const isToggle =
                    mobileToggle.contains(event.target);

                if (
                    !isInside &&
                    !isToggle &&
                    window.innerWidth <= 991 &&
                    sidebar.classList.contains('show')
                ) {

                    sidebar.classList.remove('show');
                }
            });
        }

        // Auto Close Alerts

        document.querySelectorAll(
            '.alert:not([data-persist-alert="true"])'
        ).forEach(alert => {

            setTimeout(() => {

                const bsAlert =
                    bootstrap.Alert.getOrCreateInstance(alert);

                bsAlert.close();

            }, 5000);

        });

    </script>

    @stack('scripts')

</body>

</html>