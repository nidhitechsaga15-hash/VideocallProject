@extends('layouts.app')

@section('title', 'Login - Video Call App')

@section('content')
<div class="container-fluid p-0 min-vh-100">
    <div class="row g-0 min-vh-100">
        <!-- Left Side - Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center p-4" style="background: linear-gradient(to bottom, #ffffff, #f8f9fa);">
            <div class="w-100" style="max-width: 450px;">
                <!-- Logo -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-gradient text-white mb-3" 
                         style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="bi bi-camera-video fs-4"></i>
                    </div>
                    <h2 class="fw-bold mb-2" style="color: #2d3748;">Welcome back!</h2>
                    <p class="text-muted small">Enter to get unlimited access to video calls & information</p>
                </div>

                <!-- Form Card -->
                <div class="glass-effect rounded-4 p-4 shadow-lg">
                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold" style="color: #4a5568;">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" 
                                       placeholder="Enter your mail address" value="{{ old('email') }}" required>
                            </div>
                            @error('email')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold" style="color: #4a5568;">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" 
                                       placeholder="Enter password" required>
                                <span class="input-group-text bg-light border-start-0 cursor-pointer" onclick="togglePassword('password')">
                                    <i class="bi bi-eye text-muted" id="togglePasswordIcon"></i>
                                </span>
                            </div>
                            @error('password')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember" style="accent-color: #667eea;">
                                <label class="form-check-label text-muted small" for="remember">
                                    Remember me
                                </label>
                            </div>
                            <a href="#" class="text-decoration-none small fw-semibold" style="color: #667eea;">Forgot your password?</a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-gradient text-white w-100 py-3 rounded-3 fw-semibold mb-3">
                            Log In
                        </button>

                        <!-- Divider -->
                        <div class="d-flex align-items-center my-4">
                            <hr class="flex-grow-1">
                            <span class="px-3 text-muted small">Or, Login with</span>
                            <hr class="flex-grow-1">
                        </div>

                        <!-- Social Login -->
                        <div class="d-grid">
                            <a href="{{ route('auth.google') }}" class="btn btn-outline-secondary rounded-3 py-2 text-decoration-none">
                                <i class="bi bi-google me-2"></i> Sign in with Google
                            </a>
                        </div>

                        <!-- Register Link -->
                        <div class="text-center mt-4">
                            <p class="text-muted small mb-0">
                                Don't have an account? 
                                <a href="{{ route('register') }}" class="text-decoration-none fw-semibold" style="color: #667eea;">Create one now</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side - Decorative -->
        <div class="col-lg-6 decorative-side d-none d-lg-flex align-items-center justify-content-center position-relative">
            <div class="decorative-shape shape-1"></div>
            <div class="decorative-shape shape-2"></div>
            <div class="decorative-shape shape-3"></div>
            
            <!-- Abstract Design Elements -->
            <div class="position-relative" style="z-index: 1; width: 100%; height: 100%;">
                <!-- Geometric Shapes -->
                <div class="position-absolute" style="top: 10%; left: 10%; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 20px; transform: rotate(45deg);"></div>
                <div class="position-absolute" style="top: 20%; right: 15%; width: 80px; height: 80px; background: rgba(255,255,255,0.15); border-radius: 50%;"></div>
                <div class="position-absolute" style="bottom: 20%; left: 15%; width: 120px; height: 120px; background: rgba(255,255,255,0.1); border-radius: 30px; transform: rotate(-30deg);"></div>
                <div class="position-absolute" style="bottom: 30%; right: 10%; width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%;"></div>
                
                <!-- Content Overlay -->
                <div class="position-absolute top-50 start-50 translate-middle text-center text-white p-5">
                    <div class="mb-4">
                        <i class="bi bi-people-fill" style="font-size: 80px; opacity: 0.9;"></i>
                    </div>
                    <h3 class="fw-bold mb-3" style="font-size: 2.5rem;">Stay Connected</h3>
                    <p class="lead mb-4" style="opacity: 0.9;">Seamless video communication for everyone</p>
                    <div class="d-flex justify-content-center gap-4 flex-wrap">
                        <div class="text-center">
                            <i class="bi bi-shield-check fs-2 d-block mb-2"></i>
                            <small>Secure</small>
                        </div>
                        <div class="text-center">
                            <i class="bi bi-lightning-charge fs-2 d-block mb-2"></i>
                            <small>Fast</small>
                        </div>
                        <div class="text-center">
                            <i class="bi bi-globe fs-2 d-block mb-2"></i>
                            <small>Global</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById('togglePasswordIcon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
@endsection
