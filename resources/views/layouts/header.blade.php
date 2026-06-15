<nav class="top-navbar">

    <!-- LEFT SIDE -->
    <div class="d-flex align-items-center gap-3">

        <!-- MOBILE TOGGLE -->
        <button class="mobile-toggle" id="mobileToggle">

            <i class="bi bi-list"></i>

        </button>

        <!-- PAGE TITLE -->
        <div class="page-title">

            <h1>
                @yield('page-title', 'Dashboard')
            </h1>

            <p>
                Welcome back,
                <strong>{{ auth()->user()->name }}</strong>
                👋
            </p>

        </div>

    </div>

    <!-- RIGHT SIDE -->
    <div class="user-menu">

        <!-- NOTIFICATION -->
        <div class="notification-btn position-relative">

            <button class="btn notification-button">

                <i class="bi bi-bell"></i>

                <span class="notification-badge">
                    0
                </span>

            </button>

        </div>

        <!-- USER DROPDOWN -->
        <div class="dropdown">

            <div class="user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">

                <!-- USER INFO -->
                <div class="user-info d-none d-sm-block">

                    <div class="name">
                        {{ auth()->user()->name }}
                    </div>

                    <div class="role">
                        Administrator
                    </div>

                </div>

                <!-- USER AVATAR -->
                <div class="user-avatar">

                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}

                </div>

            </div>

            <!-- DROPDOWN -->
            <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu border-0">

                <li class="dropdown-header">

                    <div class="fw-bold">
                        {{ auth()->user()->name }}
                    </div>

                    <small class="text-muted">
                        Administrator Account
                    </small>

                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <!-- PROFILE -->
                <li>

                    <a class="dropdown-item" href="{{ route('admin.profile.index') }}">

                        <i class="bi bi-person-circle me-2"></i>

                        My Profile

                    </a>

                </li>

                <!-- SETTINGS -->
                <li>

                    <a class="dropdown-item" href="{{ route('admin.setting.index') }}">

                        <i class="bi bi-gear me-2"></i>

                        Settings

                    </a>

                </li>

                <!-- HELP -->
                <li>

                    <a class="dropdown-item" href="#">

                        <i class="bi bi-question-circle me-2"></i>

                        Help Center

                    </a>

                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <!-- LOGOUT -->
                <li>

                    <form method="POST" action="{{ route('logout') }}">

                        @csrf

                        <button type="submit" class="dropdown-item text-danger">

                            <i class="bi bi-box-arrow-right me-2"></i>

                            Logout

                        </button>

                    </form>

                </li>

            </ul>

        </div>

    </div>

</nav>

<style>
    /* TOP NAVBAR */

    .top-navbar {

        background:
            rgba(255, 255, 255, 0.80);

        backdrop-filter: blur(14px);

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

    /* PAGE TITLE */

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
        gap: 1rem;
    }

    /* NOTIFICATION BUTTON */

    .notification-button {

        width: 45px;
        height: 45px;

        border: none;

        border-radius: 50%;

        background: white;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 1.1rem;

        color: #334155;

        position: relative;

        transition: 0.25s ease;

        box-shadow:
            0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .notification-button:hover {

        background: #eff6ff;

        color: #2563eb;

        transform: translateY(-2px);
    }

    /* BADGE */

    .notification-badge {

        position: absolute;

        top: -3px;
        right: -2px;

        width: 18px;
        height: 18px;

        background: #ef4444;

        color: white;

        border-radius: 50%;

        font-size: 0.7rem;
        font-weight: 600;

        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* USER DROPDOWN */

    .user-dropdown {

        display: flex;
        align-items: center;
        gap: 12px;

        padding: 0.45rem 0.7rem;

        border-radius: 50px;

        cursor: pointer;

        transition: 0.25s ease;
    }

    .user-dropdown:hover {

        background: #f1f5f9;
    }

    /* USER AVATAR */

    .user-avatar {

        width: 46px;
        height: 46px;

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

    /* USER INFO */

    .user-info {
        text-align: right;
    }

    .user-info .name {

        font-size: 0.92rem;
        font-weight: 700;

        color: #1e293b;
    }

    .user-info .role {

        font-size: 0.75rem;

        color: #64748b;
    }

    /* DROPDOWN MENU */

    .user-dropdown-menu {

        width: 240px;

        border-radius: 18px;

        padding: 0.7rem;

        margin-top: 12px;

        box-shadow:
            0 12px 30px rgba(0, 0, 0, 0.08);
    }

    .dropdown-header {

        padding: 0.5rem 0.75rem;
    }

    .dropdown-item {

        border-radius: 10px;

        padding: 0.75rem 1rem;

        font-size: 0.92rem;

        transition: 0.2s ease;
    }

    .dropdown-item:hover {

        background: #eff6ff;

        color: #2563eb;
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

    @media(max-width: 991px) {

        .mobile-toggle {
            display: block;
        }

        .top-navbar {
            padding: 1rem;
        }

        .page-title h1 {
            font-size: 1.2rem;
        }

        .user-info {
            display: none;
        }
    }
</style>