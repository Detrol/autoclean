<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmployeeInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'token',
        'invited_by',
        'accepted_at',
        'expires_at',
        'is_admin',
        'assigned_stations',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_admin' => 'boolean',
        'assigned_stations' => 'array',
    ];

    /**
     * The user who sent the invitation.
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Generate a new invitation token.
     */
    public static function generateToken(): string
    {
        return Str::random(32);
    }

    /**
     * Check if the invitation has expired.
     */
    public function hasExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the invitation has been accepted.
     */
    public function hasBeenAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    /**
     * Check if the invitation is still valid.
     */
    public function isValid(): bool
    {
        return ! $this->hasExpired() && ! $this->hasBeenAccepted();
    }

    /**
     * Mark the invitation as accepted.
     */
    public function markAsAccepted(): void
    {
        $this->update(['accepted_at' => now()]);
    }

    /**
     * Get the invitation URL.
     */
    public function getInvitationUrl(): string
    {
        return route('invitation.accept', ['token' => $this->token]);
    }

    /**
     * Scope to get valid invitations.
     */
    public function scopeValid($query)
    {
        return $query->whereNull('accepted_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope to get expired invitations.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())
            ->whereNull('accepted_at');
    }

    /**
     * Scope to get accepted invitations.
     */
    public function scopeAccepted($query)
    {
        return $query->whereNotNull('accepted_at');
    }
}
