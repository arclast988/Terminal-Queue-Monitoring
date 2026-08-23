<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#D62828">
    <title>Reset Password · Palompon Transit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>">
    <style>
        :root {
            --red: #D62828;
            --red-dark: #B71C1C;
            --red-soft: rgba(214, 40, 40, .10);
            --white: #FFFFFF;
            --bg: #F8F9FA;
            --text: #1F2937;
            --text-2: #6B7280;
            --border: #E5E7EB;
            --border-strong: #D1D5DB;
            --success: #16A34A;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;
            --space-4: 16px;
            --space-5: 20px;
            --space-6: 24px;
            --space-8: 32px;
            --shadow-card: 0 24px 60px -16px rgba(15, 23, 42, .14), 0 10px 24px -12px rgba(15, 23, 42, .08);
            --shadow-btn: 0 12px 26px -10px rgba(214, 40, 40, .5);
            --font: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { -webkit-text-size-adjust: 100%; }
        body {
            font-family: var(--font);
            background: transparent;
            color: var(--text);
            min-height: 100vh;
            min-height: 100dvh;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            position: relative;
        }

        /* Semi-transparent system background watermark (Palompon Photo Slideshow) */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            background-repeat: no-repeat;
            background-position: center center;
            background-size: cover;
            opacity: 0.22;
            z-index: 0;
            pointer-events: none;
            animation: palomponBgSlideshow 30s infinite ease-in-out;
        }

        @keyframes palomponBgSlideshow {
            0%, 17% { background-image: url('<?= base_url('images/bg/bg1_townhall.png') ?>'); opacity: 0.22; }
            19% { opacity: 0.05; }
            20%, 37% { background-image: url('<?= base_url('images/bg/bg2_aerial_port.png') ?>'); opacity: 0.22; }
            39% { opacity: 0.05; }
            40%, 57% { background-image: url('<?= base_url('images/bg/bg3_aerial_town.png') ?>'); opacity: 0.22; }
            59% { opacity: 0.05; }
            60%, 77% { background-image: url('<?= base_url('images/bg/bg4_terminal_exterior.png') ?>'); opacity: 0.22; }
            79% { opacity: 0.05; }
            80%, 97% { background-image: url('<?= base_url('images/bg/bg5_terminal_bay.png') ?>'); opacity: 0.22; }
            99% { opacity: 0.05; }
        }

        .scenery { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
        .scenery svg { position: absolute; display: block; }
        .scenery .routes { top: -60px; right: -80px; width: 640px; height: 640px; }
        .scenery .skyline { bottom: -2px; left: 0; width: 100%; height: 150px; }

        .auth {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-8);
        }

        .brand { display: inline-flex; align-items: center; gap: 12px; animation: fadeDown .6s ease .1s both; }
        .brand-mark {
            width: 46px; height: 46px; border-radius: 13px;
            padding: 3px;
            background: rgba(255, 255, 255, 0.85);
            border: 1.5px solid var(--border);
            box-shadow: 0 8px 18px -8px rgba(15, 23, 42, .22);
            display: grid; place-items: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        .brand-mark img {
            display: block; width: 100%; height: 100%;
            border-radius: 9px; object-fit: cover;
        }
        .brand-name { font-size: 17px; font-weight: 700; letter-spacing: -.01em; color: var(--text); line-height: 1.2; }
        .brand-sub { display: block; font-size: 12.5px; font-weight: 500; color: var(--text-2); margin-top: 2px; }

        .card {
            width: 100%;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: var(--radius-xl);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: var(--shadow-card);
            padding: var(--space-8) var(--space-8);
            animation: slideUp .7s cubic-bezier(.22, 1, .36, 1) .2s both;
        }
        .card-head { text-align: center; margin-bottom: var(--space-8); }
        .card-head .step-icon {
            width: 64px; height: 64px; margin: 0 auto 18px; border-radius: 18px;
            background: var(--red-soft); color: var(--red);
            display: grid; place-items: center;
        }
        .card-head .step-icon svg { width: 30px; height: 30px; }
        .card-head h1 { font-size: 24px; font-weight: 800; letter-spacing: -.02em; color: var(--text); }
        .card-head .sub { margin-top: 6px; font-size: 14.5px; color: var(--text-2); line-height: 1.55; }

        .alert {
            display: flex; align-items: flex-start; gap: 10px;
            font-size: 13.5px; line-height: 1.5;
            padding: 12px 14px; border-radius: var(--radius-md); margin-bottom: var(--space-6);
        }
        .alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
        .alert-danger { background: #FEF2F2; border: 1px solid #FECACA; border-left: 4px solid var(--red); color: #B91C1C; }

        .field { margin-bottom: var(--space-5); }
        .field label { display: block; font-size: 13.5px; font-weight: 600; color: var(--text); margin-bottom: 8px; }
        .field .control { position: relative; }
        .field input {
            width: 100%; height: 52px; padding: 0 16px;
            font-family: inherit; font-size: 15px; font-weight: 500; color: var(--text);
            background: #FCFDFD;
            border: 1.5px solid var(--border); border-radius: var(--radius-md);
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .field input::placeholder { color: #9CA3AF; font-weight: 400; }
        .field input:hover { border-color: var(--border-strong); }
        .field input:focus { outline: none; border-color: var(--red); background: var(--white); box-shadow: 0 0 0 4px var(--red-soft); }
        .field.password input { padding-right: 56px; }
        .field .hint { display: flex; align-items: center; gap: 7px; margin-top: 8px; font-size: 12.5px; color: var(--text-2); }
        .field .hint svg { width: 14px; height: 14px; flex-shrink: 0; color: #9CA3AF; }
        .field .hint.ok { color: var(--success); }
        .field .hint.ok svg { color: var(--success); }

        .toggle {
            position: absolute; top: 50%; right: 4px; transform: translateY(-50%);
            width: 48px; height: 48px;
            display: grid; place-items: center;
            border: none; background: transparent; color: #9CA3AF;
            border-radius: var(--radius-md); cursor: pointer;
            transition: color .15s ease, background .15s ease;
        }
        .toggle:hover { color: var(--text-2); background: rgba(15, 23, 42, .04); }
        .toggle svg { width: 20px; height: 20px; }
        .toggle .icon-off { display: none; }
        .toggle[aria-pressed="true"] .icon-on { display: none; }
        .toggle[aria-pressed="true"] .icon-off { display: block; }

        .btn {
            width: 100%; height: 52px; border: none; border-radius: var(--radius-md);
            font-family: inherit; font-size: 15.5px; font-weight: 700; letter-spacing: .01em;
            cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            color: #fff; background: linear-gradient(150deg, var(--red), var(--red-dark)); box-shadow: var(--shadow-btn);
            transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
        }
        .btn svg { width: 18px; height: 18px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 16px 32px -10px rgba(214, 40, 40, .58); }
        .btn:active { transform: translateY(0); }
        .btn .spinner {
            width: 18px; height: 18px;
            border: 2px solid rgba(255, 255, 255, .35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            display: none;
        }
        .btn.is-loading { pointer-events: none; opacity: .9; }
        .btn.is-loading .spinner { display: inline-block; }

        .back {
            margin-top: var(--space-6);
            min-height: 48px;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%;
            font-size: 14px; font-weight: 600; color: var(--text-2);
            text-decoration: none; border-radius: var(--radius-md);
            transition: color .15s ease, background .15s ease;
        }
        .back svg { width: 17px; height: 17px; }
        .back:hover { color: var(--red); background: var(--red-soft); }

        a:focus-visible, button:focus-visible, input:focus-visible { outline: 2px solid var(--red); outline-offset: 2px; }

        @keyframes fadeDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(28px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }

        @media (max-width: 640px) {
            body { padding: 20px 16px; }
            .card { padding: var(--space-6); border-radius: var(--radius-lg); }
        }
        @media (max-width: 400px) { .card { padding: 28px 20px; } }
    </style>
</head>
<body>
    <div class="scenery" aria-hidden="true">
        <svg class="routes" viewBox="0 0 640 640" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M40 470C170 350 300 500 470 350S640 170 610 60" stroke="#D62828" stroke-opacity="0.10" stroke-width="2" stroke-dasharray="2 10" stroke-linecap="round"/>
            <path d="M90 560C240 420 420 560 560 420" stroke="#D62828" stroke-opacity="0.07" stroke-width="2" stroke-dasharray="2 10" stroke-linecap="round"/>
            <circle cx="470" cy="350" r="6" fill="#D62828" fill-opacity="0.14"/>
            <circle cx="470" cy="350" r="14" stroke="#D62828" stroke-opacity="0.10" stroke-width="1.5"/>
            <circle cx="610" cy="60" r="5" fill="#D62828" fill-opacity="0.12"/>
            <circle cx="40" cy="470" r="5" fill="#D62828" fill-opacity="0.10"/>
        </svg>
        <svg class="skyline" viewBox="0 0 1440 150" preserveAspectRatio="xMidYMax slice" fill="#D62828" xmlns="http://www.w3.org/2000/svg">
            <path fill-opacity="0.05" d="M0 150V96h40v54h34V60h48v90h40V84h52v66h36V40h46v110h52V74h44v76h40V58h54v92h34V100h48v50h40V70h44v80h40V48h60v102h34V96h44v54h36V64h52v86h40V110h46v40h44V76h52v74h36V124h60v26z"/>
        </svg>
    </div>

    <main class="auth">
        <div class="brand">
            <span class="brand-mark" aria-hidden="true">
                <img src="<?= base_url('images/9HFScgVg_400x400.png') ?>"
                     alt="Palompon Transit logo"
                     width="400" height="400" loading="eager">
            </span>
            <span class="brand-name">Palompon Transit<span class="brand-sub">Terminal Monitoring &amp; Vehicle Dispatching</span></span>
        </div>

        <div class="card">
            <div class="card-head">
                <span class="step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 2l-2 2m-7.6 7.6a2.1 2.1 0 0 0-3 3L6 16l2 2 3.4-3.4a2.1 2.1 0 0 0 3-3L21 4l-2-2"/>
                        <path d="M16 8l3 3"/>
                        <path d="M14.5 14.5L19 19a2.1 2.1 0 0 1-3 3l-4.5-4.5"/>
                    </svg>
                </span>
                <h1>Reset password</h1>
                <p class="sub">Create a new password for your account.</p>
            </div>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                    </svg>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('reset-password/' . $token) ?>" method="post" novalidate>
                <?= csrf_field() ?>

                <div class="field password">
                    <label for="password">New password</label>
                    <div class="control">
                        <input type="password" id="password" name="password" placeholder="At least 6 characters" autocomplete="new-password" required minlength="6" autofocus>
                        <button type="button" class="toggle" aria-pressed="false" aria-label="Show password">
                            <svg class="icon-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="icon-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M17.94 17.94A10.5 10.5 0 0 1 12 19c-6.5 0-10-7-10-7a18 18 0 0 1 5.06-5.94M9.9 4.24A9.5 9.5 0 0 1 12 5c6.5 0 10 7 10 7a18.2 18.2 0 0 1-2.16 3.19"/>
                                <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                                <path d="M1 1l22 22"/>
                            </svg>
                        </button>
                    </div>
                    <p class="hint" id="pwHint" aria-live="polite">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                        </svg>
                        <span>At least 6 characters</span>
                    </p>
                </div>

                <div class="field password">
                    <label for="confirm_password">Confirm password</label>
                    <div class="control">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat new password" autocomplete="new-password" required minlength="6">
                        <button type="button" class="toggle" aria-pressed="false" aria-label="Show password">
                            <svg class="icon-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="icon-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M17.94 17.94A10.5 10.5 0 0 1 12 19c-6.5 0-10-7-10-7a18 18 0 0 1 5.06-5.94M9.9 4.24A9.5 9.5 0 0 1 12 5c6.5 0 10 7 10 7a18.2 18.2 0 0 1-2.16 3.19"/>
                                <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                                <path d="M1 1l22 22"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn">
                    <span class="spinner" aria-hidden="true"></span>
                    <span class="btn-label">Reset password</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14"/><path d="M13 6l6 6-6 6"/>
                    </svg>
                </button>
            </form>

            <a class="back" href="<?= base_url('login') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/>
                </svg>
                Back to login
            </a>
        </div>
    </main>

    <script>
        (function () {
            // --- Show / hide password for each field ---
            document.querySelectorAll('.toggle').forEach(function (toggle) {
                var field = toggle.closest('.control');
                toggle.addEventListener('click', function () {
                    var input = field.querySelector('input');
                    var show = toggle.getAttribute('aria-pressed') !== 'true';
                    input.type = show ? 'text' : 'password';
                    toggle.setAttribute('aria-pressed', String(show));
                    toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                });
            });

            // --- Live strength hint (min length only; matches server rule) ---
            var pw = document.getElementById('password');
            var hint = document.getElementById('pwHint');
            if (pw && hint) {
                pw.addEventListener('input', function () {
                    if (pw.value.length >= 6) {
                        hint.classList.add('ok');
                        hint.querySelector('span').textContent = 'Looks good';
                    } else {
                        hint.classList.remove('ok');
                        hint.querySelector('span').textContent = 'At least 6 characters';
                    }
                });
            }

            // --- Soft loading state on submit (client-side only) ---
            var form = document.querySelector('form');
            var submit = form ? form.querySelector('.btn') : null;
            if (form && submit) {
                form.addEventListener('submit', function () {
                    submit.classList.add('is-loading');
                    var label = submit.querySelector('.btn-label');
                    if (label) label.textContent = 'Resetting\u2026';
                });
            }
        })();
    </script>
    <script src="<?= base_url('assets/js/auto-dismiss-alerts.js') ?>"></script>
</body>
</html>