<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <title>Login - BIS System</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #10b981 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        /* Background overlay pattern/gradient effect */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 20%);
            pointer-events: none;
            z-index: 0;
        }

        .main-container {
            width: 100%;
            max-width: 1200px;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
        }

        .brand-section {
            color: white;
            margin-bottom: 2rem;
        }

        .logo-img {
            width: 100%;
            max-width: 500px;
            height: auto;
            object-fit: contain;
            margin-bottom: 1.5rem;
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .company-name {
            display: none;
        }

        .system-title {
            font-size: 3rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .form-control {
            border-radius: 10px;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            /* Space for icon */
            border: 1px solid #ced4da;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 1rem;
            z-index: 10;
        }

        .btn-login {
            background-color: #1d4ed8;
            /* Blue similar to reference */
            border: none;
            border-radius: 25px;
            padding: 0.8rem;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: #1e40af;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(29, 78, 216, 0.3);
        }

        .forgot-link {
            font-size: 0.85rem;
            color: #64748b;
            text-decoration: none;
            margin-top: 1rem;
            display: inline-block;
        }

        .forgot-link:hover {
            color: #1d4ed8;
        }

        .signup-text {
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: #64748b;
        }

        /* Decorative blobs matching reference style */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.5;
        }

        .blob-1 {
            width: 300px;
            height: 300px;
            background: #3b82f6;
            top: -100px;
            left: -100px;
        }

        .blob-2 {
            width: 400px;
            height: 400px;
            background: #10b981;
            bottom: -100px;
            right: -100px;
        }

        @media (max-width: 991.98px) {
            .main-container {
                flex-direction: column;
                padding: 1rem;
            }

            .brand-section {
                text-align: center;
                margin-bottom: 2rem;
            }

            .system-title {
                font-size: 2rem;
            }

            .login-card {
                margin: 0 auto;
            }

            body {
                height: auto;
                padding: 2rem 0;
                overflow-y: auto;
            }
        }
    </style>
</head>

<body>

    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="main-container d-flex align-items-center justify-content-between w-100 h-100">

        <!-- Left Section: Branding -->
        <div class="brand-section col-lg-6 pe-lg-5">
            <img src="{{ asset('images/Update Logo DEM 2022 V2.png') }}" alt="Logo" class="logo-img">
            <div class="company-name">PT DHARMA ELECTRINDO MFG.</div>
            <h1 class="system-title">BIS - Budget &<br>Investment<br>System</h1>
        </div>

        <!-- Right Section: Login Card -->
        <div class="col-lg-5 d-flex justify-content-center justify-content-lg-end">
            <div class="login-card">
                <form action="{{ route('login.post') }}" method="POST">
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4 small border-0 shadow-sm">
                            <i class="fas fa-exclamation-circle me-2"></i> {{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="mb-3 position-relative">
                        <i class="far fa-envelope input-icon"></i>
                        <input type="text" name="username" class="form-control" placeholder="Username / Email Address"
                            required autofocus>
                    </div>

                    <div class="mb-4 position-relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login text-white">LOGIN</button>


                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>