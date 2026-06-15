<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINIPALASA HIGHER EDUCATION INSTITUTE · welcome</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;1,14..32,300&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-0: #04111b;
            --bg-1: #071b2a;
            --bg-2: #0a2235;
            --panel: rgba(8, 25, 38, 0.58);
            --panel-strong: rgba(10, 30, 45, 0.82);
            --border: rgba(167, 243, 208, 0.14);
            --border-strong: rgba(125, 211, 252, 0.22);
            --text-main: #f8fcff;
            --text-soft: #bfd4df;
            --text-dim: #8fb0bf;
            --blue: #38bdf8;
            --cyan: #22d3ee;
            --teal: #14b8a6;
            --emerald: #34d399;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            color: var(--text-main);
            background:
                radial-gradient(circle at 18% 12%, rgba(34, 211, 238, 0.18), transparent 28%),
                radial-gradient(circle at 82% 18%, rgba(56, 189, 248, 0.16), transparent 26%),
                radial-gradient(circle at 80% 84%, rgba(20, 184, 166, 0.18), transparent 25%),
                linear-gradient(145deg, var(--bg-0) 0%, var(--bg-1) 50%, var(--bg-2) 100%);
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,0.9), rgba(0,0,0,0.55));
            pointer-events: none;
        }

        .ambient {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }

        .orb {
            position: absolute;
            border-radius: 999px;
            filter: blur(92px);
            opacity: 0.42;
            animation: drift 12s ease-in-out infinite;
        }

        .orb:nth-child(1) {
            width: 280px;
            height: 280px;
            top: 5%;
            left: -5%;
            background: rgba(34, 211, 238, 0.28);
        }

        .orb:nth-child(2) {
            width: 240px;
            height: 240px;
            top: 14%;
            right: -4%;
            background: rgba(56, 189, 248, 0.24);
            animation-delay: 2s;
        }

        .orb:nth-child(3) {
            width: 180px;
            height: 180px;
            bottom: 8%;
            left: 8%;
            background: rgba(20, 184, 166, 0.18);
            animation-delay: 4s;
        }

        .orb:nth-child(4) {
            width: 220px;
            height: 220px;
            bottom: -6%;
            right: 12%;
            background: rgba(52, 211, 153, 0.18);
            animation-delay: 1s;
        }

        .dashboard-shell {
            width: min(920px, calc(100% - 32px));
            z-index: 2;
            padding: 18px;
            border-radius: 34px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow:
                0 30px 70px rgba(0, 0, 0, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .container {
            position: relative;
            border-radius: 28px;
            padding: 2rem;
            background: linear-gradient(180deg, rgba(8, 24, 36, 0.88), rgba(6, 19, 29, 0.78));
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, 0.16), transparent 30%),
                radial-gradient(circle at top right, rgba(20, 184, 166, 0.12), transparent 26%),
                linear-gradient(135deg, rgba(255, 255, 255, 0.07), transparent 32%, transparent 68%, rgba(255, 255, 255, 0.03));
            pointer-events: none;
        }

        .container::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 28px;
            padding: 1px;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.28), rgba(20, 184, 166, 0.10), rgba(52, 211, 153, 0.18));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
            opacity: 0.8;
        }

        .topbar {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.8rem;
            flex-wrap: wrap;
        }

        .brand-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.85rem 1.1rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-soft);
            font-size: 0.92rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .brand-chip i {
            color: var(--cyan);
        }

        .status-group {
            display: flex;
            gap: 0.7rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.72rem 0.95rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-soft);
            font-size: 0.86rem;
            font-weight: 500;
        }

        .status-pill i {
            color: var(--emerald);
        }

        .logo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1.4rem;
            position: relative;
            z-index: 2;
        }

        .logo-container:hover {
            transform: translateY(-1px);
            transition: transform 0.25s ease;
        }

        .logo {
            max-width: 250px;
            width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            filter:
                drop-shadow(0 0 18px rgba(56, 189, 248, 0.26))
                drop-shadow(0 10px 24px rgba(0, 0, 0, 0.25));
        }

        .hero {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            text-align: center;
            align-items: center;
        }

        h1 {
            font-size: clamp(2rem, 4vw, 3.4rem);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -0.04em;
            color: var(--text-main);
        }

        .accent {
            background: linear-gradient(90deg, #67e8f9 0%, #38bdf8 38%, #14b8a6 72%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subline {
            color: var(--text-soft);
            font-size: 1rem;
            line-height: 1.7;
            max-width: 760px;
            margin: 0 auto;
        }

        .tagline {
            margin: 1rem auto 0;
            max-width: 680px;
            padding: 1rem 1.1rem;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.045);
            border: 1px solid rgba(255, 255, 255, 0.07);
            color: var(--text-soft);
            font-style: italic;
            line-height: 1.6;
        }

        .redirect-card {
            margin: 2rem auto 0;
            width: fit-content;
            padding: 1rem 1.2rem;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.70), rgba(14, 165, 233, 0.72));
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow:
                0 18px 32px rgba(0, 0, 0, 0.28),
                inset 0 1px 0 rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.10);
            position: relative;
            z-index: 2;
        }

        .redirect-card i {
            font-size: 1.3rem;
            color: white;
        }

        .redirect-text {
            font-size: 1rem;
            font-weight: 700;
            color: white;
            letter-spacing: 0.01em;
        }

        .countdown-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.15rem;
            padding-left: 0.2rem;
        }

        .countdown-badge {
            background: rgba(255, 255, 255, 0.96);
            color: #0f766e;
            font-weight: 800;
            font-size: 1.45rem;
            padding: 0.28rem 0.95rem;
            border-radius: 999px;
            min-width: 70px;
            text-align: center;
            line-height: 1;
        }

        .countdown-label {
            font-size: 0.82rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.82);
        }

        .meta-note {
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-dim);
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.7rem 1.1rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.045);
            border: 1px solid rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(10px);
        }

        .meta-note i {
            color: var(--cyan);
        }

        @keyframes drift {
            0%, 100% {
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-24px) scale(1.05);
            }
        }

        @media (max-width: 640px) {
            .dashboard-shell {
                width: calc(100% - 18px);
                padding: 10px;
                border-radius: 26px;
            }

            .container {
                padding: 1.35rem;
                border-radius: 20px;
            }

            .topbar {
                justify-content: center;
            }

            .status-group {
                justify-content: center;
            }

            .redirect-card {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                border-radius: 20px;
            }

            .countdown-badge {
                min-width: 84px;
                font-size: 1.65rem;
            }
        }
    </style>
</head>

<body>
    <div class="ambient">
        <div class="orb"></div>
        <div class="orb"></div>
        <div class="orb"></div>
        <div class="orb"></div>
    </div>

    <div class="dashboard-shell">
        <div class="container">
            <div class="topbar">
                <div class="brand-chip">
                    <i class="fas fa-layer-group"></i>
                    Premium dashboard welcome screen
                </div>
                <div class="status-group">
                    <div class="status-pill"><i class="fas fa-shield-halved"></i> Secure portal</div>
                    <div class="status-pill"><i class="fas fa-wifi"></i> Online</div>
                </div>
            </div>

            <div class="logo-container">
                <img src="{{ asset('storage/logo/logo.png') }}" alt="MINIPALASA HIGHER EDUCATION INSTITUTE logo" class="logo"
                    onerror="this.src='https://placehold.co/320x120/0f766e/ffffff?text=MINIPALASA+INSTITUTE&font=inter';">
            </div>

            <div class="hero">
                <h1>
                    Welcome to <span class="accent">MINIPALASA HIGHER EDUCATION INSTITUTE</span>
                </h1>

                <p class="subline">
                    A modern, secure, and premium learning environment designed for excellence, growth, and future-ready education.
                </p>

                <p class="tagline">
                    <i class="fas fa-quote-left" style="font-size:0.9rem; opacity:0.65; margin-right:6px;"></i>
                    Empowering minds, shaping futures through excellence in education
                    <i class="fas fa-quote-right" style="font-size:0.9rem; opacity:0.65; margin-left:6px;"></i>
                </p>

                <div class="redirect-card">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    <span class="redirect-text">Redirecting to login</span>
                    <div class="countdown-wrap">
                        <span class="countdown-badge" id="countdown">5</span>
                        <span class="countdown-label">seconds</span>
                    </div>
                </div>

                <div class="meta-note">
                    <i class="fas fa-graduation-cap"></i> premium portal
                    <i class="fas fa-circle" style="font-size:0.35rem; color: var(--emerald);"></i>
                    <i class="fas fa-lock"></i> encrypted access
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            let secondsLeft = 5;
            const countdownSpan = document.getElementById('countdown');
            countdownSpan.textContent = secondsLeft;

            const timer = setInterval(() => {
                secondsLeft -= 1;
                countdownSpan.textContent = secondsLeft;

                if (secondsLeft <= 0) {
                    clearInterval(timer);
                    window.location.href = "{{ route('login') }}";
                }
            }, 1000);
        })();
    </script>
</body>

</html>