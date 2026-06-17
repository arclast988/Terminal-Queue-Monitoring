<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code - Palompon Transit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #0d47a1 0%, #1565c0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            padding: 20px;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 44px 40px 36px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            animation: slideUp 0.5s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Icon area ── */
        .icon-area {
            text-align: center;
            margin-bottom: 24px;
        }
        .icon-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(21,101,192,0.25); }
            50%      { box-shadow: 0 0 0 14px rgba(21,101,192,0); }
        }
        .icon-circle i {
            font-size: 30px;
            color: #1565c0;
        }

        /* ── Typography ── */
        .card h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 6px;
            text-align: center;
        }
        .card .subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 28px;
            text-align: center;
            line-height: 1.5;
        }
        .email-highlight {
            color: #1565c0;
            font-weight: 600;
        }

        /* ── OTP digit inputs ── */
        .otp-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 28px;
        }
        .otp-input {
            width: 52px;
            height: 60px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            font-family: 'Outfit', monospace;
            color: #1a1a2e;
            outline: none;
            transition: all 0.2s ease;
            caret-color: #1565c0;
        }
        .otp-input:focus {
            border-color: #1565c0;
            box-shadow: 0 0 0 3px rgba(21,101,192,0.12);
            transform: scale(1.05);
        }
        .otp-input.filled {
            border-color: #1565c0;
            background: #f0f7ff;
        }
        .otp-input.error {
            border-color: #ef4444;
            background: #fef2f2;
            animation: shake 0.4s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            50% { transform: translateX(6px); }
            75% { transform: translateX(-4px); }
        }

        /* ── Submit button ── */
        .btn-verify {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(21,101,192,0.3);
        }
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(21,101,192,0.4);
        }
        .btn-verify:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .btn-verify .spinner {
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Resend area ── */
        .resend-area {
            text-align: center;
            margin-top: 22px;
            font-size: 14px;
            color: #6b7280;
        }
        .resend-link {
            color: #1565c0;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.2s;
        }
        .resend-link:hover { color: #0d47a1; }
        .resend-link.disabled {
            color: #9ca3af;
            cursor: not-allowed;
            pointer-events: none;
        }
        .countdown {
            font-weight: 600;
            color: #1565c0;
        }

        /* ── Alerts ── */
        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        /* ── Back link ── */
        .back-link {
            text-align: center;
            margin-top: 16px;
        }
        .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }
        .back-link a:hover { color: #1565c0; }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .card { padding: 32px 22px 28px; }
            .otp-input { width: 44px; height: 52px; font-size: 20px; }
            .otp-container { gap: 7px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <!-- Icon -->
        <div class="icon-area">
            <div class="icon-circle">
                <i class="fas fa-envelope-open-text"></i>
            </div>
        </div>

        <h2>Check Your Email</h2>
        <p class="subtitle">
            We've sent a 6-digit verification code to<br>
            <span class="email-highlight"><?= esc($masked_email ?? '***@***.com') ?></span>
        </p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <form id="verifyForm" action="<?= base_url('verify-reset-code/' . $token) ?>" method="post">
            <?= csrf_field() ?>

            <!-- 6-digit OTP inputs -->
            <div class="otp-container" id="otpContainer">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code" data-index="0" autofocus>
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="1">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="2">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="3">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="4">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="5">
            </div>

            <!-- Hidden field that receives the combined code -->
            <input type="hidden" name="reset_code" id="resetCode">

            <button type="submit" class="btn-verify" id="btnVerify" disabled>
                <span class="spinner" id="spinner"></span>
                <i class="fas fa-shield-halved" id="btnIcon"></i>
                <span id="btnText">Verify Code</span>
            </button>
        </form>

        <!-- Resend -->
        <div class="resend-area">
            <span id="resendWait">
                Resend code in <span class="countdown" id="countdown">60</span>s
            </span>
            <form id="resendForm" action="<?= base_url('resend-reset-code/' . $token) ?>" method="post" style="display:inline;">
                <?= csrf_field() ?>
                <a class="resend-link disabled" id="resendLink" onclick="document.getElementById('resendForm').submit(); return false;">
                    <i class="fas fa-redo"></i> Resend Code
                </a>
            </form>
        </div>

        <div class="back-link">
            <a href="<?= base_url('login') ?>"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('.otp-input');
        const form   = document.getElementById('verifyForm');
        const hidden = document.getElementById('resetCode');
        const btn    = document.getElementById('btnVerify');
        const spinner = document.getElementById('spinner');
        const btnIcon = document.getElementById('btnIcon');
        const btnText = document.getElementById('btnText');

        // ── Focus / input logic ──
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
            });

            input.addEventListener('focus', function () {
                this.select();
            });

            // Handle paste
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

        // ── Form submit ──
        form.addEventListener('submit', function () {
            btn.disabled = true;
            spinner.style.display = 'inline-block';
            btnIcon.style.display = 'none';
            btnText.textContent = 'Verifying…';
        });

        // ── If there was an error, shake the inputs ──
        <?php if (session()->getFlashdata('error')): ?>
        inputs.forEach(inp => inp.classList.add('error'));
        setTimeout(() => inputs.forEach(inp => inp.classList.remove('error')), 600);
        <?php endif; ?>

        // ── Countdown for resend ──
        let seconds = 60;
        const countdownEl = document.getElementById('countdown');
        const resendWait  = document.getElementById('resendWait');
        const resendLink  = document.getElementById('resendLink');

        const timer = setInterval(function () {
            seconds--;
            countdownEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                resendWait.style.display = 'none';
                resendLink.classList.remove('disabled');
            }
        }, 1000);
    });
    </script>
</body>
</html>
