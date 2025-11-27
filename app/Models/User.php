<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'otp_code',
        'otp_expires_at',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'otp_expires_at' => 'datetime',
        ];
    }

    /**
     * Generate and save OTP code
     */
    public function generateOtp(): string
    {
        $otp = str_pad((string) rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $this->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);
        return $otp;
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(string $otp): bool
    {
        if ($this->otp_code === $otp && $this->otp_expires_at && $this->otp_expires_at->isFuture()) {
            $this->update([
                'email_verified_at' => now(),
                'otp_code' => null,
                'otp_expires_at' => null,
            ]);
            return true;
        }
        return false;
    }
}
