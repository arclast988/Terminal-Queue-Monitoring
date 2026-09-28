<?php $hasCustomLoginArtwork = app_has_custom_login_card(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="only light">
    <meta name="theme-color" content="#D62828">
    <title>Sign in · <?= esc(app_system_title()) ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= esc(app_logo()) ?>">
    <link rel="shortcut icon" href="<?= esc(app_logo()) ?>">
    <link rel="apple-touch-icon" href="<?= esc(app_logo()) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>">
    <?= app_theme_css() ?>
    <style>
        /* =================================================================
           <?= esc(app_name()) ?> — Premium Login
           Design tokens (8pt system)
           ================================================================= */
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

            --space-1: 4px;
            --space-2: 8px;
            --space-3: 12px;
            --space-4: 16px;
            --space-5: 20px;
            --space-6: 24px;
            --space-8: 32px;
            --space-10: 40px;
            --space-12: 48px;
            --space-16: 64px;

            --shadow-card: 0 24px 60px -16px rgba(15, 23, 42, .14), 0 10px 24px -12px rgba(15, 23, 42, .08);
            --shadow-btn: 0 12px 26px -10px rgba(214, 40, 40, .5);
            --shadow-chip: 0 8px 20px -6px rgba(15, 23, 42, .18);

            --font: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        a, button, input { touch-action: manipulation; }

        html { -webkit-text-size-adjust: 100%; }

        body {
            font-family: var(--font);
            background: transparent;
            color: var(--text);
            min-height: 100vh;
            min-height: 100dvh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            position: relative;
        }

        /* Semi-transparent system background watermark (Terminal Photo Slideshow or Single Hero) */
        <?php
        $bgMode = app_bg_mode();
        $useSingle = ($bgMode === 'single');
        ?>
        <?php if ($useSingle): ?>
        <?php if (app_has_custom_bg()): ?>
        body::after {
            content: "";
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            background-repeat: no-repeat;
            background-position: center center;
            background-size: cover;
            background-image: url('<?= esc(app_bg_image()) ?>');
            opacity: 0.28;
            z-index: 0;
            pointer-events: none;
        }
        <?php endif; ?>
        <?php else: ?>
        <?= app_bg_slideshow_css(null, 0.22) ?>
        <?php endif; ?>

        /* ---- Subtle transportation scenery (route lines, nodes, faint skyline) ---- */
        .scenery {
            position: fixed !important;
            inset: 0 !important;
            z-index: 0 !important;
            pointer-events: none !important;
            overflow: hidden !important;
        }
        .scenery svg { position: absolute; display: block; }
        .scenery .routes { top: -60px; right: -80px; width: 640px; height: 640px; }
        .scenery .skyline { bottom: -2px; left: 0; width: 100%; height: 150px; }

        /* =================================================================
           Layout
           ================================================================= */
        .page {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space-6) var(--space-6) var(--space-8);
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* ---- Brand row ---- */
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            margin-bottom: clamp(var(--space-5), 4vh, var(--space-8));
            animation: fadeDown .6s ease .1s both;
            width: fit-content;
        }
        .brand-mark {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            padding: 3px;
            background: var(--white);
            border: 2px solid var(--border);
            box-shadow: 0 4px 14px -3px rgba(15, 23, 42, .20);
            display: grid;
            place-items: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        .brand-mark img {
            display: block;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        .brand-name { font-size: 21px; font-weight: 800; letter-spacing: -.015em; color: #000000 !important; line-height: 1.2; text-shadow: 0 1px 2px rgba(255, 255, 255, 0.85); }
        .brand-sub { display: block; font-size: 13.5px; font-weight: 800; color: #000000 !important; margin-top: 2px; letter-spacing: 0.5px; text-transform: uppercase; text-shadow: 0 1px 2px rgba(255, 255, 255, 0.85); }

        /* ---- Main grid: hero (left) / card (right) ---- */
        .login-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.12fr) minmax(0, .88fr);
            gap: clamp(var(--space-10), 5vw, var(--space-16));
            align-items: center;
            flex: 1;
        }

        /* =================================================================
           Hero / Branding column
           ================================================================= */
        .auth-page .hero { padding: 0 !important; min-height: 0 !important; }
        .auth-page .hero .kicker { font-size: 13px !important; line-height: 1.35 !important; margin-bottom: var(--space-4) !important; }
        .auth-page .hero .lede { font-size: 17px !important; line-height: 1.65 !important; margin-bottom: 0 !important; }
        .hero-copy {
            animation: fadeUp .7s ease .15s both;
        }
        .kicker {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--red);
            margin-bottom: var(--space-4);
        }
        .kicker::before {
            content: "";
            width: 26px;
            height: 2.5px;
            border-radius: 2px;
            background: var(--red);
        }
        .hero-copy h1 {
            font-size: clamp(2.2rem, 4.3vw, 3.4rem);
            font-weight: 800;
            letter-spacing: -.035em;
            line-height: 1.08;
            color: #000000 !important;
            max-width: 14ch;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.85);
        }
        .hero-copy h1 .accent { color: var(--red); }
        .hero-copy .lede {
            margin-top: var(--space-5);
            font-size: 17px;
            line-height: 1.65;
            color: #000000 !important;
            max-width: 48ch;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.85);
        }

        .features {
            margin-top: var(--space-8);
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }
        .feature { display: flex; align-items: center; gap: var(--space-4); }
        .feature-icon {
            width: 42px; height: 42px;
            border-radius: 12px;
            background: var(--white);
            border: 1.5px solid rgba(15, 23, 42, 0.08);
            display: grid; place-items: center;
            color: var(--red);
            flex-shrink: 0;
            box-shadow: 0 6px 14px -4px rgba(15, 23, 42, .1);
        }
        .feature-icon svg { width: 20px; height: 20px; }
        .feature-text b { display: block; font-size: 15.5px; font-weight: 800; color: #000000 !important; text-shadow: 0 1px 2px rgba(255, 255, 255, 0.85); }
        .feature-text span { font-size: 14.5px; color: #000000 !important; font-weight: 600; line-height: 1.45; text-shadow: 0 1px 2px rgba(255, 255, 255, 0.85); }

        /* ---- Feature list and prominent system seal ---- */
        .hero-support {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            align-items: start;
            gap: var(--space-5);
            margin-top: var(--space-5);
        }
        .hero-support .features { margin-top: 0; }
        .hero-support.has-custom-artwork { display: block; }
        .hero-support.has-custom-artwork .features { margin-bottom: var(--space-5); }
        @media (min-width: 992px) {
            .hero-support:not(.has-custom-artwork) { max-width: 48ch; }
        }
        .hero-art {
            display: grid;
            place-items: center;
            width: 360px;
            min-height: 270px;
            margin-inline: auto;
            padding: 12px;
            border-radius: 20px;
            background: radial-gradient(circle at 50% 100%, rgba(220, 38, 38, .09), transparent 62%), rgba(255, 255, 255, .97);
            border: 1px solid rgba(183, 28, 28, .19);
            border-bottom: 3px solid var(--red);
            overflow: hidden;
            box-shadow: 0 12px 26px -18px rgba(153, 27, 27, .32), 0 0 0 3px rgba(220, 38, 38, .04);
        }
        .hero-art:not(.has-custom-artwork) {
            margin-inline: calc(42px + var(--space-4)) auto;
            background: radial-gradient(circle at 50% 46%, rgba(255, 215, 125, .24), transparent 61%), radial-gradient(circle at 50% 100%, rgba(220, 38, 38, .07), transparent 64%), linear-gradient(135deg, #fff 10%, #fff9f1 55%, #fff);
            box-shadow: 0 16px 32px -20px rgba(153, 27, 27, .4), 0 0 0 3px rgba(220, 38, 38, .045), inset 0 1px 0 rgba(255, 255, 255, .95);
        }
        .hero-art img {
            display: block;
            width: 242px;
            height: 242px;
            object-fit: contain;
        }
        .hero-art:not(.has-custom-artwork) img {
            filter: drop-shadow(0 0 10px rgba(245, 184, 61, .26));
        }
        .hero-art.has-custom-artwork {
            width: min(100%, 420px);
            min-height: 0;
            padding: var(--space-4);
        }
        .hero-art.has-custom-artwork img {
            width: 100%;
            height: auto;
            max-height: 210px;
            object-fit: contain;
        }

        @media (min-width: 992px) and (max-width: 1199px) {
            .hero-art { width: 360px; min-height: 270px; }
        }

        /* =================================================================
           Login card
           ================================================================= */
        .login-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: var(--radius-xl);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: var(--shadow-card);
            padding: var(--space-10);
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
            animation: slideUp .7s cubic-bezier(.22, 1, .36, 1) .25s both;
        }
        .login-card .card-head { margin-bottom: var(--space-8); }
        .login-card h2 { font-size: 25px; font-weight: 700; letter-spacing: -.02em; color: var(--text); }
        .login-card .card-sub { margin-top: var(--space-2); font-size: 14.5px; color: var(--text-2); line-height: 1.55; }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-left: 4px solid var(--red);
            color: #B91C1C;
            font-size: 13.5px;
            line-height: 1.5;
            padding: 12px 14px;
            border-radius: var(--radius-md);
            margin-bottom: var(--space-6);
        }
        .alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }

        .field { margin-bottom: var(--space-5); }
        .field label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: var(--space-2);
        }
        .field .control { position: relative; }
        .field input {
            width: 100%;
            height: 52px;
            padding: 0 var(--space-4);
            font-family: inherit;
            font-size: 15px;
            font-weight: 500;
            color: var(--text);
            background: #FCFDFD;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .field input::placeholder { color: #9CA3AF; font-weight: 400; }
        .field input:hover { border-color: var(--border-strong); }
        .field input:focus {
            outline: none;
            border-color: var(--red);
            background: var(--white);
            box-shadow: 0 0 0 4px var(--red-soft);
        }
        .field.password input { padding-right: 56px; }

        .toggle {
            position: absolute;
            top: 50%;
            right: 4px;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border: none;
            background: transparent;
            color: #9CA3AF;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: color .15s ease, background .15s ease;
        }
        .toggle:hover { color: var(--text-2); background: rgba(15, 23, 42, .04); }
        .toggle svg { width: 20px; height: 20px; }
        .toggle .icon-off { display: none; }
        .toggle[aria-pressed="true"] .icon-on { display: none; }
        .toggle[aria-pressed="true"] .icon-off { display: block; }

        .options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-4);
            margin: var(--space-2) 0 var(--space-6);
        }
        .check {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 48px;
            cursor: pointer;
            user-select: none;
            position: relative;
        }
        .check input { position: absolute; opacity: 0; width: 20px; height: 20px; }
        .check .box {
            width: 20px; height: 20px;
            border: 1.5px solid var(--border-strong);
            border-radius: 6px;
            background: var(--white);
            display: grid; place-items: center;
            flex-shrink: 0;
            transition: background .15s ease, border-color .15s ease;
        }
        .check .box svg { width: 12px; height: 12px; color: #fff; opacity: 0; transition: opacity .15s ease; }
        .check input:checked + .box { background: #2563eb; border-color: #2563eb; }
        .check input:checked + .box svg { opacity: 1; }
        .check input:focus-visible + .box { outline: 2px solid #2563eb; outline-offset: 2px; }
        .check .label { font-size: 14px; font-weight: 500; color: var(--text); }
        .forgot {
            font-size: 14px;
            font-weight: 600;
            color: var(--red);
            text-decoration: none;
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            padding: 0 2px;
            border-radius: var(--space-1);
            transition: color .15s ease;
        }
        .forgot:hover { color: var(--red-dark); }

        .btn {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 15.5px;
            font-weight: 700;
            letter-spacing: .01em;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
        }
        .btn-primary {
            color: #fff;
            background: linear-gradient(150deg, var(--red), var(--red-dark));
            box-shadow: var(--shadow-btn);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 32px -10px rgba(214, 40, 40, .58); }
        .btn-primary:active { transform: translateY(0); box-shadow: 0 8px 18px -8px rgba(214, 40, 40, .5); }
        .btn .spinner {
            width: 18px; height: 18px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, .35);
            border-top-color: #fff;
            display: none;
            animation: spin .7s linear infinite;
        }
        .btn.is-loading { pointer-events: none; opacity: .9; }
        .btn.is-loading .spinner { display: inline-block; }
        .btn.is-loading .btn-label { opacity: .92; }

        .divider {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            margin: var(--space-6) 0;
            color: #9CA3AF;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .btn-guest {
            background: var(--white);
            color: var(--text-2);
            border: 1.5px solid var(--border);
        }
        .btn-guest:hover { color: var(--red); border-color: var(--red); transform: translateY(-1px); }
        .btn-guest:active { transform: translateY(0); }
        .btn-guest svg { width: 18px; height: 18px; }

        .card-foot {
            margin-top: var(--space-6);
            text-align: center;
            font-size: 12.5px;
            color: #9CA3AF;
            line-height: 1.5;
        }

        /* ---- Focus visibility (keyboard) ---- */
        a:focus-visible, button:focus-visible, input:focus-visible {
            outline: 2px solid var(--red);
            outline-offset: 2px;
        }
        .btn:focus-visible, .forgot:focus-visible, .toggle:focus-visible { outline-offset: 3px; }

        /* =================================================================
           Animations
           ================================================================= */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(28px) scale(.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }

        /* =================================================================
           Responsive — laptop / tablet / mobile
           ================================================================= */

        /* =================================================================
           Responsive Breakpoints:
           - Large / Ultrawide Monitors (≥1440px / ≥2000px)
           - Standard Desktop (1025px - 1439px)
           - Compact Laptops (≤820px height)
           - Tablets (641px - 1024px)
           - Mobile (<640px / <420px)
           ================================================================= */

        /* Large & Ultra-Wide Desktop Displays (1440p, 2K, 4K, 21:9) */
        @media (min-width: 1440px) {
            .page {
                max-width: 1320px;
                padding: var(--space-8) var(--space-8) var(--space-10);
            }
            .hero-copy h1 {
                font-size: clamp(2.8rem, 3.6vw, 3.8rem);
            }
            .login-card {
                max-width: 460px;
                padding: var(--space-10) var(--space-8);
            }
        }

        @media (min-width: 2000px) {
            .page {
                max-width: 1440px;
            }
            .hero-copy h1 {
                font-size: 4rem;
            }
        }

        /* Narrow tablets and phones keep one default seal in the brand row. */
        @media (max-width: 991px) {
            .hero { display: block; order: 2; width: 100%; }
            .hero-copy { animation: none; }
            .hero-copy > .kicker,
            .hero-copy > h1,
            .hero-copy > .lede,
            .hero-support .features { display: none; }
            .hero-support,
            .hero-support.has-custom-artwork {
                display: flex;
                justify-content: center;
                margin-top: var(--space-5);
            }
            .hero-support:not(.has-custom-artwork) { display: none; }
            .hero-art,
            .hero-art.has-custom-artwork {
                width: 100%;
                min-height: 176px;
                padding: 12px;
            }
            .login-col { order: 1; }

            .page {
                max-width: 500px;
                justify-content: center;
            }

            .brand {
                align-self: center;
                margin-left: auto;
                margin-right: auto;
                margin-bottom: var(--space-6);
                animation-name: fadeDown;
            }

            .login-layout {
                display: flex;
                flex-direction: column;
                justify-content: center;
                gap: 0;
                min-height: auto;
                flex: 0 0 auto;
            }

            .login-col { width: 100%; }

            .login-card {
                max-width: 440px;
                margin: 0 auto;
                padding: var(--space-8) var(--space-6);
                background: #ffffff;
                backdrop-filter: none;
                -webkit-backdrop-filter: none;
            }
        }

        @media (min-width: 992px) and (max-width: 1200px) {
            .login-card {
                background: #ffffff;
                backdrop-filter: none;
                -webkit-backdrop-filter: none;
            }
        }

        /* Compact Tablets & Large Phones (≤640px) */
        @media (max-width: 640px) {
            .page { padding: var(--space-5) var(--space-4) var(--space-6); }
            .brand {
                flex-direction: column;
                width: 100%;
                gap: 8px;
                margin-bottom: 14px;
                text-align: center;
            }
            .brand-mark { width: 64px; height: 64px; }
            .brand-name, .brand-sub { display: block; text-align: center; }
            .login-card {
                padding: var(--space-6) var(--space-5);
                border-radius: var(--radius-lg);
                background: #ffffff;
                backdrop-filter: none;
                -webkit-backdrop-filter: none;
            }
            .login-card h2 { font-size: 22px; }
            .login-card .card-head { margin-bottom: 20px; }
            .field { margin-bottom: 16px; }
            .options { margin: 4px 0 20px; }
        }

        /* Small Phones (≤420px) */
        @media (max-width: 420px) {
            .page { padding: 16px 10px 20px; }
            .brand-name { font-size: 19px; }
            .brand-sub { font-size: 12px; }
            .login-card { padding: 18px 14px; border-radius: var(--radius-md); }
            .login-card h2 { font-size: 20px; }
        }

        @media (max-width: 350px) {
            .options { flex-direction: column; align-items: flex-start; gap: 4px; }
        }

        @media (min-width: 992px) {
            .page { padding-top: 20px; padding-bottom: 24px; }
            .brand { margin-bottom: 20px; }
            .hero-copy h1 { font-size: clamp(2.3rem, 3.5vw, 3.5rem); }
            .features { margin-top: 24px; gap: 12px; }
            .hero-support { margin-top: var(--space-4); }
        }

        @media (min-width: 992px) and (min-height: 1000px) {
            .page { justify-content: center; }
            .login-layout { flex: 0 0 auto; }
        }

        @media (min-width: 992px) and (max-height: 820px) {
            .brand { margin-bottom: var(--space-3); }
            .hero-copy h1 { font-size: 2.3rem; }
            .hero-copy .lede { margin-top: var(--space-2); }
            .auth-page .hero .lede { font-size: 15px !important; }
            .features { margin-top: var(--space-3); gap: var(--space-2); }
            .feature-icon { width: 34px; height: 34px; }
            .hero-support { margin-top: var(--space-3); }
            .hero-art:not(.has-custom-artwork) { margin-inline-start: calc(34px + var(--space-4)); }
            .hero-art:not(.has-custom-artwork) img { max-height: 235px; }
            .login-card { padding: var(--space-6); }
            .login-card .card-head { margin-bottom: var(--space-4); }
        }

        @media (min-width: 641px) and (max-width: 991px) and (max-height: 820px) {
            .page { padding-top: 16px; padding-bottom: 16px; }
            .brand { margin-bottom: 16px; }
            .login-card { padding-top: 24px; padding-bottom: 24px; }
        }
    </style>
</head>
<body class="auth-page">

    <!-- Subtle transportation scenery -->
    <div class="scenery" aria-hidden="true">
        <svg class="routes" viewBox="0 0 640 640" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M40 470C170 350 300 500 470 350S640 170 610 60"
                  stroke="#D62828" stroke-opacity="0.10" stroke-width="2" stroke-dasharray="2 10" stroke-linecap="round"/>
            <path d="M90 560C240 420 420 560 560 420"
                  stroke="#D62828" stroke-opacity="0.07" stroke-width="2" stroke-dasharray="2 10" stroke-linecap="round"/>
            <circle cx="470" cy="350" r="6" fill="#D62828" fill-opacity="0.14"/>
            <circle cx="470" cy="350" r="14" stroke="#D62828" stroke-opacity="0.10" stroke-width="1.5"/>
            <circle cx="610" cy="60" r="5" fill="#D62828" fill-opacity="0.12"/>
            <circle cx="610" cy="60" r="12" stroke="#D62828" stroke-opacity="0.08" stroke-width="1.5"/>
            <circle cx="40" cy="470" r="5" fill="#D62828" fill-opacity="0.10"/>
        </svg>
        <svg class="skyline" viewBox="0 0 1440 150" preserveAspectRatio="xMidYMax slice" fill="#D62828" xmlns="http://www.w3.org/2000/svg">
            <path fill-opacity="0.05" d="M0 150V96h40v54h34V60h48v90h40V84h52v66h36V40h46v110h52V74h44v76h40V58h54v92h34V100h48v50h40V70h44v80h40V48h60v102h34V96h44v54h36V64h52v86h40V110h46v40h44V76h52v74h36V56h60v94h44V120h38v30h24V150H0z"/>
            <path fill-opacity="0.04" d="M0 150V120h28v30h28V84h40v66h44V64h52v86h40V104h48v46h36V52h56v98h40V92h52v58h40V120h44v30h36V72h48v78h40V110h52v40h34V88h52v62h40V120h44v30h36V96h52v54h36V140h40v10H0z"/>
        </svg>
    </div>

    <div class="page">
        <!-- Brand -->
        <div class="brand">
            <span class="brand-mark" aria-hidden="true">
                <img class="app-logo" src="<?= esc(app_logo()) ?>"
                     alt="<?= esc(app_name()) ?> logo"
                     width="400" height="400" loading="eager">
            </span>
            <span class="brand-name">
                <span class="brand-title app-brand-name"><?= esc(app_name()) ?></span>
                <span class="brand-sub"><?= esc(app_subtitle()) ?></span>
            </span>
        </div>

        <main class="login-layout">
            <!-- Left — hero copy + artwork -->
            <section class="hero">
                <div class="hero-copy">
                    <p class="kicker"><?= esc(login_kicker()) ?></p>
                    <h1><?= login_headline_html() ?></h1>
                    <p class="lede">
                        <?= esc(app_name()) ?> <?= esc(login_subheadline()) ?>
                    </p>
                    <div class="hero-support <?= $hasCustomLoginArtwork ? 'has-custom-artwork' : '' ?>">
                        <div class="features">
                            <div class="feature">
                                <span class="feature-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M12 7v5l3 3"/>
                                    </svg>
                                </span>
                                <span class="feature-text">
                                    <b><?= esc(login_feature1_title()) ?></b>
                                    <span><?= esc(login_feature1_desc()) ?></span>
                                </span>
                            </div>
                            <div class="feature">
                                <span class="feature-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 3l7 3v5c0 4.5-3 8.4-7 10-4-1.6-7-5.5-7-10V6z"/>
                                        <path d="M9.5 12l1.8 1.8 3.4-3.6"/>
                                    </svg>
                                </span>
                                <span class="feature-text">
                                    <b><?= esc(login_feature2_title()) ?></b>
                                    <span><?= esc(login_feature2_desc()) ?></span>
                                </span>
                            </div>
                        </div>

                        <div class="hero-art <?= $hasCustomLoginArtwork ? 'has-custom-artwork' : '' ?>" aria-hidden="true">
                            <img src="<?= esc(app_login_card_image()) ?>"
                                 id="loginHeroImg"
                                 alt=""
                                 width="1536" height="1024" loading="lazy" decoding="async">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Right — floating login card -->
            <aside class="login-col">
                <div class="login-card">
                    <div class="card-head">
                        <h2>Welcome back</h2>
                        <p class="card-sub">Sign in to your dashboard.</p>
                    </div>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 8v4"/>
                                <path d="M12 16h.01"/>
                            </svg>
                            <span><?= session()->getFlashdata('error') ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('login') ?>" method="post" id="loginForm">
                        <?= csrf_field() ?>

                        <div class="field">
                            <label for="username">Username or Email</label>
                            <div class="control">
                                <input type="text" id="username" name="username"
                                       placeholder="Enter your username or email"
                                       autocomplete="username"
                                       required
                                       oninvalid="this.setCustomValidity(this.validity.valueMissing ? 'Please fill out this field.' : '')"
                                       oninput="this.setCustomValidity('')"
                                       autofocus>
                            </div>
                        </div>

                        <div class="field password">
                            <label for="password">Password</label>
                            <div class="control">
                                <input type="password" id="password" name="password"
                                       placeholder="Enter your password"
                                       autocomplete="current-password"
                                       required
                                       oninvalid="this.setCustomValidity(this.validity.valueMissing ? 'Please fill out this field.' : '')"
                                       oninput="this.setCustomValidity('')">
                                <button type="button" class="toggle" aria-pressed="false"
                                        aria-label="Show password">
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

                        <div class="options">
                            <label class="check">
                                <input type="checkbox" id="remember" name="remember">
                                <span class="box" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 12.5l5 5L20 6.5"/>
                                    </svg>
                                </span>
                                <span class="label">Remember me</span>
                            </label>
                            <a class="forgot" href="<?= base_url('forgot-password') ?>">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <span class="spinner" aria-hidden="true"></span>
                            <span class="btn-label">Sign in</span>
                        </button>
                    </form>

                    <div class="divider">or</div>

                    <a class="btn btn-guest" href="<?= base_url('guest') ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14"/>
                            <path d="M13 6l6 6-6 6"/>
                        </svg>
                        Continue as guest
                    </a>

                    <p class="card-foot">Sign-in activity is recorded for security.</p>
                </div>
            </aside>
        </main>

    </div>

    <script src="<?= base_url('assets/js/global-loader.js?v=20260928a') ?>"></script>
    <script src="<?= base_url('assets/js/auto-dismiss-alerts.js') ?>"></script>
    <script src="<?= base_url('js/ws-client.js?v=20260928_2') ?>"></script>
    <script>
    // Keep the editable login copy in sync with Superadmin's identity settings.
    document.addEventListener('pttm:branding-applied', function (e) {
        var data = e.detail || {};
        if (data.category && data.category !== 'identity') return;
        var brand = document.querySelector('.brand-title');
        var brandSub = document.querySelector('.brand-sub');
        if (brand && data.app_name) brand.textContent = data.app_name;
        if (brandSub && data.app_subtitle) brandSub.textContent = data.app_subtitle;
        var kicker = document.querySelector('.hero-copy .kicker');
        if (kicker && data.login_kicker) kicker.textContent = data.login_kicker;
        var headline = document.querySelector('.hero-copy h1');
        if (headline && data.login_headline) {
            var words = data.login_headline.trim().split(/\s+/);
            if (words.length >= 2) {
                var accent = document.createElement('span');
                accent.className = 'accent';
                accent.textContent = words.splice(-2).join(' ');
                headline.replaceChildren(document.createTextNode(words.join(' ') + ' '), accent);
            } else {
                headline.textContent = data.login_headline;
            }
        }
        var description = document.querySelector('.hero-copy .lede');
        if (description && data.app_name && data.login_subheadline) {
            description.textContent = data.app_name + ' ' + data.login_subheadline;
        }
        var features = document.querySelectorAll('.feature-text');
        if (features[0]) {
            if (data.login_feature1_title) features[0].querySelector('b').textContent = data.login_feature1_title;
            if (data.login_feature1_desc) features[0].querySelector('span').textContent = data.login_feature1_desc;
        }
        if (features[1]) {
            if (data.login_feature2_title) features[1].querySelector('b').textContent = data.login_feature2_title;
            if (data.login_feature2_desc) features[1].querySelector('span').textContent = data.login_feature2_desc;
        }
        var artwork = document.querySelector('.hero-art');
        var support = document.querySelector('.hero-support');
        if (artwork && support && typeof data.has_custom_login_card === 'boolean') {
            artwork.classList.toggle('has-custom-artwork', data.has_custom_login_card);
            support.classList.toggle('has-custom-artwork', data.has_custom_login_card);
        }
    });
    </script>
    <script>
        (function () {
            // --- Show / hide password ---
            var toggle = document.querySelector('.toggle');
            var password = document.getElementById('password');
            if (toggle && password) {
                toggle.addEventListener('click', function () {
                    var show = toggle.getAttribute('aria-pressed') !== 'true';
                    password.type = show ? 'text' : 'password';
                    toggle.setAttribute('aria-pressed', String(show));
                    toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                });
            }

            // --- Form validation and soft loading state on submit ---
            var form = document.getElementById('loginForm') || document.querySelector('form');
            var submit = form ? form.querySelector('.btn-primary') : null;
            if (form && submit) {
                form.addEventListener('submit', function (e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        form.reportValidity();
                        return false;
                    }
                    submit.classList.add('is-loading');
                    var label = submit.querySelector('.btn-label');
                    if (label) label.textContent = 'Signing in\u2026';
                });
            }
        })();
    </script>
</body>
</html>
