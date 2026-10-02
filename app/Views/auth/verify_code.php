<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="only light">
    <meta name="theme-color" content="#D62828">
    <title>Verify Code · <?= esc(app_name()) ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= esc(app_logo()) ?>">
    <link rel="shortcut icon" href="<?= esc(app_logo()) ?>">
    <link rel="apple-touch-icon" href="<?= esc(app_logo()) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css?v=20261002logo1') ?>">
    <?= app_theme_css() ?>
    <style>
        :root {
            --red: var(--primary, #D62828);
            --red-dark: var(--primary-dark, #B71C1C);
            --red-soft: var(--primary-soft, rgba(214, 40, 40, .10));
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

        <?php
        $bgMode = app_bg_mode();
        $useSingle = ($bgMode === 'single');
        $defaultAuthSlides = array_values(app_bg_slideshow());
        $authSlides = $useSingle
            ? [app_has_custom_bg() ? app_bg_image() : ($defaultAuthSlides[0] ?? app_bg_image())]
            : $defaultAuthSlides;
        ?>
        body.auth-page::after { content: none !important; display: none !important; }
        .auth-bg-slideshow {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100vh;
            height: 100dvh;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }
        .auth-bg-slide {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity .9s ease;
        }
        .auth-bg-slide.is-active { opacity: 1; }
        @media (prefers-reduced-motion: reduce) {
            .auth-bg-slide { transition: none; }
        }
        @media print {
            .auth-bg-slideshow { display: none !important; }
        }

        .scenery { position: fixed !important; inset: 0 !important; z-index: 0 !important; pointer-events: none !important; overflow: hidden !important; }
        .scenery svg { position: absolute; display: block; }
        .scenery .routes { top: -60px; right: -80px; width: 640px; height: 640px; }
        .scenery .skyline { bottom: -2px; left: 0; width: 100%; height: 150px; }

        .auth {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 460px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-8);
        }

        .brand { display: inline-flex; align-items: center; gap: 14px; animation: fadeDown .6s ease .1s both; }
        .brand-mark {
            width: 60px; height: 60px; border-radius: 50%;
            padding: 3.5px;
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid var(--border);
            box-shadow: 0 8px 20px -6px rgba(15, 23, 42, .22);
            display: grid; place-items: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        .brand-mark img {
            display: block; width: 100%; height: 100%;
            border-radius: 50%; object-fit: cover;
        }
        .brand-name { font-size: 21px; font-weight: 800; letter-spacing: -.015em; color: #000000 !important; line-height: 1.2; text-shadow: 0 1px 2px rgba(255, 255, 255, 0.85); }
        .brand-sub { display: block; font-size: 13.5px; font-weight: 800; color: #000000 !important; margin-top: 2px; letter-spacing: 0.5px; text-transform: uppercase; text-shadow: 0 1px 2px rgba(255, 255, 255, 0.85); }

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
        .email-highlight { color: var(--red); font-weight: 600; }

        .alert {
            display: flex; align-items: flex-start; gap: 10px;
            font-size: 13.5px; line-height: 1.5;
            padding: 12px 14px; border-radius: var(--radius-md); margin-bottom: var(--space-6);
        }
        .alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
        .alert-danger { background: #FEF2F2; border: 1px solid #FECACA; border-left: 4px solid var(--red); color: #B91C1C; }
        .alert-success { background: #F0FDF4; border: 1px solid #BBF7D0; border-left: 4px solid var(--success); color: #166534; }

        .otp-container {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: var(--space-6);
        }
        .otp-input {
            width: 54px; height: 60px;
            flex: 1 1 54px;
            min-width: 0;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            text-align: center;
            font-size: 24px; font-weight: 700;
            font-family: var(--font);
            color: var(--text);
            background: #FCFDFD;
            outline: none;
            caret-color: var(--red);
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease, background .18s ease;
        }
        .otp-input:focus { border-color: var(--red); background: var(--white); box-shadow: 0 0 0 4px var(--red-soft); transform: translateY(-2px); }
        .otp-input.filled { border-color: var(--red); background: var(--white); }
        .otp-input.error { border-color: #EF4444; background: #FEF2F2; animation: shake .4s ease; }

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
        .btn:disabled { opacity: .55; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn .spinner {
            width: 18px; height: 18px;
            border: 2px solid rgba(255, 255, 255, .35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            display: none;
        }

        .resend-area { text-align: center; margin-top: var(--space-6); font-size: 14px; color: var(--text-2); }
        .resend-link {
            color: var(--red); font-weight: 600; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
            min-height: 48px; padding: 0 8px; border-radius: var(--radius-md);
            transition: color .15s ease;
        }
        .resend-link svg { width: 15px; height: 15px; }
        .resend-link:hover { color: var(--red-dark); }
        .resend-link.disabled { color: #9CA3AF; cursor: not-allowed; pointer-events: none; }
        .countdown { font-weight: 600; color: var(--red); }

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

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            50% { transform: translateX(6px); }
            75% { transform: translateX(-4px); }
        }
        @keyframes fadeDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(28px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }

        @media (max-width: 640px) {
            .auth { gap: 20px; }
            .brand { flex-direction: column; width: 100%; gap: 8px; text-align: center; }
            .brand-mark { width: 56px; height: 56px; }
            .brand-name, .brand-sub { display: block; text-align: center; }
            .card { background: #ffffff; backdrop-filter: none; -webkit-backdrop-filter: none; }
            body { padding: 20px 16px; }
            .card { padding: var(--space-6); border-radius: var(--radius-lg); }
            .otp-input { width: 46px; height: 54px; font-size: 20px; }
            .otp-container { gap: 8px; }
        }
        @media (max-width: 420px) {
            body { padding: 16px 10px; }
            .card { padding: 20px 14px; border-radius: var(--radius-md); }
            .otp-input { width: 38px; height: 48px; font-size: 17px; }
            .otp-container { gap: 6px; }
        }
    </style>
    <?= $this->include('partials/interaction_assets') ?>
</head>
<body class="auth-page">
    <div class="auth-bg-slideshow" id="authBgSlideshow" aria-hidden="true" style="opacity: <?= $useSingle ? '0.28' : '0.22' ?>">
        <img class="auth-bg-slide is-active" src="<?= esc($authSlides[0], 'attr') ?>" alt="" decoding="async">
        <img class="auth-bg-slide" alt="" decoding="async">
    </div>

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
                <img src="<?= esc(app_logo()) ?>"
                     alt="<?= esc(app_name()) ?> logo"
                     width="400" height="400" loading="eager">
            </span>
            <span class="brand-name"><?= esc(app_name()) ?><span class="brand-sub"><?= esc(app_subtitle()) ?></span></span>
        </div>

        <div class="card">
            <div class="card-head">
                <span class="step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="3"/>
                        <path d="M22 7l-10 6L2 7"/>
                    </svg>
                </span>
                <h1>Check your email</h1>
                <p class="sub">
                    We've sent a 6-digit verification code to<br>
                    <span class="email-highlight"><?= esc($masked_email ?? '***@***.com') ?></span>
                </p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                    </svg>
                    <span><?= session()->getFlashdata('error') ?></span>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                    <span><?= session()->getFlashdata('success') ?></span>
                </div>
            <?php endif; ?>

            <form id="verifyForm" action="<?= base_url('verify-reset-code/' . $token) ?>" method="post">
                <?= csrf_field() ?>

                <div class="otp-container" id="otpContainer" aria-label="Enter the 6-digit verification code">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code" data-index="0" autofocus aria-label="Digit 1">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="1" aria-label="Digit 2">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="2" aria-label="Digit 3">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="3" aria-label="Digit 4">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="4" aria-label="Digit 5">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="5" aria-label="Digit 6">
                </div>

                <input type="hidden" name="reset_code" id="resetCode">

                <button type="submit" class="btn" id="btnVerify" disabled>
                    <span class="spinner" id="spinner"></span>
                    <svg id="btnIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3l7 3v5c0 4.5-3 8.4-7 10-4-1.6-7-5.5-7-10V6z"/>
                        <path d="M9.5 12l1.8 1.8 3.4-3.6"/>
                    </svg>
                    <span id="btnText">Verify Code</span>
                </button>
            </form>

            <div class="resend-area">
                <span id="resendWait">Resend code in <span class="countdown" id="countdown">60</span>s</span>
                <form id="resendForm" action="<?= base_url('resend-reset-code/' . $token) ?>" method="post" style="display:inline;">
                    <?= csrf_field() ?>
                    <a class="resend-link disabled" id="resendLink" style="display:none;" href="#" onclick="document.getElementById('resendForm').submit(); return false;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                        </svg>
                        Resend code
                    </a>
                </form>
            </div>

            <a class="back" href="<?= base_url('login') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/>
                </svg>
                Back to login
            </a>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('.otp-input');
        const form   = document.getElementById('verifyForm');
        const hidden = document.getElementById('resetCode');
        const btn    = document.getElementById('btnVerify');
        const spinner = document.getElementById('spinner');
        const btnIcon = document.getElementById('btnIcon');
        const btnText = document.getElementById('btnText');

        inputs.forEach((input, idx) => {
            input.addEventListener('input', function (e) {
                const val = this.value.replace(/\D/g, '');
                this.value = val.charAt(0) || '';
                if (val && idx < inputs.length - 1) {
                    inputs[idx + 1].focus();
                }
                updateState();
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && idx > 0) {
                    inputs[idx - 1].focus();
                    inputs[idx - 1].value = '';
                    updateState();
                }
                if (e.key === 'ArrowLeft'  && idx > 0) inputs[idx - 1].focus();
                if (e.key === 'ArrowRight' && idx < inputs.length - 1) inputs[idx + 1].focus();
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (!btn.disabled) {
                        form.requestSubmit ? form.requestSubmit() : form.submit();
                    }
                }
            });

            input.addEventListener('focus', function () { this.select(); });

            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                for (let i = 0; i < Math.min(paste.length, inputs.length); i++) {
                    inputs[i].value = paste[i];
                }
                const focusIdx = Math.min(paste.length, inputs.length - 1);
                inputs[focusIdx].focus();
                updateState();
            });
        });

        function updateState() {
            let code = '';
            let allFilled = true;
            inputs.forEach(inp => {
                inp.classList.toggle('filled', !!inp.value);
                inp.classList.remove('error');
                code += inp.value;
                if (!inp.value) allFilled = false;
            });
            hidden.value = code;
            btn.disabled = !allFilled;
        }

        form.addEventListener('submit', function () {
            btn.disabled = true;
            spinner.style.display = 'inline-block';
            btnIcon.style.display = 'none';
            btnText.textContent = 'Verifying\u2026';
        });

        <?php if (session()->getFlashdata('error')): ?>
        inputs.forEach(inp => inp.classList.add('error'));
        setTimeout(() => inputs.forEach(inp => inp.classList.remove('error')), 600);
        <?php endif; ?>

        let seconds = 60;
        const countdownEl = document.getElementById('countdown');
        const resendWait  = document.getElementById('resendWait');
        const resendLink  = document.getElementById('resendLink');

        const timer = setInterval(function () {
            seconds--;
            if (countdownEl) countdownEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                if (resendWait) resendWait.style.display = 'none';
                if (resendLink) {
                    resendLink.classList.remove('disabled');
                    resendLink.style.display = 'inline-flex';
                }
            }
        }, 1000);
    });
    </script>
    <?= view('partials/auth_background', ['authSlides' => $authSlides, 'defaultAuthSlides' => $defaultAuthSlides]) ?>
    <script src="<?= base_url('assets/js/global-loader.js?v=20261002btn1') ?>"></script>
    <script src="<?= base_url('assets/js/auto-dismiss-alerts.js') ?>"></script>
    <script src="<?= base_url('js/ws-client.js?v=20261002bg1') ?>"></script>
    <script>
    document.addEventListener('pttm:ws-branding_updated', function (e) {
        if (e.detail && e.detail.data && typeof window.applyLiveBranding === 'function') {
            window.applyLiveBranding(e.detail.data);
        }
    });
    </script>
</body>
</html>
