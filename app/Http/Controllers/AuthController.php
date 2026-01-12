<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => 'This email is already registered. Please login instead.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Generate and send OTP
        $otp = $user->generateOtp();
        Mail::to($user->email)->send(new OtpMail($otp, $user->name));

        return redirect()->route('otp.verify')->with('email', $user->email)->with('success', 'Registration successful! Please verify your email with OTP.');
    }

    /**
     * Show OTP verification form
     */
    public function showOtpVerify()
    {
        $email = session('email');
        if (!$email) {
            return redirect()->route('register');
        }
        return view('auth.verify-otp', ['email' => $email]);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        if ($user->verifyOtp($validated['otp'])) {
            // Auto login after OTP verification
            Auth::login($user);
            return redirect()->route('dashboard')->with('success', 'Email verified successfully! You are now logged in.');
        }

        return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $otp = $user->generateOtp();
        Mail::to($user->email)->send(new OtpMail($otp, $user->name));

        return back()->with('success', 'OTP has been resent to your email.');
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Direct login - no email verification check on login
        // Email verification only required during registration
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user exists
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if ($user) {
                // User exists, just login
                Auth::login($user);
                return redirect()->route('dashboard')->with('success', 'Logged in successfully with Google!');
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(uniqid()), // Random password since Google handles auth
                    'email_verified_at' => now(), // Google email is already verified
                    'otp' => null,
                    'otp_expires_at' => null,
                ]);
                
                // Set profile picture if available
                if ($googleUser->getAvatar()) {
                    // You can download and save the avatar if needed
                    // For now, we'll just store the URL
                }
                
                Auth::login($user);
                return redirect()->route('dashboard')->with('success', 'Account created and logged in with Google!');
            }
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['error' => 'Failed to login with Google. Please try again.']);
        }
    }
}
