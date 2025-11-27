<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Video Call App')</title>
    <meta name="user-id" content="{{ Auth::id() ?? '' }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .decorative-side {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            position: relative;
            overflow: hidden;
        }
        .decorative-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
        }
        .shape-1 {
            width: 300px;
            height: 300px;
            background: white;
            top: -100px;
            right: -100px;
        }
        .shape-2 {
            width: 200px;
            height: 200px;
            background: white;
            bottom: -50px;
            left: -50px;
        }
        .shape-3 {
            width: 150px;
            height: 150px;
            background: white;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <div class="min-h-screen flex flex-col">
        @auth
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}" style="color: #667eea;">
                    <i class="bi bi-camera-video me-2"></i>Chats WhatsApp
                </a>
                <div class="d-flex align-items-center gap-2">
                    <!-- Camera Icon -->
                    <button class="btn btn-sm rounded-circle" style="width: 35px; height: 35px; background: transparent; border: none; color: #667eea;" onclick="openCamera()" title="Camera">
                        <i class="bi bi-camera-fill fs-5"></i>
                    </button>
                    <!-- Scanner/QR Icon -->
                    <button class="btn btn-sm rounded-circle" style="width: 35px; height: 35px; background: transparent; border: none; color: #667eea;" onclick="openScanner()" title="QR Scanner">
                        <i class="bi bi-qr-code-scan fs-5"></i>
                    </button>
                    <!-- 3 Dots Menu -->
                    <div class="dropdown">
                        <button class="btn btn-sm rounded-circle dropdown-toggle" type="button" id="headerMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="width: 35px; height: 35px; background: transparent; border: none; color: #667eea;">
                            <i class="bi bi-three-dots-vertical fs-5"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="headerMenuDropdown">
                            <li><a class="dropdown-item" href="#" onclick="openProfileModal(); return false;"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        @endauth

        <main class="flex-1">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show m-4 rounded-3 border-0 shadow-sm" role="alert" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <span class="fw-semibold">{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show m-4 rounded-3 border-0 shadow-sm" role="alert" style="background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5 mt-1"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li class="fw-semibold">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Open camera - will be overridden in dashboard if available
        function openCamera() {
            // Check if camera modal exists (dashboard page)
            const cameraModal = document.getElementById('cameraModal');
            if (cameraModal) {
                // Camera functionality is in dashboard.blade.php
                // This will be overridden by dashboard's openCamera function
                return;
            }
            alert('Please navigate to dashboard to use camera');
        }

        // Open scanner/QR code
        function openScanner() {
            alert('QR Scanner feature coming soon!');
            // Can implement QR scanner functionality here
        }
    </script>
</body>
</html>

