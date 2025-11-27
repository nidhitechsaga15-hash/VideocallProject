<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupCall extends Model
{
    protected $fillable = [
        'room_id',
        'created_by',
        'type',
        'status',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * Get the creator of the group call
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all participants
     */
    public function participants(): HasMany
    {
        return $this->hasMany(GroupCallParticipant::class);
    }

    /**
     * Get active participants
     */
    public function activeParticipants(): HasMany
    {
        return $this->hasMany(GroupCallParticipant::class)->where('status', 'joined');
    }

    /**
     * Check if call is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
