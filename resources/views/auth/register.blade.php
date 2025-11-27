@extends('layouts.app')

@section('title', 'Register - Video Call App')

@section('content')
<div class="container-fluid p-0 min-vh-100">
    <div class="row g-0 min-vh-100">
        <!-- Left Side - Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center p-4" style="background: linear-gradient(to bottom, #f8f9fa, #e9ecef);">
            <div class="w-100" style="max-width: 450px;">
                <!-- Logo -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-gradient text-white mb-3" 
                         style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="bi bi-camera-video fs-4"></i>
                    </div>
                    <h2 class="fw-bold mb-2" style="color: #2d3748;">Create an account</h2>
                    <p class="text-muted small">Sign up and get started with video calls</p>
                </div>

                <!-- Form Card -->
                <div class="glass-effect rounded-4 p-4 shadow-lg">
                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        
                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold" style="color: #4a5568;">Full name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-0" id="name" name="name" 
                                       placeholder="Enter your full name" value="{{ old('name') }}" required>
                            </div>
                            @error('name')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold" style="color: #4a5568;">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" 
                                       placeholder="Enter your email address" value="{{ old('email') }}" required>
                            </div>
                            @error('email')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold" style="color: #4a5568;">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" 
                                       placeholder="Enter your password" required>
                                <span class="input-group-text bg-light border-start-0 cursor-pointer" onclick="togglePassword('password')">
                                    <i class="bi bi-eye text-muted" id="togglePasswordIcon"></i>
                                </span>
                            </div>
                            @error('password')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold" style="color: #4a5568;">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock-fill text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 ps-0" id="password_confirmation" name="password_confirmation" 
                                       placeholder="Confirm your password" required>
                                <span class="input-group-text bg-light border-start-0 cursor-pointer" onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye text-muted" id="togglePasswordConfIcon"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-gradient text-white w-100 py-3 rounded-3 fw-semibold mb-3">
                            Submit
                        </button>

                        <!-- Divider -->
                        <div class="d-flex align-items-center my-4">
                            <hr class="flex-grow-1">
                            <span class="px-3 text-muted small">Or, Sign up with</span>
                            <hr class="flex-grow-1">
                        </div>

                        <!-- Social Login -->
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-secondary rounded-3 py-2">
                                <i class="bi bi-google me-2"></i> Sign up with Google
                            </button>
                            <button type="button" class="btn btn-outline-dark rounded-3 py-2">
                                <i class="bi bi-apple me-2"></i> Sign up with Apple
                            </button>
                        </div>

                        <!-- Login Link -->
                        <div class="text-center mt-4">
                            <p class="text-muted small mb-0">
                                Already have an account? 
                                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold" style="color: #667eea;">Sign in</a>
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
            
            <!-- Content Overlay -->
            <div class="position-relative text-center text-white p-5" style="z-index: 1;">
                <div class="mb-4">
                    <i class="bi bi-camera-video-fill" style="font-size: 80px; opacity: 0.9;"></i>
                </div>
                <h3 class="fw-bold mb-3" style="font-size: 2.5rem;">Connect with Anyone</h3>
                <p class="lead mb-4" style="opacity: 0.9;">High-quality video calls with friends, family, and colleagues</p>
                <div class="d-flex justify-content-center gap-4">
                    <div class="text-center">
                        <div class="fs-3 fw-bold">HD</div>
                        <small>Quality</small>
                    </div>
                    <div class="text-center">
                        <div class="fs-3 fw-bold">24/7</div>
                        <small>Available</small>
                    </div>
                    <div class="text-center">
                        <div class="fs-3 fw-bold">Free</div>
                        <small>Forever</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId === 'password' ? 'togglePasswordIcon' : 'togglePasswordConfIcon');
    
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
