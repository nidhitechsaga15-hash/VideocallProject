<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupCallParticipant extends Model
{
    protected $fillable = [
        'group_call_id',
        'user_id',
        'status',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    /**
     * Get the group call
     */
    public function groupCall(): BelongsTo
    {
        return $this->belongsTo(GroupCall::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if participant is active
     */
    public function isActive(): bool
    {
        return $this->status === 'joined';
    }
}
