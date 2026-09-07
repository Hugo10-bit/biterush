<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BitesRush! - Login</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100vh;
            max-height: 100vh;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
            background: #ffffff;
            scroll-behavior: smooth;
        }

        body {
            display: flex;
            width: 100vw;
            animation: pageEnter 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1), transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }

        body.page-exiting {
            opacity: 0 !important;
            transform: translateY(-4px) scale(0.995);
        }

        @keyframes pageEnter {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #page-loader-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3.5px;
            width: 0%;
            background: linear-gradient(90deg, #FFC72C, #F9961F, #D92625);
            z-index: 99999;
            transition: width 0.35s ease, opacity 0.3s ease;
            pointer-events: none;
            box-shadow: 0 0 10px rgba(249, 150, 31, 0.7);
        }

        /* ── LEFT PANEL (FORM SIDE) ── */
        .panel-left {
            width: 50%;
            height: 100vh;
            max-height: 100vh;
            overflow: hidden;
            overflow-y: hidden;
            scrollbar-width: none;
            -ms-overflow-style: none;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 48px;
            box-sizing: border-box;
        }

        .panel-left::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }

        /* ── RIGHT PANEL (IMAGE SIDE) ── */
        .panel-right {
            width: 50%;
            height: 100vh;
            max-height: 100vh;
            background: linear-gradient(160deg, #ffb347 0%, #f9961f 40%, #e07b00 75%, #c05400 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .panel-right img.bg-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            display: block;
            user-select: none;
        }

        /* ── LOGO ── */
        .logo-wrap {
            width: 100%;
            max-width: 440px;
            margin-bottom: 20px;
            margin-top: 0;
        }

        .logo-wrap img {
            height: 65px;
            width: auto;
            object-fit: contain;
        }

        /* ── FORM AREA ── */
        .form-area {
            display: flex;
            flex-direction: column;
            justify-content: center;
            max-width: 440px;
            width: 100%;
        }

        .form-area h1 {
            font-size: 32px;
            font-weight: 700;
            color: #0b0b0b;
            line-height: 1.2;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .form-area p.subtitle {
            font-size: 14px;
            color: #8e8e8e;
            font-weight: 400;
            margin-bottom: 20px;
        }

        /* ── LABELS ── */
        label.field-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        /* ── INPUTS ── */
        .input-wrap {
            position: relative;
            margin-bottom: 14px;
        }

        .input-wrap input {
            width: 100%;
            height: 46px;
            padding: 0 44px 0 16px;
            font-size: 13.5px;
            font-family: 'Poppins', sans-serif;
            color: #1f2937;
            background: #ffffff;
            border: 1.2px solid #d1d5db;
            border-radius: 9px;
            outline: none;
            transition: all 0.2s ease;
        }

        .input-wrap input::placeholder {
            color: #9ca3af;
            font-size: 13px;
        }

        .input-wrap input:focus {
            border-color: #f9961f;
            box-shadow: 0 0 0 3px rgba(249, 150, 31, 0.15);
        }

        .input-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #4b5563;
            display: flex;
            align-items: center;
        }

        .input-icon button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            color: #4b5563;
            display: flex;
            align-items: center;
        }

        .input-icon button:hover { color: #111; }

        /* ── REMEMBER / FORGOT ── */
        .row-check {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 2px;
            margin-bottom: 16px;
        }

        .check-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #1a1a1a;
            cursor: pointer;
            user-select: none;
        }

        .custom-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            border: 1.2px solid #9ca3af;
            border-radius: 4px;
            outline: none;
            cursor: pointer;
            position: relative;
            background: #ffffff;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }

        .custom-checkbox:checked {
            background-color: #f9961f;
            border-color: #f9961f;
        }

        .custom-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 4.5px;
            top: 2px;
            width: 4px;
            height: 7px;
            border: solid #ffffff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .forgot-link {
            font-size: 13px;
            color: #1a1a1a;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-link:hover { color: #f9961f; }

        /* ── BUTTONS ── */
        .btn-primary {
            width: 100%;
            height: 48px;
            background: #f9961f;
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 9px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 3px 8px rgba(249, 150, 31, 0.3);
            margin-bottom: 14px;
        }

        .btn-primary:hover  {
            background: #e88610;
            box-shadow: 0 4px 14px rgba(249, 150, 31, 0.4);
        }
        .btn-primary:active { transform: scale(0.99); }

        /* ── SWITCH LINK ── */
        .switch-line {
            font-size: 13px;
            color: #1f2937;
        }

        .switch-line a {
            color: #ef4444;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }

        .switch-line a:hover {
            color: #dc2626;
            text-decoration: underline;
        }

        /* ── ALERT MESSAGES ── */
        .alert-error {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff5f5;
            border: 1px solid #fecaca;
            color: #D92625;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .alert-success {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .hidden { display: none !important; }

        /* ── DIVIDER ── */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 4px 0 14px;
            color: #9ca3af;
            font-size: 12.5px;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        /* ── GOOGLE BUTTON ── */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 46px;
            background: #ffffff;
            border: 1.2px solid #d1d5db;
            border-radius: 9px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            margin-bottom: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .btn-google:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            color: #111;
        }
        .btn-google svg {
            flex-shrink: 0;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            html, body {
                height: auto;
                max-height: none;
                overflow-y: auto;
            }
            .panel-right { display: none; }
            .panel-left  {
                width: 100%;
                height: auto;
                min-height: 100vh;
                max-height: none;
                padding: 32px 28px;
            }
            .form-area { max-width: 100%; margin-top: 0; }
        }
    </style>
</head>
<body>
    <div id="page-loader-bar"></div>

    <!-- ══════════════ LEFT PANEL (FORM) ══════════════ -->
    <div class="panel-left">

        <!-- Logo -->
        <div class="logo-wrap">
            <img src="{{ asset('images/logo.png') }}" alt="BitesRush!">
        </div>

        <!-- Form Area -->
        <div class="form-area">

            <!-- Alerts -->
            @if (session('error'))
                <div class="alert-error">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-error">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if (session('status'))
                <div class="alert-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- ═══ LOGIN SECTION ═══ -->
            <div id="login-section" class="{{ ($mode ?? 'login') !== 'login' ? 'hidden' : '' }}">
                <h1>Welcome Back!</h1>
                <p class="subtitle">Please enter your detail first</p>

                <form method="POST" action="{{ url('/login') }}" id="login-form">
                    @csrf

                    <!-- Username -->
                    <label class="field-label" for="login_username">Username</label>
                    <div class="input-wrap">
                        <input
                            type="text"
                            id="login_username"
                            name="username"
                            placeholder="Enter your Username"
                            value="{{ old('username') }}"
                            required
                            autocomplete="username"
                        >
                        <span class="input-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                    </div>

                    <!-- Password -->
                    <label class="field-label" for="login_password">Password</label>
                    <div class="input-wrap">
                        <input
                            type="password"
                            id="login_password"
                            name="password"
                            placeholder="Enter your Password"
                            required
                            autocomplete="current-password"
                        >
                        <span class="input-icon">
                            <button type="button" class="toggle-password-btn" data-target="login_password" aria-label="Toggle Password">
                                <svg class="icon-eye" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="icon-eye-off hidden" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                                    <line x1="2" y1="2" x2="22" y2="22"></line>
                                </svg>
                            </button>
                        </span>
                    </div>

                    <!-- Remember Me + Forgot Password -->
                    <div class="row-check">
                        <label class="check-label">
                            <input type="checkbox" name="remember" id="remember" class="custom-checkbox">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-link">Forgot Password?</a>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-primary">Login</button>
                </form>

                <!-- Divider -->
                <div class="divider">Or continue with</div>

                <!-- Google Login -->
                <a href="{{ route('auth.google') }}" class="btn-google" id="btn-google-login">
                    <svg width="18" height="18" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12s5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z" fill="#FFC107"/>
                        <path d="M6.306,14.691l6.571,4.819C14.655,15.108,19.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z" fill="#FF3D00"/>
                        <path d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z" fill="#4CAF50"/>
                        <path d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z" fill="#1976D2"/>
                    </svg>
                    Login with Google
                </a>

                <p class="switch-line">
                    don't have an account? <a href="javascript:void(0)" onclick="switchForm('register')">Create one</a>
                </p>
            </div>

            <!-- ═══ REGISTER SECTION ═══ -->
            <div id="register-section" class="{{ ($mode ?? '') !== 'register' ? 'hidden' : '' }}">
                <h1>Create Account</h1>
                <p class="subtitle">Fill in your details to get started</p>

                <form method="POST" action="{{ url('/register') }}" id="register-form">
                    @csrf

                    <!-- Username -->
                    <label class="field-label" for="reg_username">Username</label>
                    <div class="input-wrap">
                        <input type="text" id="reg_username" name="username" placeholder="Enter your Username" required>
                        <span class="input-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                    </div>

                    <!-- Password -->
                    <label class="field-label" for="reg_password">Password</label>
                    <div class="input-wrap">
                        <input type="password" id="reg_password" name="password" placeholder="Enter your Password" required>
                        <span class="input-icon">
                            <button type="button" class="toggle-password-btn" data-target="reg_password" aria-label="Toggle Password">
                                <svg class="icon-eye" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="icon-eye-off hidden" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                                    <line x1="2" y1="2" x2="22" y2="22"></line>
                                </svg>
                            </button>
                        </span>
                    </div>

                    <!-- Email -->
                    <label class="field-label" for="reg_email">Email</label>
                    <div class="input-wrap">
                        <input type="email" id="reg_email" name="email" placeholder="Enter your Email" required>
                        <span class="input-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                        </span>
                    </div>

                    <!-- Phone -->
                    <label class="field-label" for="reg_phone">Phone Number</label>
                    <div class="input-wrap">
                        <input type="tel" id="reg_phone" name="phone_number" placeholder="Enter your Number">
                        <span class="input-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 11.5a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.61 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6.08 6.08l.96-.96a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                        </span>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-primary" style="margin-top:6px;">Register</button>
                </form>

                <!-- Divider -->
                <div class="divider">Or continue with</div>

                <!-- Google Register -->
                <a href="{{ route('auth.google') }}" class="btn-google" id="btn-google-register">
                    <svg width="18" height="18" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12s5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z" fill="#FFC107"/>
                        <path d="M6.306,14.691l6.571,4.819C14.655,15.108,19.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z" fill="#FF3D00"/>
                        <path d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z" fill="#4CAF50"/>
                        <path d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z" fill="#1976D2"/>
                    </svg>
                    Sign up with Google
                </a>

                <p class="switch-line">
                    Already have an account? <a href="javascript:void(0)" onclick="switchForm('login')">Click to Login</a>
                </p>
            </div>

        </div><!-- /form-area -->
    </div><!-- /panel-left -->

    <!-- ══════════════ RIGHT PANEL (IMAGE) ══════════════ -->
    <div class="panel-right">
        <img class="bg-image" src="{{ asset('images/burger-bg.jpg') }}" alt="Crispy burger with cheese sauce">
    </div>

    <script>
        function switchForm(mode) {
            const loginSec = document.getElementById('login-section');
            const regSec   = document.getElementById('register-section');
            if (mode === 'register') {
                loginSec.classList.add('hidden');
                regSec.classList.remove('hidden');
                history.pushState(null, '', '{{ url("/register") }}');
            } else {
                regSec.classList.add('hidden');
                loginSec.classList.remove('hidden');
                history.pushState(null, '', '{{ url("/login") }}');
            }
        }

        document.querySelectorAll('.toggle-password-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const input    = document.getElementById(this.dataset.target);
                const eye      = this.querySelector('.icon-eye');
                const eyeOff   = this.querySelector('.icon-eye-off');
                const isPass   = input.type === 'password';
                input.type     = isPass ? 'text' : 'password';
                eye.classList.toggle('hidden', isPass);
                eyeOff.classList.toggle('hidden', !isPass);
            });
        });

        window.addEventListener('popstate', function () {
            switchForm(window.location.pathname.includes('register') ? 'register' : 'login');
        });

        // Smooth Page Transition Handler
        document.addEventListener('DOMContentLoaded', () => {
            const loader = document.getElementById('page-loader-bar');
            if (loader) {
                loader.style.width = '100%';
                setTimeout(() => { loader.style.opacity = '0'; }, 200);
            }

            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                const target = link.getAttribute('target');

                if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:') || target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
                    return;
                }

                if (link.hostname === window.location.hostname) {
                    if (loader) {
                        loader.style.opacity = '1';
                        loader.style.width = '70%';
                    }
                    document.body.classList.add('page-exiting');
                }
            });
        });

        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                document.body.classList.remove('page-exiting');
                const loader = document.getElementById('page-loader-bar');
                if (loader) {
                    loader.style.width = '100%';
                    setTimeout(() => { loader.style.opacity = '0'; loader.style.width = '0%'; }, 200);
                }
            }
        });
    </script>
</body>
</html>
