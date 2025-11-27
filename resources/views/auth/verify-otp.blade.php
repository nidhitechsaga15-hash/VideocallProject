@extends('layouts.app')

@section('title', 'Verify OTP - Video Call App')

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
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                    <h2 class="fw-bold mb-2" style="color: #2d3748;">Verify Your Email</h2>
                    <p class="text-muted small mb-1">We've sent a 6-digit code to</p>
                    <p class="fw-semibold mb-0" style="color: #667eea;">{{ session('email') ?? old('email') }}</p>
                </div>

                <!-- Form Card -->
                <div class="glass-effect rounded-4 p-4 shadow-lg">
                    <form action="{{ route('otp.verify') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="{{ session('email') ?? old('email') }}">
                        
                        <!-- OTP Input -->
                        <div class="mb-4">
                            <label for="otp" class="form-label fw-semibold text-center d-block" style="color: #4a5568;">Enter Verification Code</label>
                            <div class="d-flex justify-content-center gap-2 mb-3" id="otpContainer">
                                <input type="text" class="form-control text-center fw-bold fs-3" 
                                       style="width: 50px; height: 60px; font-size: 1.8rem; letter-spacing: 0.2em;" 
                                       maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp1" required>
                                <input type="text" class="form-control text-center fw-bold fs-3" 
                                       style="width: 50px; height: 60px; font-size: 1.8rem; letter-spacing: 0.2em;" 
                                       maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp2" required>
                                <input type="text" class="form-control text-center fw-bold fs-3" 
                                       style="width: 50px; height: 60px; font-size: 1.8rem; letter-spacing: 0.2em;" 
                                       maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp3" required>
                                <input type="text" class="form-control text-center fw-bold fs-3" 
                                       style="width: 50px; height: 60px; font-size: 1.8rem; letter-spacing: 0.2em;" 
                                       maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp4" required>
                                <input type="text" class="form-control text-center fw-bold fs-3" 
                                       style="width: 50px; height: 60px; font-size: 1.8rem; letter-spacing: 0.2em;" 
                                       maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp5" required>
                                <input type="text" class="form-control text-center fw-bold fs-3" 
                                       style="width: 50px; height: 60px; font-size: 1.8rem; letter-spacing: 0.2em;" 
                                       maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp6" required>
                            </div>
                            <input type="hidden" name="otp" id="otpInput">
                            @error('otp')
                                <small class="text-danger d-block text-center mt-2">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Info Box -->
                        <div class="alert alert-info rounded-3 mb-4 border-0" style="background: rgba(102, 126, 234, 0.1);">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle me-2" style="color: #667eea;"></i>
                                <small class="mb-0" style="color: #667eea;">Code expires in 10 minutes</small>
                            </div>
                        </div>

                        <!-- Verify Button -->
                        <button type="submit" class="btn btn-gradient text-white w-100 py-3 rounded-3 fw-semibold mb-3">
                            <i class="bi bi-check-circle me-2"></i> Verify Code
                        </button>

                        <!-- Resend OTP -->
                        <div class="text-center">
                            <form action="{{ route('otp.resend') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="email" value="{{ session('email') ?? old('email') }}">
                                <p class="text-muted small mb-2">Didn't receive the code?</p>
                                <button type="submit" class="btn btn-link text-decoration-none p-0 fw-semibold" style="color: #667eea;">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Resend OTP
                                </button>
                            </form>
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
                    <i class="bi bi-envelope-check" style="font-size: 80px; opacity: 0.9;"></i>
                </div>
                <h3 class="fw-bold mb-3" style="font-size: 2.5rem;">Email Verification</h3>
                <p class="lead mb-4" style="opacity: 0.9;">Check your inbox for the verification code</p>
                <div class="d-flex justify-content-center gap-4">
                    <div class="text-center">
                        <i class="bi bi-clock fs-2 d-block mb-2"></i>
                        <small>10 Min</small>
                    </div>
                    <div class="text-center">
                        <i class="bi bi-shield-lock fs-2 d-block mb-2"></i>
                        <small>Secure</small>
                    </div>
                    <div class="text-center">
                        <i class="bi bi-check2-circle fs-2 d-block mb-2"></i>
                        <small>Verified</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// OTP Input Handling
const otpInputs = ['otp1', 'otp2', 'otp3', 'otp4', 'otp5', 'otp6'];
const otpInput = document.getElementById('otpInput');

// Auto-focus and move to next input
otpInputs.forEach((id, index) => {
    const input = document.getElementById(id);
    
    input.addEventListener('input', function(e) {
        // Only allow numbers
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Move to next input if value entered
        if (this.value && index < otpInputs.length - 1) {
            document.getElementById(otpInputs[index + 1]).focus();
        }
        
        updateOTP();
    });
    
    input.addEventListener('keydown', function(e) {
        // Move to previous input on backspace
        if (e.key === 'Backspace' && !this.value && index > 0) {
            document.getElementById(otpInputs[index - 1]).focus();
        }
        updateOTP();
    });
    
    input.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedData = e.clipboardData.getData('text').slice(0, 6);
        pastedData.split('').forEach((char, i) => {
            if (otpInputs[i] && /[0-9]/.test(char)) {
                document.getElementById(otpInputs[i]).value = char;
            }
        });
        updateOTP();
        document.getElementById(otpInputs[Math.min(pastedData.length, 5)]).focus();
    });
});

function updateOTP() {
    const otp = otpInputs.map(id => document.getElementById(id).value).join('');
    otpInput.value = otp;
}

// Focus first input on load
document.getElementById('otp1').focus();
</script>
@endsection
