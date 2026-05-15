<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Palompon Transit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-wrapper {
            display: flex;
            width: 95%;
            max-width: 1100px;
            min-height: 600px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .mobile-logo-header {
            display: none;
        }

        /* Desktop Branding Styles */
        .login-branding {
            flex: 1;
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-right: 3px solid #0052cc;
        }

        .branding-content {
            width: 100%;
        }

        .vehicle-icon {
            font-size: 60px;
            color: #0052cc;
            margin-bottom: 15px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }

        .branding-content h1 {
            color: #0052cc;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .branding-content p {
            color: #0052cc;
            font-size: 14px;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .features-list {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .feature-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 82, 204, 0.2);
            transition: all 0.3s ease;
        }

        .feature-box:hover {
            transform: translateX(10px);
            box-shadow: 0 6px 20px rgba(0, 82, 204, 0.3);
        }

        .feature-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #0052cc 0%, #003d99 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .feature-text h3 {
            color: #0052cc;
            font-size: 14px;
            font-weight: 700;
            margin: 0;
        }

        .feature-text p {
            color: #666;
            font-size: 12px;
            margin: 0;
            opacity: 0.8;
        }

        /* Right Side - Login Form */
        .login-form-section {
            flex: 1;
            padding: 40px 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }

        .login-form-section h2 {
            color: #1a3a7a;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .login-form-section > p {
            color: #666;
            font-size: 15px;
            margin-bottom: 35px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #0052cc;
            box-shadow: 0 0 0 4px rgba(0, 82, 204, 0.1);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .remember-forgot a {
            color: #0052cc;
            text-decoration: none;
            font-weight: 600;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #0052cc;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #0052cc 0%, #003d99 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 82, 204, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 82, 204, 0.4);
        }

        .guest-link {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
        }

        .guest-link a {
            color: #0052cc;
            text-decoration: none;
            font-weight: 600;
        }

        .alert {
            margin-bottom: 25px;
            border-radius: 8px;
            padding: 14px;
            font-size: 14px;
            border-left: 5px solid #d9534f;
            background: #fff5f5;
            color: #d9534f;
        }

        /* Responsive Overrides (Placed at bottom to ensure specificity) */
        @media (max-width: 991px) {
            body {
                padding: 0;
                align-items: flex-start;
                background: white; /* Clean background for mobile */
            }
            .login-wrapper {
                flex-direction: column;
                width: 100%;
                max-width: 100%;
                border-radius: 0;
                border: none;
                box-shadow: none;
                min-height: 100vh;
            }
            .login-branding {
                display: none !important; /* Force hide branding on mobile */
            }
            .mobile-logo-header {
                display: flex !important;
                align-items: center;
                gap: 12px;
                margin-bottom: 25px;
                padding: 15px 0;
                border-bottom: 1px solid #eee;
            }
            .mobile-logo-header i {
                font-size: 28px;
                color: #0052cc;
            }
            .mobile-logo-header h1 {
                font-size: 22px;
                font-weight: 700;
                color: #0052cc;
                margin: 0;
            }
            .login-form-section {
                padding: 20px 20px;
                justify-content: flex-start;
            }
            .login-form-section h2 {
                font-size: 26px;
                margin-bottom: 5px;
            }
            .login-form-section > p {
                font-size: 14px;
                margin-bottom: 25px;
            }
            .form-group {
                margin-bottom: 15px;
            }
            .form-group label {
                margin-bottom: 5px;
            }
            .form-group input {
                padding: 12px 14px;
            }
            .remember-forgot {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <!-- Left Side - Branding (Desktop Only) -->
        <div class="login-branding">
            <div class="branding-content">
                <div class="vehicle-icon">
                    <i class="fas fa-shuttle-van"></i>
                </div>
                <h1>Palompon Transit</h1>
                <p>Real-Time Terminal Monitoring System</p>
            </div>

            <div class="features-list">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="feature-text">
                        <h3>Real-Time</h3>
                        <p>Live system updates</p>
                    </div>
                </div>

                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="feature-text">
                        <h3>Analytics</h3>
                        <p>Smart reports</p>
                    </div>
                </div>

                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="feature-text">
                        <h3>Secure</h3>
                        <p>Protected data</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-form-section">
            <!-- Mobile-Only Compact Header -->
            <div class="mobile-logo-header">
                <i class="fas fa-shuttle-van"></i>
                <h1>Palompon Transit</h1>
            </div>

            <h2>Welcome Back!</h2>
            <p>Login to access the terminal monitoring system</p>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Enter your username" 
                        required 
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password" 
                        required
                    >
                </div>

                <div class="remember-forgot">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="<?= base_url('forgot-password') ?>">Forgot password?</a>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                </button>
            </form>

            <div class="guest-link">
                <a href="<?= base_url('guest') ?>">Continue as Guest</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
