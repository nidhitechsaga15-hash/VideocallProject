<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallRequest extends Model
{
    protected $fillable = [
        'caller_id',
        'receiver_id',
        'room_id',
        'status',
        'answered_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * Get the caller user
     */
    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    /**
     * Get the receiver user
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Check if call is active
     */
    public function isActive(): bool
    {
        return $this->status === 'pending' || $this->status === 'accepted';
    }
}
